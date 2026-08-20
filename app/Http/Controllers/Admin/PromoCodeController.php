<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\PromoRedemption;
use App\Models\SystemTransaction;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PromoCodeController extends Controller
{
    private function allowedContexts(): array
    {
        return ['funding', 'logs', 'sms', 'boost'];
    }

    public function index(Request $request)
    {
        $codes = PromoCode::latest()->paginate(20, ['*'], 'codes_page');
        $codes->appends($request->query());

        $rq = trim((string) $request->query('rq', ''));
        $codeId = (string) $request->query('code_id', 'all');

        $redemptionsQuery = PromoRedemption::query()->with(['promoCode', 'user', 'fundingTransaction']);
        if ($codeId !== 'all' && is_numeric($codeId)) {
            $redemptionsQuery->where('promo_code_id', (int) $codeId);
        }
        if ($rq !== '') {
            $like = '%' . $rq . '%';
            $redemptionsQuery->where(function ($q) use ($rq, $like) {
                $q->whereHas('promoCode', function ($p) use ($like) {
                    $p->where('code', 'like', $like);
                })->orWhereHas('user', function ($u) use ($rq, $like) {
                    $u->where('email', 'like', $like)->orWhere('name', 'like', $like);
                    if (is_numeric($rq)) {
                        $u->orWhere('id', (int) $rq);
                    }
                });
            });
        }

        $redemptions = $redemptionsQuery
            ->latest()
            ->paginate(20, ['*'], 'redemptions_page');
        $redemptions->appends($request->query());

        $bonusByFundingId = [];
        $fundingIds = collect($redemptions->items())
            ->pluck('funding_transaction_id')
            ->filter()
            ->unique()
            ->values()
            ->all();
        if (! empty($fundingIds)) {
            $refs = array_map(fn ($id) => 'PROMO-' . (string) $id, $fundingIds);
            $bonusTx = WalletTransaction::where('type', 'promo_bonus')
                ->whereIn('reference', $refs)
                ->get(['reference', 'amount_usd']);
            foreach ($bonusTx as $tx) {
                $ref = (string) $tx->reference;
                if (str_starts_with($ref, 'PROMO-')) {
                    $id = (int) substr($ref, strlen('PROMO-'));
                    $bonusByFundingId[$id] = (float) $tx->amount_usd;
                }
            }
        }

        $codeOptions = PromoCode::orderBy('code')->get(['id', 'code']);

        return view('admin.promo-codes.index', compact('codes', 'redemptions', 'rq', 'codeId', 'codeOptions', 'bonusByFundingId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:40|unique:promo_codes,code',
            'title' => 'nullable|string|max:120',
            'message' => 'nullable|string|max:255',
            'type' => 'required|string|in:percent,fixed',
            'value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'is_public' => 'nullable|in:1',
            'applies_to' => 'nullable|array',
            'applies_to.*' => 'in:' . implode(',', $this->allowedContexts()),
        ]);

        $appliesTo = $validated['applies_to'] ?? $this->allowedContexts();
        $appliesTo = array_values(array_unique($appliesTo));

        PromoCode::create([
            'code' => strtoupper($validated['code']),
            'title' => $validated['title'] ?? null,
            'message' => $validated['message'] ?? null,
            'applies_to' => $appliesTo,
            'type' => $validated['type'],
            'value' => $validated['value'],
            'max_uses' => $validated['max_uses'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'used_count' => 0,
            'is_active' => true,
            'is_public' => $request->has('is_public'),
        ]);

        Cache::forget('promos:public');
        Cache::forget('promos:public:v2');

        return back()->with('success', 'Promo code created.');
    }

    public function toggle($id)
    {
        $code = PromoCode::findOrFail($id);
        $code->is_active = ! $code->is_active;
        $code->save();

        Cache::forget('promos:public');
        Cache::forget('promos:public:v2');

        return back()->with('success', 'Promo code updated.');
    }

    public function togglePublic($id)
    {
        $code = PromoCode::findOrFail($id);
        $code->is_public = ! (bool) $code->is_public;
        $code->save();

        Cache::forget('promos:public');
        Cache::forget('promos:public:v2');

        return back()->with('success', 'Promo visibility updated.');
    }

    public function broadcast($id)
    {
        $code = PromoCode::findOrFail($id);
        if (! $code->is_active) {
            return back()->with('error', 'Enable the promo code before broadcasting.');
        }
        if (! (bool) $code->is_public) {
            return back()->with('error', 'Make the promo code public before notifying users.');
        }

        $title = $code->title ?: 'New Promo Code';
        $valueText = $code->type === 'fixed'
            ? ('$' . number_format((float) $code->value, 2) . ' off')
            : (number_format((float) $code->value, 2) . '% off');
        $services = (array) ($code->applies_to ?? []);
        if (empty($services)) {
            $services = $this->allowedContexts();
        }
        $servicesText = implode(', ', array_map(fn ($v) => strtoupper((string) $v), $services));

        $message = trim((string) ($code->message ?: ("Use code {$code->code} for {$valueText}. Applies to: {$servicesText}.")));
        $actionUrl = '/dashboard';

        $now = now();
        $count = 0;

        User::where('account_status', 'active')
            ->orderBy('id')
            ->select(['id'])
            ->chunkById(500, function ($users) use ($title, $message, $actionUrl, $now, &$count) {
                $rows = [];
                foreach ($users as $u) {
                    $rows[] = [
                        'id' => (string) Str::uuid(),
                        'type' => 'promo_announcement',
                        'notifiable_type' => User::class,
                        'notifiable_id' => $u->id,
                        'data' => json_encode([
                            'title' => $title,
                            'message' => $message,
                            'action_url' => $actionUrl,
                        ]),
                        'read_at' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if (! empty($rows)) {
                    DB::table('notifications')->insert($rows);
                    $count += count($rows);
                }
            });

        SystemTransaction::create([
            'actor_user_id' => auth()->id(),
            'user_id' => auth()->id(),
            'type' => 'promo',
            'action' => 'broadcast',
            'status' => 'completed',
            'subject_type' => PromoCode::class,
            'subject_id' => (string) $code->id,
            'meta' => [
                'code' => (string) $code->code,
                'sent' => (int) $count,
            ],
        ]);

        Cache::forget('promos:public');
        Cache::forget('promos:public:v2');

        return back()->with('success', 'Promo sent to users: ' . number_format($count));
    }
}
