<?php

namespace App\Http\Controllers;

use App\Models\VideoLearningWord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $totalVideos = VideoLearningWord::count();
        $visibleVideos = VideoLearningWord::where('is_visible', true)->count();
        $hiddenVideos = VideoLearningWord::where('is_visible', false)->count();

        $stats = [
            'total_users'     => 1,
            'active_users'    => 1,
            'total_videos'    => $totalVideos,
            'visible_videos'  => $visibleVideos,
            'hidden_videos'   => $hiddenVideos,
            'total_revenue'   => '$12,540',
            'total_orders'    => 284,
        ];

        $recentVideoItems = VideoLearningWord::orderBy('updated_at', 'desc')->orderBy('id', 'desc')->take(5)->get();

        $recentActivity = [
            ['icon' => 'video', 'text' => "Video Learning module active ({$totalVideos} items)", 'time' => 'Live', 'color' => 'purple'],
            ['icon' => 'user', 'text' => 'Admin logged in', 'time' => 'Just now', 'color' => 'blue'],
        ];

        return view('dashboard.index', compact('stats', 'recentActivity', 'recentVideoItems'));
    }

    public function profile()
    {
        return view('dashboard.profile', ['user' => Auth::user()]);
    }

    public function settings()
    {
        return view('dashboard.settings');
    }
}
