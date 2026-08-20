<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'service_type' => 'nullable|string|max:50',
            'review_text' => 'nullable|string|max:1000',
        ]);

        $review = Review::create([
            'user_id' => auth()->id(),
            'rating' => (int) $validated['rating'],
            'service_type' => $validated['service_type'] ?? 'general',
            'review_text' => $validated['review_text'] ?? null,
            'is_approved' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you! Your review and rating have been submitted.',
            'review' => $review,
        ]);
    }

    public function checkStatus(): JsonResponse
    {
        $hasReviewed = Review::where('user_id', auth()->id())->exists();

        return response()->json([
            'success' => true,
            'has_reviewed' => $hasReviewed,
        ]);
    }
}
