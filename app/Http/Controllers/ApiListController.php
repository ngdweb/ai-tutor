<?php

namespace App\Http\Controllers;

use App\Models\VideoLearningWord;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiListController extends Controller
{
    /**
     * Display the API Documentation & List screen with live testing.
     */
    public function index(Request $request): View
    {
        $visibleCount = VideoLearningWord::where('is_visible', true)->count();
        $sampleRecord = VideoLearningWord::where('is_visible', true)->first();

        $endpoints = [
            [
                'name'        => 'Get All Video Learning Words',
                'method'      => 'GET',
                'endpoint'    => '/api/video-learning-words',
                'full_url'    => url('/api/video-learning-words'),
                'description' => 'Fetches all active (visible) video learning words in saved sequence order (order_index ASC, updated_at DESC). Excludes hidden records.',
                'params'      => [
                    [
                        'name'        => 'search',
                        'type'        => 'string',
                        'required'    => false,
                        'description' => 'Optional filter by word name or JSON content.'
                    ]
                ],
                'headers'     => [
                    'Authorization' => 'Bearer <YOUR_API_TOKEN>',
                    'Accept'        => 'application/json',
                ],
                'sample_response' => [
                    'status'  => true,
                    'message' => 'Video learning word records fetched successfully.',
                    'total'   => $visibleCount,
                    'data'    => [
                        [
                            'id'            => $sampleRecord?->id ?? 1,
                            'video_url'     => $sampleRecord?->video_url ?? url('uploads/video_learning/videos/sample.mp4'),
                            'thumbnail_url' => $sampleRecord?->thumbnail_url ?? url('uploads/video_learning/thumbnails/sample.jpg'),
                            'json_data'     => $sampleRecord?->parsed_json ?? ['word' => 'Apple', 'phonetic' => '/ˈæp.əl/'],
                            'created_at'    => $sampleRecord?->created_at?->toIso8601String() ?? now()->toIso8601String(),
                            'updated_at'    => $sampleRecord?->updated_at?->toIso8601String() ?? now()->toIso8601String(),
                        ]
                    ]
                ]
            ],
            [
                'name'        => 'Get Single Video Learning Word',
                'method'      => 'GET',
                'endpoint'    => '/api/video-learning-words/{id}',
                'full_url'    => url('/api/video-learning-words/' . ($sampleRecord?->id ?? 1)),
                'description' => 'Fetches detailed information for a single video learning item by ID. Returns 404 if not found or hidden. Requires Bearer token in Authorization header.',
                'params'      => [
                    [
                        'name'        => 'id',
                        'type'        => 'integer',
                        'required'    => true,
                        'description' => 'Unique ID of the video learning record.'
                    ]
                ],
                'headers'     => [
                    'Authorization' => 'Bearer <YOUR_API_TOKEN>',
                    'Accept'        => 'application/json',
                ],
                'sample_response' => [
                    'status'  => true,
                    'message' => 'Video learning word fetched successfully.',
                    'data'    => [
                        'id'            => $sampleRecord?->id ?? 1,
                        'video_url'     => $sampleRecord?->video_url ?? url('uploads/video_learning/videos/sample.mp4'),
                        'thumbnail_url' => $sampleRecord?->thumbnail_url ?? url('uploads/video_learning/thumbnails/sample.jpg'),
                        'json_data'     => $sampleRecord?->parsed_json ?? ['word' => 'Apple', 'phonetic' => '/ˈæp.əl/'],
                        'created_at'    => $sampleRecord?->created_at?->toIso8601String() ?? now()->toIso8601String(),
                        'updated_at'    => $sampleRecord?->updated_at?->toIso8601String() ?? now()->toIso8601String(),
                    ]
                ]
            ]
        ];

        return view('api_list.index', compact('endpoints', 'visibleCount'));
    }
}
