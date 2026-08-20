<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $type = (string) $request->query('type', 'sms');
        if (! in_array($type, ['sms', 'boost', 'logs'], true)) {
            $type = 'sms';
        }

        $q = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));

        $orders = null;

        if ($type === 'sms') {
            $query = auth()->user()->smsOrders()->latest();
            if ($status !== '') {
                $query->where('status', $status);
            }
            if ($q !== '') {
                $query->where(function ($sub) use ($q) {
                    $sub->where('phone_number', 'like', '%'.$q.'%')
                        ->orWhere('country', 'like', '%'.$q.'%')
                        ->orWhere('product', 'like', '%'.$q.'%')
                        ->orWhere('operator', 'like', '%'.$q.'%');
                });
            }
            $orders = $query->paginate(15)->withQueryString();
        } elseif ($type === 'boost') {
            $query = auth()->user()->boostOrders()->with('service')->latest();
            if ($status !== '') {
                $query->where('status', $status);
            }
            if ($q !== '') {
                $query->where('link', 'like', '%'.$q.'%');
            }
            $orders = $query->paginate(15)->withQueryString();
        } else {
            $query = auth()->user()->logOrders()->withCount('credentials')->latest();
            if ($status !== '') {
                $query->where('status', $status);
            }
            $orders = $query->paginate(15)->withQueryString();
        }

        return view('user.orders', compact('type', 'orders', 'q', 'status'));
    }
}
