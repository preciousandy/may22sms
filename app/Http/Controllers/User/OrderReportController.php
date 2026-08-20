<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\BoostOrder;
use App\Models\LogOrder;
use App\Models\OrderReport;
use App\Models\SmsOrder;
use App\Models\SystemTransaction;
use Illuminate\Http\Request;

class OrderReportController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_type' => 'required|in:sms,boost,logs',
            'order_id' => 'required|integer',
            'reason' => 'nullable|string|max:1000',
        ]);

        $userId = auth()->id();
        $type = $validated['order_type'];
        $orderId = (int) $validated['order_id'];
        $reason = trim((string) ($validated['reason'] ?? 'Issue reported by user.'));

        $order = null;
        if ($type === 'sms') {
            $order = SmsOrder::where('id', $orderId)->where('user_id', $userId)->first();
        } elseif ($type === 'boost') {
            $order = BoostOrder::where('id', $orderId)->where('user_id', $userId)->first();
        } elseif ($type === 'logs') {
            $order = LogOrder::where('id', $orderId)->where('user_id', $userId)->first();
        }

        if (!$order) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
            }
            return back()->with('error', 'Order not found.');
        }

        $st = strtolower((string) $order->status);
        if ($type !== 'logs' && in_array($st, ['received', 'completed', 'success'], true)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Completed or received orders cannot be reported.'], 422);
            }
            return back()->with('error', 'Completed or received orders cannot be reported.');
        }

        if ($order->is_reported) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Order issue has already been reported.'], 422);
            }
            return back()->with('error', 'Order issue has already been reported.');
        }

        // Mark order as reported
        $order->is_reported = true;
        $order->reported_at = now();
        $order->report_status = 'pending';
        $order->save();

        // Create Report Entry
        $report = OrderReport::create([
            'user_id' => $userId,
            'order_type' => $type,
            'order_id' => $order->id,
            'reason' => $reason,
            'status' => 'pending',
        ]);

        SystemTransaction::create([
            'actor_user_id' => $userId,
            'user_id' => $userId,
            'type' => 'order_report',
            'action' => 'submit',
            'status' => 'pending',
            'subject_type' => OrderReport::class,
            'subject_id' => (string) $report->id,
            'meta' => [
                'order_type' => $type,
                'order_id' => $order->id,
                'reason' => $reason,
            ],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Report submitted successfully. Admin is reviewing your transaction.',
                'reported_at' => $order->reported_at ? $order->reported_at->toIso8601String() : now()->toIso8601String(),
            ]);
        }

        return back()->with('success', 'Report submitted successfully. Admin team will review your order.');
    }
}
