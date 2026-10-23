<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LogCategoryVideo;
use Illuminate\Http\Request;

class TutorialController extends Controller
{
    /**
     * Display the Tutorials and How-It-Works knowledge center for all services.
     */
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'sms');

        // Dynamic category video tutorials if configured by admin
        $categoryVideos = LogCategoryVideo::where('is_active', true)
            ->latest()
            ->get()
            ->groupBy('platform');

        return view('user.tutorials', compact('activeTab', 'categoryVideos'));
    }
}
