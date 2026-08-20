<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

class WalletController extends Controller
{
    public function index()
    {
        $transactions = auth()->user()
            ->walletTransactions()
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('user.wallet', compact('transactions'));
    }
}
