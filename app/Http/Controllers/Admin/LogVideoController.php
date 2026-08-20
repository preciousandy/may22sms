<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogCategoryVideo;
use App\Models\LogItem;
use App\Models\SystemTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LogVideoController extends Controller
{
    public function index()
    {
        $videos = LogCategoryVideo::latest()->get();

        $existingPlatforms = LogItem::distinct()->pluck('platform')->toArray();
        $defaultPlatforms = ['facebook', 'tiktok', 'instagram', 'twitter', 'youtube', 'discord', 'other'];
        $platforms = array_values(array_unique(array_merge($defaultPlatforms, array_map('strtolower', $existingPlatforms))));

        return view('admin.logs.videos', compact('videos', 'platforms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'platform' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'video_url' => 'nullable|string|max:1000',
            'video_file' => 'nullable|file|mimes:mp4,webm,ogg,mov,avi|max:51200',
        ]);

        if (! $request->filled('video_url') && ! $request->hasFile('video_file')) {
            return back()->withErrors(['video_url' => 'Please provide either a video URL link or upload a video file.']);
        }

        $videoPath = null;
        if ($request->hasFile('video_file')) {
            $videoPath = $request->file('video_file')->store('log_videos', 'public');
        }

        $video = LogCategoryVideo::create([
            'platform' => strtolower(trim($request->platform)),
            'title' => trim($request->title),
            'video_url' => $request->video_url ? trim($request->video_url) : null,
            'video_file' => $videoPath,
            'is_active' => true,
        ]);

        SystemTransaction::create([
            'actor_user_id' => auth()->id(),
            'type' => 'log_category_video',
            'action' => 'create',
            'status' => 'completed',
            'subject_type' => LogCategoryVideo::class,
            'subject_id' => (string) $video->id,
            'after' => [
                'platform' => $video->platform,
                'title' => $video->title,
            ],
        ]);

        return back()->with('success', 'Category video guide added successfully.');
    }

    public function update(Request $request, $id)
    {
        $video = LogCategoryVideo::findOrFail($id);

        $request->validate([
            'platform' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'video_url' => 'nullable|string|max:1000',
            'video_file' => 'nullable|file|mimes:mp4,webm,ogg,mov,avi|max:51200',
        ]);

        if ($request->hasFile('video_file')) {
            if ($video->video_file && Storage::disk('public')->exists($video->video_file)) {
                Storage::disk('public')->delete($video->video_file);
            }
            $video->video_file = $request->file('video_file')->store('log_videos', 'public');
        }

        $video->platform = strtolower(trim($request->platform));
        $video->title = trim($request->title);
        if ($request->filled('video_url')) {
            $video->video_url = trim($request->video_url);
        }
        $video->save();

        return back()->with('success', 'Category video guide updated successfully.');
    }

    public function toggle($id)
    {
        $video = LogCategoryVideo::findOrFail($id);
        $video->is_active = ! $video->is_active;
        $video->save();

        $status = $video->is_active ? 'enabled' : 'disabled';

        return back()->with('success', "Video guide '{$video->title}' is now {$status}.");
    }

    public function destroy($id)
    {
        $video = LogCategoryVideo::findOrFail($id);

        if ($video->video_file && Storage::disk('public')->exists($video->video_file)) {
            Storage::disk('public')->delete($video->video_file);
        }

        $video->delete();

        return back()->with('success', 'Category video guide deleted successfully.');
    }
}
