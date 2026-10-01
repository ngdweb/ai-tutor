<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VideoLearningWord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoLearningApiController extends Controller
{
    /**
     * Get all active (visible) video learning word records.
     * Hidden records (is_visible = 0) are excluded from the response.
     */
    public function index(Request $request): JsonResponse
    {
        $query = VideoLearningWord::query()->where('is_visible', true);

        // Optional search param for API consumers
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('video_name', 'like', "%{$search}%")
                  ->orWhere('json_data', 'like', "%{$search}%");
            });
        }

        $query->orderBy('updated_at', 'desc')->orderBy('id', 'desc');

        $format = fn ($item) => [
            'id'            => $item->id,
            'video_url'     => $item->video_url,
            'thumbnail_url' => $item->thumbnail_url,
            'json_data'     => $item->parsed_json,
            'created_at'    => $item->created_at?->toIso8601String(),
            'updated_at'    => $item->updated_at?->toIso8601String(),
        ];

        // Optional, backward-compatible pagination: ?per_page=50&page=2 (capped at 200).
        // Omit per_page to get the full list exactly as before.
        if ($request->filled('per_page') && (int) $request->get('per_page') >= 1) {
            $perPage = min((int) $request->get('per_page'), 200);
            $paginator = $query->paginate($perPage);

            return response()->json([
                'status'     => true,
                'message'    => 'Video learning word records fetched successfully.',
                'total'      => $paginator->total(),
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page'     => $paginator->perPage(),
                    'last_page'    => $paginator->lastPage(),
                    'total'        => $paginator->total(),
                ],
                'data'       => $paginator->getCollection()->map($format)->values(),
            ], 200);
        }

        $formattedData = $query->get()->map($format);

        return response()->json([
            'status'  => true,
            'message' => 'Video learning word records fetched successfully.',
            'total'   => $formattedData->count(),
            'data'    => $formattedData,
        ], 200);
    }

    /**
     * Get single visible record by ID
     */
    public function show(int $id): JsonResponse
    {
        $item = VideoLearningWord::where('is_visible', true)->find($id);

        if (!$item) {
            return response()->json([
                'status'  => false,
                'message' => 'Video learning word record not found or is currently hidden.',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Video learning word fetched successfully.',
            'data'    => [
                'id'            => $item->id,
                'video_url'     => $item->video_url,
                'thumbnail_url' => $item->thumbnail_url,
                'json_data'     => $item->parsed_json,
                'created_at'    => $item->created_at?->toIso8601String(),
                'updated_at'    => $item->updated_at?->toIso8601String(),
            ],
        ], 200);
    }
}
