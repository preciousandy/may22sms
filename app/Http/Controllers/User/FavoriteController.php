<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|max:30',
            'key' => 'required|string|max:120',
            'label' => 'nullable|string|max:255',
        ]);

        $existing = Favorite::where('user_id', auth()->id())
            ->where('type', $validated['type'])
            ->where('key', $validated['key'])
            ->first();

        if ($existing) {
            $existing->delete();

            return response()->json(['favorited' => false]);
        }

        Favorite::create([
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'key' => $validated['key'],
            'label' => $validated['label'] ?? null,
        ]);

        return response()->json(['favorited' => true]);
    }

    public function index(Request $request)
    {
        $type = (string) $request->query('type', '');
        $query = Favorite::where('user_id', auth()->id());
        if ($type !== '') {
            $query->where('type', $type);
        }

        return response()->json($query->latest()->get());
    }
}
