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

        $items = $query->orderBy('order_index', 'asc')->orderBy('updated_at', 'desc')->get();

        $formattedData = $items->map(function ($item) {
            return [
                'id'            => $item->id,
                'video_url'     => $item->video_url,
                'thumbnail_url' => $item->thumbnail_url,
                'json_data'     => $item->parsed_json,
                'created_at'    => $item->created_at?->toIso8601String(),
                'updated_at'    => $item->updated_at?->toIso8601String(),
            ];
        });

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
