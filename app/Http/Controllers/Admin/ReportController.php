<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BoostOrder;
use App\Models\LogOrder;
use App\Models\OrderReport;
use App\Models\SmsOrder;
use App\Models\SystemTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $status = (string) $request->query('status', 'pending');
        $type = (string) $request->query('type', 'all');
        $q = trim((string) $request->query('q', ''));

        $query = OrderReport::with(['user'])->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($type !== 'all') {
            $query->where('order_type', $type);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $like = '%' . $q . '%';
                $sub->where('reason', 'like', $like)
                    ->orWhere('id', (int) $q)
                    ->orWhere('order_id', (int) $q)
                    ->orWhereHas('user', function ($uQuery) use ($like, $q) {
                        $uQuery->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like);
                        if (is_numeric($q)) {
                            $uQuery->orWhere('id', (int) $q);
                        }
                    });
            });
        }

        $reports = $query->paginate(20)->appends($request->query());

        $pendingCount = OrderReport::where('status', 'pending')->count();
        $resolvedCount = OrderReport::where('status', 'resolved')->count();
        $dismissedCount = OrderReport::where('status', 'dismissed')->count();

        return view('admin.reports.index', compact(
            'reports',
            'status',
            'type',
            'q',
            'pendingCount',
            'resolvedCount',
            'dismissedCount'
        ));
    }

    public function action(Request $request, $id, WalletService $wallet)
    {
        $report = OrderReport::with('user')->findOrFail($id);

        $validated = $request->validate([
            'action' => 'required|in:refund,resolve,dismiss',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $act = $validated['action'];
        $notes = trim((string) ($validated['admin_notes'] ?? ''));

        $order = $report->order;
        $orderType = $report->order_type;

        if ($act === 'refund') {
            if ($order && $report->user) {
                $amount = 0.00;
                $refPrefix = 'REFUND-REPORT-';
                if ($orderType === 'sms') {
                    $amount = (float) ($order->price ?? 0);
                    $refPrefix = 'SMSREFUND-REPORT-';
                    $order->status = 'CANCELED';
                } elseif ($orderType === 'boost') {
                    $amount = (float) ($order->charge ?? 0);
                    $refPrefix = 'BOOSTREFUND-REPORT-';
                    $order->status = 'CANCELED';
                } elseif ($orderType === 'logs') {
                    $amount = (float) ($order->total_amount ?? 0);
                    $refPrefix = 'LOGREFUND-REPORT-';
                    $order->status = 'CANCELED';
                }

                if ($amount > 0) {
                    $ref = $refPrefix . $order->id;
                    try {
                        $wallet->credit($report->user, $amount, $orderType . '_refund', $ref, [
                            'report_id' => $report->id,
                            'order_id' => $order->id,
                            'admin_id' => auth()->id(),
                            'notes' => $notes ?: 'Admin refunded reported order.',
                        ]);
                    } catch (\Throwable $e) {
                        // Ignore duplicate wallet credit exception if already credited
                    }
                }

                $order->report_status = 'resolved';
                $order->save();
            }

            $report->status = 'resolved';
            $report->admin_notes = $notes ?: 'Refund issued to user wallet.';
            $report->save();

            $msg = 'Report resolved and refund issued to user wallet.';
        } elseif ($act === 'resolve') {
            if ($order) {
                $order->report_status = 'resolved';
                $order->save();
            }

            $report->status = 'resolved';
            $report->admin_notes = $notes ?: 'Report resolved by admin.';
            $report->save();

            $msg = 'Report marked as resolved.';
        } else {
            if ($order) {
                $order->report_status = 'dismissed';
                $order->save();
            }

            $report->status = 'dismissed';
            $report->admin_notes = $notes ?: 'Report dismissed by admin.';
            $report->save();

            $msg = 'Report dismissed.';
        }

        SystemTransaction::create([
            'actor_user_id' => auth()->id(),
            'user_id' => $report->user_id,
            'type' => 'order_report',
            'action' => $act,
            'status' => 'completed',
            'subject_type' => OrderReport::class,
            'subject_id' => (string) $report->id,
            'meta' => [
                'action' => $act,
                'admin_notes' => $notes,
            ],
        ]);

        return back()->with('success', $msg);
    }
}
