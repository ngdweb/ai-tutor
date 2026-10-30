<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\VideoLearningWord;
use App\Models\LearnupCategory;
use App\Models\LearnupWord;
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
        $sampleCategory = Category::where('is_active', true)->first();

        $endpoints = [
            [
                'name'        => 'Get All Categories',
                'method'      => 'GET',
                'endpoint'    => '/api/categories',
                'full_url'    => url('/api/categories'),
                'description' => 'Fetches all active categories. Each category includes a preview of its latest 5 visible videos plus a total_videos count.',
                'params'      => [],
                'headers'     => [
                    'Authorization' => 'Bearer <YOUR_API_TOKEN>',
                    'Accept'        => 'application/json',
                ],
                'sample_response' => [
                    'status'  => true,
                    'message' => 'Categories fetched successfully.',
                    'total'   => Category::where('is_active', true)->count(),
                    'data'    => [
                        [
                            'id'           => $sampleCategory?->id ?? 1,
                            'name'         => $sampleCategory?->name ?? 'General',
                            'image_url'    => $sampleCategory?->image_url,
                            'total_videos' => $sampleCategory ? $sampleCategory->videos()->where('is_visible', true)->count() : 0,
                            'videos'       => [
                                [
                                    'id'            => $sampleRecord?->id ?? 1,
                                    'category_id'   => $sampleRecord?->category_id ?? 1,
                                    'video_url'     => $sampleRecord?->video_url ?? url('uploads/video_learning/videos/sample.mp4'),
                                    'thumbnail_url' => $sampleRecord?->thumbnail_url ?? url('uploads/video_learning/thumbnails/sample.jpg'),
                                    'json_data'     => $sampleRecord?->parsed_json ?? ['word' => 'Apple'],
                                    'created_at'    => $sampleRecord?->created_at?->toIso8601String() ?? now()->toIso8601String(),
                                    'updated_at'    => $sampleRecord?->updated_at?->toIso8601String() ?? now()->toIso8601String(),
                                ]
                            ],
                        ]
                    ]
                ]
            ],
            [
                'name'        => 'Get Category Videos (POST, category_id in body)',
                'method'      => 'POST',
                'endpoint'    => '/api/categories/videos',
                'full_url'    => url('/api/categories/videos'),
                'description' => 'Fetches ALL visible videos of a single category. Send category_id in the request body. Pass category_id = 0 to return every visible video across all categories. (The old GET /api/categories/{id} still works.)',
                'params'      => [
                    [
                        'name'        => 'category_id',
                        'type'        => 'integer (body)',
                        'required'    => true,
                        'description' => 'Category ID in the JSON body. Use 0 to fetch all videos from every category.'
                    ]
                ],
                'headers'     => [
                    'Authorization' => 'Bearer <YOUR_API_TOKEN>',
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                ],
                'sample_body' => [
                    'category_id' => $sampleCategory?->id ?? 1,
                ],
                'sample_response' => [
                    'status'   => true,
                    'message'  => 'Category videos fetched successfully.',
                    'category' => [
                        'id'        => $sampleCategory?->id ?? 1,
                        'name'      => $sampleCategory?->name ?? 'General',
                        'image_url' => $sampleCategory?->image_url,
                    ],
                    'total'    => $sampleCategory ? $sampleCategory->videos()->where('is_visible', true)->count() : 0,
                    'data'     => [
                        [
                            'id'            => $sampleRecord?->id ?? 1,
                            'category_id'   => $sampleRecord?->category_id ?? 1,
                            'video_url'     => $sampleRecord?->video_url ?? url('uploads/video_learning/videos/sample.mp4'),
                            'thumbnail_url' => $sampleRecord?->thumbnail_url ?? url('uploads/video_learning/thumbnails/sample.jpg'),
                            'json_data'     => $sampleRecord?->parsed_json ?? ['word' => 'Apple'],
                            'created_at'    => $sampleRecord?->created_at?->toIso8601String() ?? now()->toIso8601String(),
                            'updated_at'    => $sampleRecord?->updated_at?->toIso8601String() ?? now()->toIso8601String(),
                        ]
                    ]
                ]
            ],
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

        $pageTitle = 'Video Learning API';

        return view('api_list.index', compact('endpoints', 'visibleCount', 'pageTitle'));
    }

    /**
     * LearnUp with AI API Documentation & List screen.
     */
    public function learnup(Request $request): View
    {
        $visibleCount   = LearnupWord::where('is_visible', true)->count();
        $sampleRecord   = LearnupWord::where('is_visible', true)->first();
        $sampleCategory = LearnupCategory::where('is_active', true)->first();

        $endpoints = [
            [
                'name'        => 'Get All LearnUp Categories',
                'method'      => 'GET',
                'endpoint'    => '/api/learnup-categories',
                'full_url'    => url('/api/learnup-categories'),
                'description' => 'Fetches all active LearnUp categories. Each includes a preview of its latest 5 visible items plus a total_videos count.',
                'params'      => [],
                'headers'     => [
                    'Authorization' => 'Bearer <YOUR_API_TOKEN>',
                    'Accept'        => 'application/json',
                ],
                'sample_response' => [
                    'status'  => true,
                    'message' => 'Categories fetched successfully.',
                    'total'   => LearnupCategory::where('is_active', true)->count(),
                    'data'    => [
                        [
                            'id'           => $sampleCategory?->id ?? 1,
                            'name'         => $sampleCategory?->name ?? 'General',
                            'image_url'    => $sampleCategory?->image_url,
                            'total_videos' => $sampleCategory ? $sampleCategory->videos()->where('is_visible', true)->count() : 0,
                            'videos'       => [
                                [
                                    'id'            => $sampleRecord?->id ?? 1,
                                    'category_id'   => $sampleRecord?->category_id ?? 1,
                                    'video_url'     => $sampleRecord?->video_url ?? url('uploads/learnup_with_ai/general/videos/sample.mp4'),
                                    'thumbnail_url' => $sampleRecord?->thumbnail_url ?? url('uploads/learnup_with_ai/general/thumbnails/sample.jpg'),
                                    'json_data'     => $sampleRecord?->parsed_json ?? ['word' => 'Apple'],
                                    'created_at'    => $sampleRecord?->created_at?->toIso8601String() ?? now()->toIso8601String(),
                                    'updated_at'    => $sampleRecord?->updated_at?->toIso8601String() ?? now()->toIso8601String(),
                                ]
                            ],
                        ]
                    ]
                ]
            ],
            [
                'name'        => 'Get LearnUp Category Videos (POST, category_id in body)',
                'method'      => 'POST',
                'endpoint'    => '/api/learnup-categories/videos',
                'full_url'    => url('/api/learnup-categories/videos'),
                'description' => 'Fetches ALL visible items of a single LearnUp category. Send category_id in the body. Pass category_id = 0 to return every visible item across all categories.',
                'params'      => [
                    [
                        'name'        => 'category_id',
                        'type'        => 'integer (body)',
                        'required'    => true,
                        'description' => 'Category ID in the JSON body. Use 0 to fetch all items from every category.'
                    ]
                ],
                'headers'     => [
                    'Authorization' => 'Bearer <YOUR_API_TOKEN>',
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                ],
                'sample_body' => [
                    'category_id' => $sampleCategory?->id ?? 1,
                ],
                'sample_response' => [
                    'status'   => true,
                    'message'  => 'Category videos fetched successfully.',
                    'category' => [
                        'id'        => $sampleCategory?->id ?? 1,
                        'name'      => $sampleCategory?->name ?? 'General',
                        'image_url' => $sampleCategory?->image_url,
                    ],
                    'total'    => $sampleCategory ? $sampleCategory->videos()->where('is_visible', true)->count() : 0,
                    'data'     => [],
                ]
            ],
            [
                'name'        => 'Get All LearnUp Items',
                'method'      => 'GET',
                'endpoint'    => '/api/learnup-words',
                'full_url'    => url('/api/learnup-words'),
                'description' => 'Fetches all active (visible) LearnUp items in saved sequence order. Excludes hidden records.',
                'params'      => [
                    [
                        'name'        => 'search',
                        'type'        => 'string',
                        'required'    => false,
                        'description' => 'Optional filter by name or JSON content.'
                    ]
                ],
                'headers'     => [
                    'Authorization' => 'Bearer <YOUR_API_TOKEN>',
                    'Accept'        => 'application/json',
                ],
                'sample_response' => [
                    'status'  => true,
                    'message' => 'LearnUp records fetched successfully.',
                    'total'   => $visibleCount,
                    'data'    => [
                        [
                            'id'            => $sampleRecord?->id ?? 1,
                            'video_url'     => $sampleRecord?->video_url ?? url('uploads/learnup_with_ai/general/videos/sample.mp4'),
                            'thumbnail_url' => $sampleRecord?->thumbnail_url ?? url('uploads/learnup_with_ai/general/thumbnails/sample.jpg'),
                            'json_data'     => $sampleRecord?->parsed_json ?? ['word' => 'Apple'],
                            'created_at'    => $sampleRecord?->created_at?->toIso8601String() ?? now()->toIso8601String(),
                            'updated_at'    => $sampleRecord?->updated_at?->toIso8601String() ?? now()->toIso8601String(),
                        ]
                    ]
                ]
            ],
            [
                'name'        => 'Get Single LearnUp Item',
                'method'      => 'GET',
                'endpoint'    => '/api/learnup-words/{id}',
                'full_url'    => url('/api/learnup-words/' . ($sampleRecord?->id ?? 1)),
                'description' => 'Fetches detailed information for a single LearnUp item by ID. Returns 404 if not found or hidden.',
                'params'      => [
                    [
                        'name'        => 'id',
                        'type'        => 'integer',
                        'required'    => true,
                        'description' => 'Unique ID of the LearnUp record.'
                    ]
                ],
                'headers'     => [
                    'Authorization' => 'Bearer <YOUR_API_TOKEN>',
                    'Accept'        => 'application/json',
                ],
                'sample_response' => [
                    'status'  => true,
                    'message' => 'LearnUp record fetched successfully.',
                    'data'    => [
                        'id'            => $sampleRecord?->id ?? 1,
                        'video_url'     => $sampleRecord?->video_url ?? url('uploads/learnup_with_ai/general/videos/sample.mp4'),
                        'thumbnail_url' => $sampleRecord?->thumbnail_url ?? url('uploads/learnup_with_ai/general/thumbnails/sample.jpg'),
                        'json_data'     => $sampleRecord?->parsed_json ?? ['word' => 'Apple'],
                        'created_at'    => $sampleRecord?->created_at?->toIso8601String() ?? now()->toIso8601String(),
                        'updated_at'    => $sampleRecord?->updated_at?->toIso8601String() ?? now()->toIso8601String(),
                    ]
                ]
            ]
        ];

        $pageTitle = 'LearnUp with AI API';

        return view('api_list.index', compact('endpoints', 'visibleCount', 'pageTitle'));
    }
}
