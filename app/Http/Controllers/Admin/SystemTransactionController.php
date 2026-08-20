<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemTransaction;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SystemTransactionController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $type = (string) $request->query('type', 'all');
        $status = (string) $request->query('status', 'all');
        $action = (string) $request->query('action', 'all');

        $query = SystemTransaction::query()
            ->with(['actor:id,name,email,is_admin', 'user:id,name,email'])
            ->orderByDesc('id');

        if ($type !== 'all') {
            $query->where('type', $type);
        }
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($action !== 'all') {
            $query->where('action', $action);
        }

        if ($q !== '') {
            $like = '%' . $q . '%';
            $query->where(function ($sub) use ($q, $like) {
                $sub->where('reference', 'like', $like)
                    ->orWhere('subject_id', 'like', $like)
                    ->orWhere('subject_type', 'like', $like);

                if (is_numeric($q)) {
                    $sub->orWhere('id', (int) $q)
                        ->orWhere('user_id', (int) $q)
                        ->orWhere('actor_user_id', (int) $q);
                }

                $sub->orWhereHas('user', function ($u) use ($like) {
                    $u->where('email', 'like', $like)->orWhere('name', 'like', $like);
                });
                $sub->orWhereHas('actor', function ($u) use ($like) {
                    $u->where('email', 'like', $like)->orWhere('name', 'like', $like);
                });
            });
        }

        $transactions = $query->paginate(50);
        $transactions->appends($request->query());

        $typeOptions = SystemTransaction::select('type')->distinct()->orderBy('type')->pluck('type')->values();
        $statusOptions = SystemTransaction::select('status')->distinct()->orderBy('status')->pluck('status')->filter()->values();
        $actionOptions = SystemTransaction::select('action')->distinct()->orderBy('action')->pluck('action')->values();

        return view('admin.transactions.index', compact('transactions', 'q', 'type', 'status', 'action', 'typeOptions', 'statusOptions', 'actionOptions'));
    }

    public function show($id)
    {
        $tx = SystemTransaction::with(['actor:id,name,email,is_admin', 'user:id,name,email'])->findOrFail($id);

        return view('admin.transactions.show', compact('tx'));
    }

    public function update(Request $request, $id)
    {
        $tx = SystemTransaction::findOrFail($id);

        $validated = $request->validate([
            'status' => 'nullable|string|max:40',
            'note' => 'nullable|string|max:500',
        ]);

        if (array_key_exists('status', $validated)) {
            $tx->status = $validated['status'] !== '' ? $validated['status'] : null;
        }

        $meta = is_array($tx->meta ?? null) ? $tx->meta : [];
        if (($validated['note'] ?? '') !== '') {
            $meta['admin_note'] = (string) $validated['note'];
            $meta['admin_note_by'] = auth()->id();
            $meta['admin_note_at'] = now()->toDateTimeString();
            $tx->meta = $meta;
        }

        $tx->save();

        return back()->with('success', 'Transaction updated.');
    }

    public function approve($id)
    {
        $tx = SystemTransaction::findOrFail($id);

        if ($tx->approved_at !== null) {
            return back()->with('error', 'Already approved.');
        }

        $tx->approved_at = now();
        $tx->approved_by = auth()->id();
        $tx->status = $tx->status ?: 'approved';
        $tx->save();

        return back()->with('success', 'Transaction approved.');
    }

    public function destroy($id)
    {
        $tx = SystemTransaction::findOrFail($id);
        $tx->delete();

        return back()->with('success', 'Transaction removed from log.');
    }

    public function revert($id, WalletService $wallet)
    {
        $tx = SystemTransaction::findOrFail($id);

        if (! $tx->reversible) {
            return back()->with('error', 'This transaction cannot be reverted.');
        }
        if ($tx->reverted_at !== null) {
            return back()->with('error', 'This transaction is already reverted.');
        }
        if ($tx->type !== 'wallet') {
            return back()->with('error', 'Revert is supported only for wallet transactions.');
        }

        $user = User::find($tx->user_id);
        if (! $user) {
            return back()->with('error', 'User not found.');
        }

        $amount = (float) ($tx->amount_usd ?? 0);
        if ($amount == 0.0) {
            return back()->with('error', 'Nothing to revert.');
        }

        DB::beginTransaction();
        try {
            $wallet->apply($user, -$amount, 'reversal', 'revert:system_tx:' . $tx->id, [
                'reverts_system_transaction_id' => $tx->id,
                'admin_id' => auth()->id(),
            ]);

            $tx->reverted_at = now();
            $tx->reverted_by = auth()->id();
            $tx->status = 'reverted';

            $meta = is_array($tx->meta ?? null) ? $tx->meta : [];
            $meta['reverted_reason'] = 'admin_revert';
            $tx->meta = $meta;

            $tx->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            $msg = $e->getMessage() === 'insufficient_funds' ? 'Cannot revert: insufficient wallet balance.' : 'Failed to revert transaction.';
            return back()->with('error', $msg);
        }

        return back()->with('success', 'Transaction reverted.');
    }

    public function backfill(Request $request)
    {
        $limit = (int) $request->input('limit', 5000);
        if ($limit < 1) {
            $limit = 5000;
        }
        if ($limit > 50000) {
            $limit = 50000;
        }

        $created = 0;

        $created += $this->backfillByTable(
            $limit,
            'wallet_transactions',
            WalletTransaction::class,
            function (array $rows): array {
                $payload = [];
                foreach ($rows as $row) {
                    $meta = [];
                    if (! empty($row->meta)) {
                        $decoded = json_decode((string) $row->meta, true);
                        if (is_array($decoded)) {
                            $meta = $decoded;
                        }
                    }

                    $actorUserId = $row->user_id;
                    if (array_key_exists('admin_id', $meta) && $meta['admin_id']) {
                        $actorUserId = (int) $meta['admin_id'];
                    }

                    $payload[] = [
                        'actor_user_id' => $actorUserId,
                        'user_id' => (int) $row->user_id,
                        'type' => 'wallet',
                        'action' => (string) $row->type,
                        'status' => 'completed',
                        'subject_type' => WalletTransaction::class,
                        'subject_id' => (string) $row->id,
                        'amount_usd' => (float) $row->amount_usd,
                        'reference' => (string) $row->reference,
                        'reversible' => (string) $row->type === 'admin_adjustment',
                        'before' => json_encode(['walletBalance' => (float) $row->balance_before]),
                        'after' => json_encode(['walletBalance' => (float) $row->balance_after]),
                        'meta' => $row->meta,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                        'deleted_at' => null,
                    ];
                }
                return $payload;
            }
        );

        $created += $this->backfillByTable(
            max(0, $limit - $created),
            'funding_transactions',
            \App\Models\FundingTransaction::class,
            function (array $rows): array {
                $payload = [];
                foreach ($rows as $row) {
                    $payload[] = [
                        'actor_user_id' => (int) $row->user_id,
                        'user_id' => (int) $row->user_id,
                        'type' => 'funding',
                        'action' => 'create',
                        'status' => (string) ($row->status ?? 'pending'),
                        'subject_type' => \App\Models\FundingTransaction::class,
                        'subject_id' => (string) $row->id,
                        'amount_usd' => $row->amount_usd !== null ? (float) $row->amount_usd : null,
                        'reference' => $row->reference !== null ? (string) $row->reference : null,
                        'reversible' => false,
                        'before' => null,
                        'after' => null,
                        'meta' => json_encode([
                            'payment_method' => $row->payment_method ?? null,
                            'provider' => $row->provider ?? null,
                        ]),
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                        'deleted_at' => null,
                    ];
                }
                return $payload;
            }
        );

        $created += $this->backfillByTable(
            max(0, $limit - $created),
            'log_orders',
            \App\Models\LogOrder::class,
            function (array $rows): array {
                $payload = [];
                foreach ($rows as $row) {
                    $payload[] = [
                        'actor_user_id' => (int) $row->user_id,
                        'user_id' => (int) $row->user_id,
                        'type' => 'logs_order',
                        'action' => 'create',
                        'status' => (string) ($row->status ?? ''),
                        'subject_type' => \App\Models\LogOrder::class,
                        'subject_id' => (string) $row->id,
                        'amount_usd' => $row->total_amount !== null ? (float) $row->total_amount : null,
                        'reference' => null,
                        'reversible' => false,
                        'before' => null,
                        'after' => null,
                        'meta' => json_encode([
                            'total_items' => $row->total_items ?? null,
                        ]),
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                        'deleted_at' => null,
                    ];
                }
                return $payload;
            }
        );

        $created += $this->backfillByTable(
            max(0, $limit - $created),
            'sms_orders',
            \App\Models\SmsOrder::class,
            function (array $rows): array {
                $payload = [];
                foreach ($rows as $row) {
                    $payload[] = [
                        'actor_user_id' => (int) $row->user_id,
                        'user_id' => (int) $row->user_id,
                        'type' => 'sms_order',
                        'action' => 'create',
                        'status' => (string) ($row->status ?? ''),
                        'subject_type' => \App\Models\SmsOrder::class,
                        'subject_id' => (string) $row->id,
                        'amount_usd' => $row->price !== null ? (float) $row->price : null,
                        'reference' => $row->order_id !== null ? (string) $row->order_id : null,
                        'reversible' => false,
                        'before' => null,
                        'after' => null,
                        'meta' => json_encode([
                            'phone_number' => $row->phone_number ?? null,
                            'country' => $row->country ?? null,
                            'product' => $row->product ?? null,
                        ]),
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                        'deleted_at' => null,
                    ];
                }
                return $payload;
            }
        );

        $created += $this->backfillBoostOrders(max(0, $limit - $created));

        return back()->with('success', 'Backfill complete. Added ' . $created . ' transactions.');
    }

    private function backfillBoostOrders(int $limit): int
    {
        if ($limit <= 0) {
            return 0;
        }

        $created = 0;
        $lastId = 0;

        while ($created < $limit) {
            $rows = DB::table('boost_orders')
                ->leftJoin('boost_services', 'boost_services.id', '=', 'boost_orders.boost_service_id')
                ->where('boost_orders.id', '>', $lastId)
                ->orderBy('boost_orders.id')
                ->limit(min(1000, $limit - $created))
                ->select([
                    'boost_orders.id',
                    'boost_orders.user_id',
                    'boost_orders.status',
                    'boost_orders.charge',
                    'boost_orders.quantity',
                    'boost_orders.jap_order_id',
                    'boost_orders.created_at',
                    'boost_orders.updated_at',
                    'boost_services.name as service_name',
                ])
                ->get();

            if ($rows->isEmpty()) {
                break;
            }

            $ids = $rows->pluck('id')->map(fn ($v) => (string) $v)->all();
            $existing = SystemTransaction::where('subject_type', \App\Models\BoostOrder::class)
                ->whereIn('subject_id', $ids)
                ->pluck('subject_id')
                ->all();
            $existingSet = array_fill_keys(array_map('strval', $existing), true);

            $batch = [];
            foreach ($rows as $row) {
                $lastId = (int) $row->id;
                if (isset($existingSet[(string) $row->id])) {
                    continue;
                }
                $batch[] = [
                    'actor_user_id' => (int) $row->user_id,
                    'user_id' => (int) $row->user_id,
                    'type' => 'boost_order',
                    'action' => 'create',
                    'status' => (string) ($row->status ?? ''),
                    'subject_type' => \App\Models\BoostOrder::class,
                    'subject_id' => (string) $row->id,
                    'amount_usd' => $row->charge !== null ? (float) $row->charge : null,
                    'reference' => $row->jap_order_id !== null ? (string) $row->jap_order_id : null,
                    'reversible' => false,
                    'before' => null,
                    'after' => null,
                    'meta' => json_encode([
                        'quantity' => $row->quantity ?? null,
                        'service' => $row->service_name ?? null,
                    ]),
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                    'deleted_at' => null,
                ];
            }

            if (! empty($batch)) {
                DB::table('system_transactions')->insert($batch);
                $created += count($batch);
            }
        }

        return $created;
    }

    private function backfillByTable(int $limit, string $table, string $subjectType, \Closure $mapRows): int
    {
        if ($limit <= 0) {
            return 0;
        }

        $created = 0;
        $lastId = 0;

        while ($created < $limit) {
            $rows = DB::table($table)
                ->where('id', '>', $lastId)
                ->orderBy('id')
                ->limit(min(1000, $limit - $created))
                ->get();

            if ($rows->isEmpty()) {
                break;
            }

            $lastId = (int) $rows->max('id');
            $ids = $rows->pluck('id')->map(fn ($v) => (string) $v)->all();

            $existing = SystemTransaction::where('subject_type', $subjectType)
                ->whereIn('subject_id', $ids)
                ->pluck('subject_id')
                ->all();
            $existingSet = array_fill_keys(array_map('strval', $existing), true);

            $filtered = [];
            foreach ($rows as $row) {
                if (isset($existingSet[(string) $row->id])) {
                    continue;
                }
                $filtered[] = $row;
            }

            if (empty($filtered)) {
                continue;
            }

            $payload = $mapRows($filtered);
            if (! empty($payload)) {
                DB::table('system_transactions')->insert($payload);
                $created += count($payload);
            }
        }

        return $created;
    }
}
