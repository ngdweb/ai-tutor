<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories with search and pagination.
     */
    public function index(Request $request): View|\Illuminate\Http\Response
    {
        $query = Category::query()->withCount('videos');

        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active' || $status === '1') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive' || $status === '0') {
                $query->where('is_active', false);
            }
        }

        $items = $query->orderBy('order_index', 'asc')->orderBy('id', 'desc')->paginate(10)->withQueryString();

        $stats = [
            'total'    => Category::count(),
            'active'   => Category::where('is_active', true)->count(),
            'inactive' => Category::where('is_active', false)->count(),
        ];

        // Full unpaginated list (in saved sequence) for the drag-and-drop reorder popup.
        $allCategories = Category::withCount('videos')
            ->orderBy('order_index', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        // AJAX search/pagination: return only the table fragment from the same view.
        if ($request->ajax()) {
            return response(
                view('categories.index', compact('items', 'stats', 'allCategories'))->fragment('categoryTable')
            );
        }

        return view('categories.index', compact('items', 'stats', 'allCategories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        return view('categories.form');
    }

    /**
     * Show the form for editing an existing category.
     */
    public function edit(int $id): View
    {
        $record = Category::findOrFail($id);
        return view('categories.form', compact('record'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'image'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            'is_active' => 'nullable',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->storeImage($request->file('image'), $request->input('name'));
        }

        // Place new categories at the FRONT of the sequence so the latest
        // created category shows first (until manually reordered).
        Category::create([
            'name'        => $request->input('name'),
            'image_path'  => $imagePath,
            'is_active'   => $request->has('is_active') ? (bool) $request->input('is_active') : true,
            'order_index' => (int) (Category::min('order_index') - 1),
        ]);

        return redirect()->route('categories.index')->with('success', 'Category created successfully!');
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name'      => 'required|string|max:255',
            'image'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            'is_active' => 'nullable',
        ]);

        if ($request->hasFile('image')) {
            if ($category->image_path && File::exists(public_path($category->image_path))) {
                File::delete(public_path($category->image_path));
            }
            $category->image_path = $this->storeImage($request->file('image'), $request->input('name'));
        }

        $category->name = $request->input('name');
        $category->is_active = $request->has('is_active') ? (bool) $request->input('is_active') : $category->is_active;
        $category->save();

        return redirect()->route('categories.index')->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified category AND permanently delete all of its videos
     * (including their physical video/thumbnail files). The default "General"
     * category is protected from deletion.
     */
    public function destroy(int $id): RedirectResponse|JsonResponse
    {
        $category = Category::findOrFail($id);

        // Protect the default General category from deletion.
        $generalId = Category::where('name', 'General')->value('id');
        if ($generalId === $category->id) {
            $msg = 'The "General" category cannot be deleted as it is the default fallback.';
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        // Delete every video in this category; each model's deleting hook
        // cleans up its video + thumbnail files from disk.
        $deletedVideos = 0;
        $category->videos()->get()->each(function ($video) use (&$deletedVideos) {
            $video->delete();
            $deletedVideos++;
        });

        // Deleting the category also removes its stored image (model hook).
        $category->delete();

        $msg = "Category and its {$deletedVideos} video(s) were deleted successfully!";
        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->route('categories.index')->with('success', $msg);
    }

    /**
     * Toggle active status (On / Off) via AJAX.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category->is_active = !$category->is_active;
        $category->save();

        return response()->json([
            'success'   => true,
            'is_active' => $category->is_active,
            'message'   => 'Status updated to ' . ($category->is_active ? 'On (Active)' : 'Off (Inactive)'),
        ]);
    }

    /**
     * Persist a new category display sequence (from the drag-and-drop popup).
     * The saved order_index drives both the admin list and the category API.
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'ordered_ids'   => 'required|array',
            'ordered_ids.*' => 'integer',
        ]);

        foreach ($request->input('ordered_ids') as $index => $id) {
            Category::where('id', $id)->update(['order_index' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category sequence updated successfully!',
        ]);
    }

    /**
     * Store an uploaded category image under
     *   public/uploads/video_learning_with_word/<category-name>/category
     * keeping the original file name (collisions get a numeric suffix).
     * Returns the relative public path.
     */
    private function storeImage($file, string $categoryName): string
    {
        $slug = Str::slug($categoryName) ?: 'general';
        $relativeDir = 'uploads/video_learning_with_word/' . $slug . '/category';
        $dir = public_path($relativeDir);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $ext  = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'category';

        // Preserve the original name; only add a suffix if a file already exists.
        $fileName = $base . '.' . $ext;
        $counter  = 1;
        while (File::exists($dir . DIRECTORY_SEPARATOR . $fileName)) {
            $fileName = $base . '-' . $counter . '.' . $ext;
            $counter++;
        }

        $file->move($dir, $fileName);

        return $relativeDir . '/' . $fileName;
    }
}
