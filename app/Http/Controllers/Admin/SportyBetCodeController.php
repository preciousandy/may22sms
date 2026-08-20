<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SportyBetCode;
use Illuminate\Http\Request;

class SportyBetCodeController extends Controller
{
    public function index()
    {
        $codes = SportyBetCode::latest()->paginate(20);
        return view('admin.sporty-bet-codes.index', compact('codes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        SportyBetCode::create($validated);

        return back()->with('success', 'SportyBet code added successfully.');
    }

    public function toggle(SportyBetCode $code)
    {
        $code->update(['is_active' => !$code->is_active]);
        return back()->with('success', 'SportyBet code status updated.');
    }

    public function destroy(SportyBetCode $code)
    {
        $code->delete();
        return back()->with('success', 'SportyBet code deleted successfully.');
    }
}
