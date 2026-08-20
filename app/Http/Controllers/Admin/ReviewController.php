<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with('user')->latest();

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->rating);
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        $reviews = $query->paginate(20)->appends($request->query());

        $stats = [
            'total' => Review::count(),
            'avg_rating' => round((float) Review::avg('rating'), 1),
            'five_star' => Review::where('rating', 5)->count(),
            'four_star' => Review::where('rating', 4)->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return back()->with('success', 'Review deleted successfully.');
    }
}
