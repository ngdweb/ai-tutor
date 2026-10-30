<?php

namespace App\Http\Controllers;

use App\Models\LearnupWord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LearnupWordController extends Controller
{
    /**
     * Build the base query with search + status filters.
     */
    private function buildQuery(Request $request)
    {
        $query = LearnupWord::query()->with('category');

        // Optional filter by category.
        if ($request->filled('category_id') && (string) $request->get('category_id') !== '0') {
            $query->where('category_id', (int) $request->get('category_id'));
        }

        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('video_name', 'like', "%{$search}%")
                  ->orWhere('json_data', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'show' || $status === '1') {
                $query->where('is_visible', true);
            } elseif ($status === 'hide' || $status === '0') {
                $query->where('is_visible', false);
            }
        }

        return $query;
    }

    /**
     * Display a listing of the resource with search, filter, and pagination.
     */
    public function index(Request $request): View
    {
        $items = $this->buildQuery($request)
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total'   => LearnupWord::count(),
            'visible' => LearnupWord::where('is_visible', true)->count(),
            'hidden'  => LearnupWord::where('is_visible', false)->count(),
        ];

        $categories = \App\Models\LearnupCategory::orderBy('order_index')->orderBy('name')->get();

        return view('learnup.index', compact('items', 'stats', 'categories'));
    }

    /**
     * Securely stream a video's bytes by DB id (authenticated POST only).
     * No file URL is ever exposed in the page; the path is resolved server-side.
     */
    public function stream(int $id): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $item = LearnupWord::findOrFail($id);

        // Resolve the real on-disk path via the model (handles category + legacy folders).
        $path = (string) $item->getVideoRelativePath();
        if ($path === '' || str_starts_with($path, 'http')) {
            abort(404);
        }

        $real = realpath(public_path($path));
        $base = realpath(public_path('uploads'));

        if ($real === false || $base === false || !str_starts_with($real, $base) || !is_file($real)) {
            abort(404);
        }

        $ext   = strtolower(pathinfo($real, PATHINFO_EXTENSION));
        $mimes = [
            'mp4' => 'video/mp4', 'webm' => 'video/webm', 'ogg' => 'video/ogg',
            'mov' => 'video/quicktime', 'mkv' => 'video/x-matroska', 'avi' => 'video/x-msvideo',
        ];

        return response()->file($real, [
            'Content-Type'           => $mimes[$ext] ?? 'application/octet-stream',
            'Cache-Control'          => 'private, no-store, max-age=0',
            'Content-Disposition'    => 'inline',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * AJAX: return a single category's videos (ordered by saved sequence) for the
     * "Set Index" reorder popup.
     */
    public function categoryVideos(int $categoryId): JsonResponse
    {
        // Cap how many rows the reorder popup loads so a huge category can never
        // stall the browser; only the columns needed are selected (no longText json).
        $limit = 500;

        $base = LearnupWord::where('category_id', $categoryId)
            ->orderBy('order_index', 'asc')
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc');

        $total = (clone $base)->count();

        $videos = $base->limit($limit)
            ->get(['id', 'category_id', 'title', 'episode_no', 'video_name', 'video_path', 'thumbnail_path', 'is_visible', 'order_index'])
            ->map(fn ($v) => [
                'id'            => $v->id,
                'title'         => $v->title,
                'episode_no'    => $v->episode_no,
                'video_name'    => $v->video_name ?: basename($v->video_path),
                'thumbnail_url' => secure_media($v->getThumbnailRelativePath()) ?? $v->thumbnail_url,
                'is_visible'    => $v->is_visible,
            ]);

        return response()->json([
            'success' => true,
            'total'   => $total,
            'limited' => $total > $limit,
            'limit'   => $limit,
            'data'    => $videos,
        ]);
    }

    /**
     * AJAX: return only the table+pagination partial (no route change).
     */
    public function listAjax(Request $request): \Illuminate\Http\Response
    {
        $items = $this->buildQuery($request)
            ->orderBy('updated_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        // The whole index view is evaluated; return only the table fragment.
        $stats = [
            'total'   => LearnupWord::count(),
            'visible' => LearnupWord::where('is_visible', true)->count(),
            'hidden'  => LearnupWord::where('is_visible', false)->count(),
        ];
        $categories = \App\Models\LearnupCategory::orderBy('order_index')->orderBy('name')->get();

        return response(
            view('learnup.index', compact('items', 'stats', 'categories'))->fragment('videoTable')
        );
    }

    /**
     * Show the dedicated form for creating new video learning record(s).
     */
    public function create(): View
    {
        $categories = \App\Models\LearnupCategory::orderBy('order_index')->orderBy('name')->get();
        return view('learnup.form', compact('categories'));
    }

    /**
     * Show the dedicated form for editing an existing video learning record.
     */
    public function edit(int $id): View
    {
        $record = LearnupWord::findOrFail($id);
        $categories = \App\Models\LearnupCategory::orderBy('order_index')->orderBy('name')->get();
        return view('learnup.form', compact('record', 'categories'));
    }

    /**
     * Store single or multiple newly created resources in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        // Category applies to every record in this submission (falls back to General).
        // Files are stored under uploads/learnup_with_ai/<category>/{videos,thumbnails}.
        $categoryId  = $this->resolveCategoryId($request);
        $videoRelDir = $this->videoDir($categoryId);
        $thumbRelDir = $this->thumbDir($categoryId);

        // Check if multiple records array is submitted
        if ($request->has('records') && is_array($request->input('records'))) {
            $recordsData = $request->input('records');
            $createdCount = 0;

            foreach ($recordsData as $index => $row) {
                $videoFile = $request->file("records.{$index}.video");
                $thumbFile = $request->file("records.{$index}.thumbnail");
                $autoThumbBase64 = $row['auto_thumbnail_base64'] ?? null;
                $jsonData = $row['json_data'] ?? '{}';
                $titleInput = $row['title'] ?? null;
                $episodeNo = $this->normalizeEpisodeNo($row['episode_no'] ?? null);
                $isVisible = isset($row['is_visible']) && ($row['is_visible'] === '1' || $row['is_visible'] === true || $row['is_visible'] === 1);

                if (!$videoFile) {
                    continue; // Skip if no video provided for this row
                }

                if (!$this->isValidJson($jsonData)) {
                    $jsonData = '{}';
                }

                $originalVideoName = $videoFile->getClientOriginalName();
                $videoExt = $videoFile->getClientOriginalExtension() ?: 'mp4';
                $videoCleanName = Str::slug(pathinfo($originalVideoName, PATHINFO_FILENAME));

                $videoFileName = $this->uniqueUploadName($videoFile, $videoRelDir, 'mp4');
                $videoFile->move(public_path($videoRelDir), $videoFileName);

                // Handle Thumbnail
                $thumbnailFileName = null;
                if ($thumbFile) {
                    $thumbFileName = $this->uniqueUploadName($thumbFile, $thumbRelDir, 'jpg');
                    $thumbFile->move(public_path($thumbRelDir), $thumbFileName);
                    $thumbnailFileName = $thumbFileName;
                } elseif (!empty($autoThumbBase64)) {
                    $thumbnailFileName = $this->saveBase64Image($autoThumbBase64, $thumbRelDir);
                }

                if (!$thumbnailFileName) {
                    $thumbnailFileName = $this->generateDefaultThumbnail($titleInput ?: $videoCleanName ?: 'Video', $thumbRelDir);
                }

                $title = $titleInput ?: ($videoCleanName ? ucwords(str_replace('-', ' ', $videoCleanName)) : 'Untitled Video');

                LearnupWord::create([
                    'category_id'    => $categoryId,
                    'title'          => $title,
                    'episode_no'     => $episodeNo,
                    'video_path'     => $videoFileName,
                    'video_name'     => $originalVideoName,
                    'thumbnail_path' => $thumbnailFileName,
                    'json_data'      => $this->formatJson($jsonData),
                    'is_visible'     => $isVisible,
                ]);

                $createdCount++;
            }

            $msg = "Successfully created {$createdCount} video learning word record(s)!";
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }
            return redirect()->route('learnup.index')->with('success', $msg);
        }

        // Single record fallback (Direct form submission)
        $request->validate([
            'title'                 => 'nullable|string|max:255',
            'video'                 => 'required|file|mimes:mp4,mov,ogg,qt,webm,mkv,avi|max:204800',
            'thumbnail'             => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            'auto_thumbnail_base64' => 'nullable|string',
            'json_data'             => 'required|string',
            'is_visible'            => 'nullable',
        ]);

        if (!$this->isValidJson($request->input('json_data'))) {
            return back()->withErrors(['json_data' => 'The JSON content must be valid JSON format.'])->withInput();
        }

        $videoFile = $request->file('video');
        $originalVideoName = $videoFile->getClientOriginalName();
        $videoExt = $videoFile->getClientOriginalExtension() ?: 'mp4';
        $videoCleanName = Str::slug(pathinfo($originalVideoName, PATHINFO_FILENAME));
        
        $videoFileName = $this->uniqueUploadName($videoFile, $videoRelDir, 'mp4');
        $videoFile->move(public_path($videoRelDir), $videoFileName);

        $thumbnailFileName = null;
        if ($request->hasFile('thumbnail')) {
            $thumbFile = $request->file('thumbnail');
            $thumbFileName = $this->uniqueUploadName($thumbFile, $thumbRelDir, 'jpg');
            $thumbFile->move(public_path($thumbRelDir), $thumbFileName);
            $thumbnailFileName = $thumbFileName;
        } elseif ($request->filled('auto_thumbnail_base64')) {
            $thumbnailFileName = $this->saveBase64Image($request->input('auto_thumbnail_base64'), $thumbRelDir);
        }

        if (!$thumbnailFileName) {
            $thumbnailFileName = $this->generateDefaultThumbnail($request->input('title') ?: $videoCleanName ?: 'Video', $thumbRelDir);
        }

        $title = $request->input('title') ?: ($videoCleanName ? ucwords(str_replace('-', ' ', $videoCleanName)) : 'Untitled Video');
        $isVisible = $request->has('is_visible') ? (bool)$request->input('is_visible') : true;

        LearnupWord::create([
            'category_id'    => $categoryId,
            'title'          => $title,
            'episode_no'     => $this->normalizeEpisodeNo($request->input('episode_no')),
            'video_path'     => $videoFileName,
            'video_name'     => $originalVideoName,
            'thumbnail_path' => $thumbnailFileName,
            'json_data'      => $this->formatJson($request->input('json_data')),
            'is_visible'     => $isVisible,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Video learning word created successfully!']);
        }

        return redirect()->route('learnup.index')->with('success', 'Video learning word created successfully!');
    }

    /**
     * Display the specified resource as JSON (for view/edit modal).
     */
    public function show(int $id): JsonResponse
    {
        $item = LearnupWord::findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => [
                'id'             => $item->id,
                'title'          => $item->title,
                'video_path'     => $item->video_path,
                'video_name'     => $item->video_name,
                'video_url'      => $item->video_url,
                'thumbnail_path' => $item->thumbnail_path,
                'thumbnail_url'  => $item->thumbnail_url,
                'json_data'      => $item->json_data,
                'is_visible'     => $item->is_visible,
                'created_at'     => $item->created_at?->format('M d, Y h:i A'),
                'updated_at'     => $item->updated_at?->format('M d, Y h:i A'),
            ]
        ]);
    }

    /**
     * Update the specified resource (and optionally create any additional batch records).
     */
    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        // Category applies to every record touched in this submission (falls back to General).
        // Files are stored under uploads/learnup_with_ai/<category>/{videos,thumbnails}.
        $categoryId  = $this->resolveCategoryId($request);
        $videoRelDir = $this->videoDir($categoryId);
        $thumbRelDir = $this->thumbDir($categoryId);

        // Check if multiple records array is submitted in edit page
        if ($request->has('records') && is_array($request->input('records'))) {
            $recordsData = $request->input('records');
            $updatedCount = 0;
            $createdCount = 0;

            foreach ($recordsData as $index => $row) {
                $rowId = $row['id'] ?? null;
                $videoFile = $request->file("records.{$index}.video");
                $thumbFile = $request->file("records.{$index}.thumbnail");
                $autoThumbBase64 = $row['auto_thumbnail_base64'] ?? null;
                $jsonData = $row['json_data'] ?? '{}';
                $titleInput = $row['title'] ?? null;
                $episodeNo = $this->normalizeEpisodeNo($row['episode_no'] ?? null);
                $isVisible = isset($row['is_visible']) && ($row['is_visible'] === '1' || $row['is_visible'] === true || $row['is_visible'] === 1);

                if (!$this->isValidJson($jsonData)) {
                    $jsonData = '{}';
                }

                if ($rowId) {
                    // Update existing record
                    $item = LearnupWord::find($rowId);
                    if ($item) {
                        if ($videoFile) {
                            $originalVideoName = $videoFile->getClientOriginalName();
                            $videoExt = $videoFile->getClientOriginalExtension() ?: 'mp4';
                            $videoCleanName = Str::slug(pathinfo($originalVideoName, PATHINFO_FILENAME));
                            $videoFileName = $this->uniqueUploadName($videoFile, $videoRelDir, 'mp4');
                            $videoFile->move(public_path($videoRelDir), $videoFileName);

                            $this->deleteFileAndCleanup($item->getVideoRelativePath());

                            $item->video_path = $videoFileName;
                            $item->video_name = $originalVideoName;
                        }

                        if ($thumbFile) {
                            $thumbFileName = $this->uniqueUploadName($thumbFile, $thumbRelDir, 'jpg');
                            $thumbFile->move(public_path($thumbRelDir), $thumbFileName);

                            $this->deleteFileAndCleanup($item->getThumbnailRelativePath());

                            $item->thumbnail_path = $thumbFileName;
                        } elseif (!empty($autoThumbBase64)) {
                            $newThumb = $this->saveBase64Image($autoThumbBase64, $thumbRelDir);
                            $this->deleteFileAndCleanup($item->getThumbnailRelativePath());
                            $item->thumbnail_path = $newThumb;
                        }

                        if ($titleInput) {
                            $item->title = $titleInput;
                        }
                        $item->episode_no = $episodeNo;
                        $oldCatId = $item->category_id;
                        if ($oldCatId !== $categoryId) {
                            $oldVideoRel = $item->getVideoRelativePath();
                            $oldThumbRel = $item->getThumbnailRelativePath();
                            $item->category_id = $categoryId;
                            $item->video_path = $this->relocateToCategory($oldVideoRel, $videoRelDir);
                            $item->thumbnail_path = $this->relocateToCategory($oldThumbRel, $thumbRelDir);
                        } else {
                            $item->category_id = $categoryId;
                        }
                        $item->json_data = $this->formatJson($jsonData);
                        $item->is_visible = $isVisible;
                        $item->updated_at = now();
                        $item->save();
                        $updatedCount++;
                    }
                } else {
                    // Additional new record added on the edit screen!
                    if ($videoFile) {
                        $originalVideoName = $videoFile->getClientOriginalName();
                        $videoExt = $videoFile->getClientOriginalExtension() ?: 'mp4';
                        $videoCleanName = Str::slug(pathinfo($originalVideoName, PATHINFO_FILENAME));

                        $videoFileName = $this->uniqueUploadName($videoFile, $videoRelDir, 'mp4');
                        $videoFile->move(public_path($videoRelDir), $videoFileName);

                        $thumbnailFileName = null;
                        if ($thumbFile) {
                            $thumbFileName = $this->uniqueUploadName($thumbFile, $thumbRelDir, 'jpg');
                            $thumbFile->move(public_path($thumbRelDir), $thumbFileName);
                            $thumbnailFileName = $thumbFileName;
                        } elseif (!empty($autoThumbBase64)) {
                            $thumbnailFileName = $this->saveBase64Image($autoThumbBase64, $thumbRelDir);
                        }

                        if (!$thumbnailFileName) {
                            $thumbnailFileName = $this->generateDefaultThumbnail($titleInput ?: $videoCleanName ?: 'Video', $thumbRelDir);
                        }

                        LearnupWord::create([
                            'category_id'    => $categoryId,
                            'title'          => $titleInput ?: ($videoCleanName ? ucwords(str_replace('-', ' ', $videoCleanName)) : 'Untitled Video'),
                            'episode_no'     => $episodeNo,
                            'video_path'     => $videoFileName,
                            'video_name'     => $originalVideoName,
                            'thumbnail_path' => $thumbnailFileName,
                            'json_data'      => $this->formatJson($jsonData),
                            'is_visible'     => $isVisible,
                        ]);
                        $createdCount++;
                    }
                }
            }

            $msg = "Changes saved successfully! ({$updatedCount} updated" . ($createdCount > 0 ? ", {$createdCount} added" : "") . ")";
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }
            return redirect()->route('learnup.index')->with('success', $msg);
        }

        // Single record fallback
        $item = LearnupWord::findOrFail($id);

        $request->validate([
            'title'                 => 'nullable|string|max:255',
            'video'                 => 'nullable|file|mimes:mp4,mov,ogg,qt,webm,mkv,avi|max:204800',
            'thumbnail'             => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            'auto_thumbnail_base64' => 'nullable|string',
            'json_data'             => 'required|string',
            'is_visible'            => 'nullable',
        ]);

        if (!$this->isValidJson($request->input('json_data'))) {
            return back()->withErrors(['json_data' => 'The JSON content must be valid JSON format.'])->withInput();
        }

        if ($request->hasFile('video')) {
            $videoFile = $request->file('video');
            $originalVideoName = $videoFile->getClientOriginalName();
            $videoExt = $videoFile->getClientOriginalExtension() ?: 'mp4';
            $videoCleanName = Str::slug(pathinfo($originalVideoName, PATHINFO_FILENAME));

            $videoFileName = $this->uniqueUploadName($videoFile, $videoRelDir, 'mp4');
            $videoFile->move(public_path($videoRelDir), $videoFileName);

            // Remove the previous file only after the new one is safely stored.
            $this->deleteFileAndCleanup($item->getVideoRelativePath());

            $item->video_path = $videoFileName;
            $item->video_name = $originalVideoName;
        }

        if ($request->hasFile('thumbnail')) {
            $thumbFile = $request->file('thumbnail');
            $thumbFileName = $this->uniqueUploadName($thumbFile, $thumbRelDir, 'jpg');
            $thumbFile->move(public_path($thumbRelDir), $thumbFileName);

            $this->deleteFileAndCleanup($item->getThumbnailRelativePath());

            $item->thumbnail_path = $thumbFileName;
        } elseif ($request->filled('auto_thumbnail_base64')) {
            $newThumb = $this->saveBase64Image($request->input('auto_thumbnail_base64'), $thumbRelDir);
            $this->deleteFileAndCleanup($item->getThumbnailRelativePath());
            $item->thumbnail_path = $newThumb;
        }

        if ($request->filled('title')) {
            $item->title = $request->input('title');
        }

        $item->episode_no = $this->normalizeEpisodeNo($request->input('episode_no'));
        $oldCatId = $item->category_id;
        if ($oldCatId !== $categoryId) {
            $oldVideoRel = $item->getVideoRelativePath();
            $oldThumbRel = $item->getThumbnailRelativePath();
            $item->category_id = $categoryId;
            $item->video_path = $this->relocateToCategory($oldVideoRel, $videoRelDir);
            $item->thumbnail_path = $this->relocateToCategory($oldThumbRel, $thumbRelDir);
        } else {
            $item->category_id = $categoryId;
        }
        $item->json_data = $this->formatJson($request->input('json_data'));
        $item->is_visible = $request->has('is_visible') ? (bool)$request->input('is_visible') : $item->is_visible;
        $item->updated_at = now();

        $item->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Video learning word updated successfully!']);
        }

        return redirect()->route('learnup.index')->with('success', 'Video learning word updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse|JsonResponse
    {
        $item = LearnupWord::findOrFail($id);
        $item->delete(); // Model booted deleting listener cleans up files

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Video learning word deleted successfully!']);
        }

        return redirect()->route('learnup.index')->with('success', 'Video learning word and files deleted successfully!');
    }

    /**
     * Toggle visibility (Show / Hide) via AJAX or standard request.
     */
    public function toggleVisibility(int $id): JsonResponse|RedirectResponse
    {
        $item = LearnupWord::findOrFail($id);
        $item->is_visible = !$item->is_visible;
        $item->save();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success'    => true,
                'is_visible' => $item->is_visible,
                'message'    => 'Visibility updated to ' . ($item->is_visible ? 'Show (Active)' : 'Hide (Inactive)'),
            ]);
        }

        return back()->with('success', 'Status updated successfully.');
    }

    /**
     * Reorder records sequence via AJAX.
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'ordered_ids' => 'required|array',
        ]);

        foreach ($request->input('ordered_ids') as $index => $id) {
            LearnupWord::where('id', $id)->update(['order_index' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Record sequence updated successfully!',
        ]);
    }

    /**
     * Resolve the category id for this submission. Uses the submitted
     * category_id when valid, otherwise falls back to the default "General"
     * category so no video is ever left without a category.
     */
    /**
     * Normalize an episode number input: integer when a non-negative number is
     * provided, otherwise null (the field is optional).
     */
    private function normalizeEpisodeNo($value): ?int
    {
        if ($value === null || $value === '' || !is_numeric($value)) {
            return null;
        }

        $int = (int) $value;
        return $int >= 0 ? $int : null;
    }

    private function resolveCategoryId(Request $request): ?int
    {
        $categoryId = $request->input('category_id');

        if ($categoryId && \App\Models\LearnupCategory::whereKey($categoryId)->exists()) {
            return (int) $categoryId;
        }

        return \App\Models\LearnupCategory::where('name', 'General')->value('id');
    }

    /**
     * Save base64 image string into the given category's thumbnails folder.
     */
    private function saveBase64Image(string $base64Data, string $thumbRelDir): ?string
    {
        try {
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, jpeg, webp
                if (!in_array($type, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $type = 'jpg';
                }
            } else {
                $type = 'jpg';
            }

            $decoded = base64_decode($base64Data);
            if ($decoded === false) {
                return null;
            }

            $this->ensureDir($thumbRelDir);
            $thumbFileName = 'thumb_' . time() . '_' . Str::random(6) . '.' . $type;
            $destination = public_path($thumbRelDir . '/' . $thumbFileName);

            File::put($destination, $decoded);

            return $thumbFileName;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Generate default thumbnail using PHP GD library into the given folder.
     */
    private function generateDefaultThumbnail(string $text, string $thumbRelDir): string
    {
        $this->ensureDir($thumbRelDir);

        $width = 640;
        $height = 360;

        $img = imagecreatetruecolor($width, $height);
        
        // Gradient or sleek dark slate background
        $bgColor = imagecolorallocate($img, 30, 41, 59); // #1e293b
        $accentColor = imagecolorallocate($img, 79, 70, 229); // #4f46e5
        $white = imagecolorallocate($img, 255, 255, 255);
        $muted = imagecolorallocate($img, 148, 163, 184);

        imagefilledrectangle($img, 0, 0, $width, $height, $bgColor);

        // Draw centered play circle
        $centerX = $width / 2;
        $centerY = $height / 2 - 20;
        imagefilledellipse($img, $centerX, $centerY, 70, 70, $accentColor);

        // Draw play triangle (approximate polygon)
        $trianglePoints = [
            $centerX - 8, $centerY - 14,
            $centerX - 8, $centerY + 14,
            $centerX + 14, $centerY,
        ];
        imagefilledpolygon($img, $trianglePoints, $white);

        // Draw title text
        $displayText = Str::limit($text, 28);
        $font = 5; // Built-in GD font 5
        $textWidth = imagefontwidth($font) * strlen($displayText);
        $textX = max(10, ($width - $textWidth) / 2);
        imagestring($img, $font, $textX, $centerY + 60, $displayText, $white);

        $badgeText = "Video Learning";
        $badgeWidth = imagefontwidth(3) * strlen($badgeText);
        imagestring($img, 3, ($width - $badgeWidth) / 2, $centerY + 85, $badgeText, $muted);

        $thumbFileName = 'thumb_' . time() . '_' . Str::random(6) . '.jpg';
        $destination = public_path($thumbRelDir . '/' . $thumbFileName);

        imagejpeg($img, $destination, 90);
        imagedestroy($img);

        return $thumbFileName;
    }

    /**
     * Check if a string is valid JSON
     */
    private function isValidJson(mixed $string): bool
    {
        if (!is_string($string) || trim($string) === '') {
            return false;
        }

        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
    }

    /**
     * Format JSON string with 2-space indentation
     */
    private function formatJson(string $jsonString): string
    {
        $decoded = json_decode($jsonString);
        return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Base relative folder for a category:
     *   uploads/learnup_with_ai/<category-name>
     */
    private function categoryBasePath(?int $categoryId): string
    {
        $name = \App\Models\LearnupCategory::whereKey($categoryId)->value('name') ?? 'General';
        $slug = Str::slug($name) ?: 'general';

        return 'uploads/learnup_with_ai/' . $slug;
    }

    /**
     * Relative "videos" directory for a category (created if missing).
     */
    private function videoDir(?int $categoryId): string
    {
        return $this->ensureDir($this->categoryBasePath($categoryId) . '/videos');
    }

    /**
     * Relative "thumbnails" directory for a category (created if missing).
     */
    private function thumbDir(?int $categoryId): string
    {
        return $this->ensureDir($this->categoryBasePath($categoryId) . '/thumbnails');
    }

    /**
     * Build a storage file name for an uploaded file, keeping its ORIGINAL name.
     * If a file with the same name already exists in the same (category) folder,
     * a timestamp is appended so nothing is overwritten:
     *   lesson.mp4  ->  lesson.mp4            (first time)
     *   lesson.mp4  ->  lesson_1790000000.mp4 (name already present)
     */
    private function uniqueUploadName($file, string $relDir, string $fallbackExt): string
    {
        $ext  = strtolower($file->getClientOriginalExtension() ?: $fallbackExt);
        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file';

        $fileName = $base . '.' . $ext;

        if (File::exists(public_path($relDir . '/' . $fileName))) {
            $fileName = $base . '_' . time() . '.' . $ext;

            // Guard against a rare same-second collision.
            if (File::exists(public_path($relDir . '/' . $fileName))) {
                $fileName = $base . '_' . time() . '_' . Str::random(4) . '.' . $ext;
            }
        }

        return $fileName;
    }

    /**
     * Delete a stored file (by relative path) and clean up its folder if empty.
     */
    private function deleteFileAndCleanup(?string $relPath): void
    {
        if (!$relPath || str_starts_with($relPath, 'http')) {
            return;
        }

        $abs = public_path($relPath);
        if (File::exists($abs)) {
            File::delete($abs);
            LearnupWord::cleanupEmptyFolders([dirname($abs)]);
        }
    }

    /**
     * Move an already-stored file into the given category folder when its
     * current folder differs (e.g. the record's category was changed on edit).
     * Cleans up the old folder if it becomes empty. Returns the new relative path.
     */
    private function relocateToCategory(?string $relPath, string $targetRelDir): ?string
    {
        if (!$relPath || str_starts_with($relPath, 'http')) {
            return $relPath ? basename($relPath) : null;
        }

        $currentDir = trim(str_replace('\\', '/', dirname($relPath)), '/');
        if ($currentDir === trim($targetRelDir, '/')) {
            return basename($relPath); // already in the correct category folder
        }

        $src = public_path($relPath);
        if (!File::exists($src)) {
            return basename($relPath); // nothing physical to move
        }

        $this->ensureDir($targetRelDir);
        $fileName = $this->uniqueNameInDir($targetRelDir, basename($relPath));
        File::move($src, public_path($targetRelDir . '/' . $fileName));

        LearnupWord::cleanupEmptyFolders([dirname($src)]);

        return $fileName;
    }

    /**
     * Ensure a plain file name is unique inside a relative directory,
     * appending a timestamp on collision.
     */
    private function uniqueNameInDir(string $relDir, string $fileName): string
    {
        if (!File::exists(public_path($relDir . '/' . $fileName))) {
            return $fileName;
        }

        $ext  = pathinfo($fileName, PATHINFO_EXTENSION);
        $base = pathinfo($fileName, PATHINFO_FILENAME);
        $dot  = $ext ? '.' . $ext : '';

        $candidate = $base . '_' . time() . $dot;
        if (File::exists(public_path($relDir . '/' . $candidate))) {
            $candidate = $base . '_' . time() . '_' . Str::random(4) . $dot;
        }

        return $candidate;
    }

    /**
     * Ensure a relative public directory exists; returns the relative path.
     */
    private function ensureDir(string $relativeDir): string
    {
        $dir = public_path($relativeDir);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true, true);
        }

        return $relativeDir;
    }
}
