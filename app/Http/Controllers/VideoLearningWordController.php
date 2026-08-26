<?php

namespace App\Http\Controllers;

use App\Models\VideoLearningWord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VideoLearningWordController extends Controller
{
    /**
     * Build the base query with search + status filters.
     */
    private function buildQuery(Request $request)
    {
        $query = VideoLearningWord::query();

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
            'total'   => VideoLearningWord::count(),
            'visible' => VideoLearningWord::where('is_visible', true)->count(),
            'hidden'  => VideoLearningWord::where('is_visible', false)->count(),
        ];

        return view('video_learning.index', compact('items', 'stats'));
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

        return response(view('video_learning._list', compact('items')));
    }

    /**
     * Show the dedicated form for creating new video learning record(s).
     */
    public function create(): View
    {
        return view('video_learning.form');
    }

    /**
     * Show the dedicated form for editing an existing video learning record.
     */
    public function edit(int $id): View
    {
        $record = VideoLearningWord::findOrFail($id);
        return view('video_learning.form', compact('record'));
    }

    /**
     * Store single or multiple newly created resources in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->ensureDirectoriesExist();

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

                $videoFileName = 'video_' . time() . '_' . Str::random(6) . ($videoCleanName ? '_' . $videoCleanName : '') . '.' . $videoExt;
                $videoFile->move(public_path('uploads/video_learning/videos'), $videoFileName);
                $videoRelativePath = 'uploads/video_learning/videos/' . $videoFileName;

                // Handle Thumbnail
                $thumbnailRelativePath = null;
                if ($thumbFile) {
                    $thumbExt = $thumbFile->getClientOriginalExtension() ?: 'jpg';
                    $thumbFileName = 'thumb_' . time() . '_' . Str::random(6) . '.' . $thumbExt;
                    $thumbFile->move(public_path('uploads/video_learning/thumbnails'), $thumbFileName);
                    $thumbnailRelativePath = 'uploads/video_learning/thumbnails/' . $thumbFileName;
                } elseif (!empty($autoThumbBase64)) {
                    $thumbnailRelativePath = $this->saveBase64Image($autoThumbBase64);
                }

                if (!$thumbnailRelativePath) {
                    $thumbnailRelativePath = $this->generateDefaultThumbnail($titleInput ?: $videoCleanName ?: 'Video');
                }

                $title = $titleInput ?: ($videoCleanName ? ucwords(str_replace('-', ' ', $videoCleanName)) : 'Untitled Video');

                VideoLearningWord::create([
                    'title'          => $title,
                    'video_path'     => $videoRelativePath,
                    'video_name'     => $originalVideoName,
                    'thumbnail_path' => $thumbnailRelativePath,
                    'json_data'      => $this->formatJson($jsonData),
                    'is_visible'     => $isVisible,
                ]);

                $createdCount++;
            }

            $msg = "Successfully created {$createdCount} video learning word record(s)!";
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }
            return redirect()->route('video-learning.index')->with('success', $msg);
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
        
        $videoFileName = 'video_' . time() . '_' . Str::random(6) . ($videoCleanName ? '_' . $videoCleanName : '') . '.' . $videoExt;
        $videoFile->move(public_path('uploads/video_learning/videos'), $videoFileName);
        $videoRelativePath = 'uploads/video_learning/videos/' . $videoFileName;

        $thumbnailRelativePath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbFile = $request->file('thumbnail');
            $thumbExt = $thumbFile->getClientOriginalExtension() ?: 'jpg';
            $thumbFileName = 'thumb_' . time() . '_' . Str::random(6) . '.' . $thumbExt;
            $thumbFile->move(public_path('uploads/video_learning/thumbnails'), $thumbFileName);
            $thumbnailRelativePath = 'uploads/video_learning/thumbnails/' . $thumbFileName;
        } elseif ($request->filled('auto_thumbnail_base64')) {
            $thumbnailRelativePath = $this->saveBase64Image($request->input('auto_thumbnail_base64'));
        }

        if (!$thumbnailRelativePath) {
            $thumbnailRelativePath = $this->generateDefaultThumbnail($request->input('title') ?: $videoCleanName ?: 'Video');
        }

        $title = $request->input('title') ?: ($videoCleanName ? ucwords(str_replace('-', ' ', $videoCleanName)) : 'Untitled Video');
        $isVisible = $request->has('is_visible') ? (bool)$request->input('is_visible') : true;

        VideoLearningWord::create([
            'title'          => $title,
            'video_path'     => $videoRelativePath,
            'video_name'     => $originalVideoName,
            'thumbnail_path' => $thumbnailRelativePath,
            'json_data'      => $this->formatJson($request->input('json_data')),
            'is_visible'     => $isVisible,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Video learning word created successfully!']);
        }

        return redirect()->route('video-learning.index')->with('success', 'Video learning word created successfully!');
    }

    /**
     * Display the specified resource as JSON (for view/edit modal).
     */
    public function show(int $id): JsonResponse
    {
        $item = VideoLearningWord::findOrFail($id);

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
        $this->ensureDirectoriesExist();

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
                $isVisible = isset($row['is_visible']) && ($row['is_visible'] === '1' || $row['is_visible'] === true || $row['is_visible'] === 1);

                if (!$this->isValidJson($jsonData)) {
                    $jsonData = '{}';
                }

                if ($rowId) {
                    // Update existing record
                    $item = VideoLearningWord::find($rowId);
                    if ($item) {
                        if ($videoFile) {
                            if ($item->video_path && File::exists(public_path($item->video_path))) {
                                File::delete(public_path($item->video_path));
                            }
                            $originalVideoName = $videoFile->getClientOriginalName();
                            $videoExt = $videoFile->getClientOriginalExtension() ?: 'mp4';
                            $videoCleanName = Str::slug(pathinfo($originalVideoName, PATHINFO_FILENAME));
                            $videoFileName = 'video_' . time() . '_' . Str::random(6) . ($videoCleanName ? '_' . $videoCleanName : '') . '.' . $videoExt;
                            $videoFile->move(public_path('uploads/video_learning/videos'), $videoFileName);

                            $item->video_path = 'uploads/video_learning/videos/' . $videoFileName;
                            $item->video_name = $originalVideoName;
                        }

                        if ($thumbFile) {
                            if ($item->thumbnail_path && File::exists(public_path($item->thumbnail_path))) {
                                File::delete(public_path($item->thumbnail_path));
                            }
                            $thumbExt = $thumbFile->getClientOriginalExtension() ?: 'jpg';
                            $thumbFileName = 'thumb_' . time() . '_' . Str::random(6) . '.' . $thumbExt;
                            $thumbFile->move(public_path('uploads/video_learning/thumbnails'), $thumbFileName);
                            $item->thumbnail_path = 'uploads/video_learning/thumbnails/' . $thumbFileName;
                        } elseif (!empty($autoThumbBase64)) {
                            if ($item->thumbnail_path && File::exists(public_path($item->thumbnail_path))) {
                                File::delete(public_path($item->thumbnail_path));
                            }
                            $item->thumbnail_path = $this->saveBase64Image($autoThumbBase64);
                        }

                        if ($titleInput) {
                            $item->title = $titleInput;
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

                        $videoFileName = 'video_' . time() . '_' . Str::random(6) . ($videoCleanName ? '_' . $videoCleanName : '') . '.' . $videoExt;
                        $videoFile->move(public_path('uploads/video_learning/videos'), $videoFileName);
                        $videoRelativePath = 'uploads/video_learning/videos/' . $videoFileName;

                        $thumbnailRelativePath = null;
                        if ($thumbFile) {
                            $thumbExt = $thumbFile->getClientOriginalExtension() ?: 'jpg';
                            $thumbFileName = 'thumb_' . time() . '_' . Str::random(6) . '.' . $thumbExt;
                            $thumbFile->move(public_path('uploads/video_learning/thumbnails'), $thumbFileName);
                            $thumbnailRelativePath = 'uploads/video_learning/thumbnails/' . $thumbFileName;
                        } elseif (!empty($autoThumbBase64)) {
                            $thumbnailRelativePath = $this->saveBase64Image($autoThumbBase64);
                        }

                        if (!$thumbnailRelativePath) {
                            $thumbnailRelativePath = $this->generateDefaultThumbnail($titleInput ?: $videoCleanName ?: 'Video');
                        }

                        VideoLearningWord::create([
                            'title'          => $titleInput ?: ($videoCleanName ? ucwords(str_replace('-', ' ', $videoCleanName)) : 'Untitled Video'),
                            'video_path'     => $videoRelativePath,
                            'video_name'     => $originalVideoName,
                            'thumbnail_path' => $thumbnailRelativePath,
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
            return redirect()->route('video-learning.index')->with('success', $msg);
        }

        // Single record fallback
        $item = VideoLearningWord::findOrFail($id);

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
            if ($item->video_path && File::exists(public_path($item->video_path))) {
                File::delete(public_path($item->video_path));
            }

            $videoFile = $request->file('video');
            $originalVideoName = $videoFile->getClientOriginalName();
            $videoExt = $videoFile->getClientOriginalExtension() ?: 'mp4';
            $videoCleanName = Str::slug(pathinfo($originalVideoName, PATHINFO_FILENAME));

            $videoFileName = 'video_' . time() . '_' . Str::random(6) . ($videoCleanName ? '_' . $videoCleanName : '') . '.' . $videoExt;
            $videoFile->move(public_path('uploads/video_learning/videos'), $videoFileName);

            $item->video_path = 'uploads/video_learning/videos/' . $videoFileName;
            $item->video_name = $originalVideoName;
        }

        if ($request->hasFile('thumbnail')) {
            if ($item->thumbnail_path && File::exists(public_path($item->thumbnail_path))) {
                File::delete(public_path($item->thumbnail_path));
            }

            $thumbFile = $request->file('thumbnail');
            $thumbExt = $thumbFile->getClientOriginalExtension() ?: 'jpg';
            $thumbFileName = 'thumb_' . time() . '_' . Str::random(6) . '.' . $thumbExt;
            $thumbFile->move(public_path('uploads/video_learning/thumbnails'), $thumbFileName);

            $item->thumbnail_path = 'uploads/video_learning/thumbnails/' . $thumbFileName;
        } elseif ($request->filled('auto_thumbnail_base64')) {
            if ($item->thumbnail_path && File::exists(public_path($item->thumbnail_path))) {
                File::delete(public_path($item->thumbnail_path));
            }
            $item->thumbnail_path = $this->saveBase64Image($request->input('auto_thumbnail_base64'));
        }

        if ($request->filled('title')) {
            $item->title = $request->input('title');
        }

        $item->json_data = $this->formatJson($request->input('json_data'));
        $item->is_visible = $request->has('is_visible') ? (bool)$request->input('is_visible') : $item->is_visible;
        $item->updated_at = now();

        $item->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Video learning word updated successfully!']);
        }

        return redirect()->route('video-learning.index')->with('success', 'Video learning word updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse|JsonResponse
    {
        $item = VideoLearningWord::findOrFail($id);
        $item->delete(); // Model booted deleting listener cleans up files

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Video learning word deleted successfully!']);
        }

        return redirect()->route('video-learning.index')->with('success', 'Video learning word and files deleted successfully!');
    }

    /**
     * Toggle visibility (Show / Hide) via AJAX or standard request.
     */
    public function toggleVisibility(int $id): JsonResponse|RedirectResponse
    {
        $item = VideoLearningWord::findOrFail($id);
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
            VideoLearningWord::where('id', $id)->update(['order_index' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Record sequence updated successfully!',
        ]);
    }

    /**
     * Save base64 image string to public/uploads/video_learning/thumbnails
     */
    private function saveBase64Image(string $base64Data): ?string
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

            $thumbFileName = 'thumb_' . time() . '_' . Str::random(6) . '.' . $type;
            $destination = public_path('uploads/video_learning/thumbnails/' . $thumbFileName);

            File::put($destination, $decoded);

            return 'uploads/video_learning/thumbnails/' . $thumbFileName;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Generate default thumbnail using PHP GD library
     */
    private function generateDefaultThumbnail(string $text): string
    {
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
        $destination = public_path('uploads/video_learning/thumbnails/' . $thumbFileName);

        imagejpeg($img, $destination, 90);
        imagedestroy($img);

        return 'uploads/video_learning/thumbnails/' . $thumbFileName;
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
     * Ensure upload folders exist in public/uploads/
     */
    private function ensureDirectoriesExist(): void
    {
        $videoDir = public_path('uploads/video_learning/videos');
        $thumbDir = public_path('uploads/video_learning/thumbnails');

        if (!File::isDirectory($videoDir)) {
            File::makeDirectory($videoDir, 0755, true, true);
        }

        if (!File::isDirectory($thumbDir)) {
            File::makeDirectory($thumbDir, 0755, true, true);
        }
    }
}
