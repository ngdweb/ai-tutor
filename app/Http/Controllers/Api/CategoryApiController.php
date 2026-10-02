<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\VideoLearningWord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryApiController extends Controller
{
    /** Videos previewed per category on the category listing. */
    private const PREVIEW_LIMIT = 5;

    /** Hard cap on page size so a single request can never load the whole table. */
    private const MAX_PER_PAGE = 200;

    /**
     * Get all active categories. Each category includes a preview of its
     * latest 5 visible videos plus a total_videos count.
     */
    public function index(Request $request): JsonResponse
    {
        // Only active categories that have at least one visible video are returned;
        // empty categories are excluded. Single query for counts (no N+1).
        $categories = Category::where('is_active', true)
            ->whereHas('videos', fn ($q) => $q->where('is_visible', true))
            ->withCount(['videos as total_videos' => fn ($q) => $q->where('is_visible', true)])
            ->orderBy('order_index', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        $data = $categories->map(function ($category) {
            $videos = $category->videos()
                ->where('is_visible', true)
                ->orderBy('order_index', 'asc')
                ->orderBy('updated_at', 'desc')
                ->orderBy('id', 'desc')
                ->limit(self::PREVIEW_LIMIT)
                ->get();

            return [
                'id'           => $category->id,
                'name'         => $category->name,
                'image_url'    => $category->image_url,
                'total_videos' => (int) $category->total_videos,
                'videos'       => $this->formatVideos($videos),
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Categories fetched successfully.',
            'total'   => $data->count(),
            'data'    => $data,
        ], 200);
    }

    /**
     * POST variant: category id is sent in the request body.
     *   POST /api/categories/videos   body: { "category_id": 5 }
     * Pass category_id = 0 to get ALL visible videos across every category.
     */
    public function videos(Request $request): JsonResponse
    {
        $categoryId = $request->input('category_id');

        if ($categoryId === null || $categoryId === '' || !is_numeric($categoryId)) {
            return response()->json([
                'status'  => false,
                'message' => 'category_id is required in the body (use 0 to fetch all videos).',
                'data'    => null,
            ], 422);
        }

        return $this->videosResponse($request, (int) $categoryId);
    }

    /**
     * GET variant (kept for backward compatibility): id in the URL.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        return $this->videosResponse($request, $id);
    }

    /**
     * Shared logic: return a category's visible videos. category id = 0 returns
     * ALL visible videos across every category.
     *
     * Optional pagination (omit for the full list): per_page / page
     * (per_page capped at 200), adds a "pagination" meta block.
     */
    private function videosResponse(Request $request, int $id): JsonResponse
    {
        $category = null;

        if ($id === 0) {
            $query = VideoLearningWord::query()->where('is_visible', true);
            $message = 'All video learning words fetched successfully.';
        } else {
            $category = Category::where('is_active', true)->find($id);
            if (!$category) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Category not found or is currently inactive.',
                    'data'    => null,
                ], 404);
            }
            $query = $category->videos()->getQuery()->where('is_visible', true);
            $message = 'Category videos fetched successfully.';
        }

        $query->orderBy('order_index', 'asc')
              ->orderBy('updated_at', 'desc')
              ->orderBy('id', 'desc');

        $categoryMeta = $category ? [
            'id'        => $category->id,
            'name'      => $category->name,
            'image_url' => $category->image_url,
        ] : null;

        $perPage = $this->resolvePerPage($request);

        if ($perPage) {
            $paginator = $query->paginate($perPage);

            return response()->json([
                'status'     => true,
                'message'    => $message,
                'category'   => $categoryMeta,
                'total'      => $paginator->total(),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page'     => $paginator->perPage(),
                    'last_page'    => $paginator->lastPage(),
                    'total'        => $paginator->total(),
                ],
                'data'       => $this->formatVideos($paginator->getCollection()),
            ], 200);
        }

        $videos = $query->get();

        return response()->json([
            'status'   => true,
            'message'  => $message,
            'category' => $categoryMeta,
            'total'    => $videos->count(),
            'data'     => $this->formatVideos($videos),
        ], 200);
    }

    /**
     * Resolve an optional page size. Returns null when pagination was not
     * requested (full list), otherwise an int capped at MAX_PER_PAGE.
     */
    private function resolvePerPage(Request $request): ?int
    {
        if (!$request->filled('per_page')) {
            return null;
        }

        $perPage = (int) $request->get('per_page');
        if ($perPage < 1) {
            return null;
        }

        return min($perPage, self::MAX_PER_PAGE);
    }

    /**
     * Format a collection of video records for API output.
     */
    private function formatVideos($videos)
    {
        return $videos->map(function ($item) {
            return [
                'id'            => $item->id,
                'category_id'   => $item->category_id,
                'video_url'     => $item->video_url,
                'thumbnail_url' => $item->thumbnail_url,
                'json_data'     => $item->parsed_json,
                'created_at'    => $item->created_at?->toIso8601String(),
                'updated_at'    => $item->updated_at?->toIso8601String(),
            ];
        })->values();
    }
}
