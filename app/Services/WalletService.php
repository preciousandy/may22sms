<?php

namespace App\Services;

use App\Models\SystemTransaction;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletService
{
    public function credit(User $user, float $amountUsd, string $type, string $reference, array $meta = []): void
    {
        $this->apply($user, abs($amountUsd), $type, $reference, $meta);
    }

    public function debit(User $user, float $amountUsd, string $type, string $reference, array $meta = []): void
    {
        $this->apply($user, -abs($amountUsd), $type, $reference, $meta);
    }

    public function apply(User $user, float $amountUsd, string $type, string $reference, array $meta = []): void
    {
        DB::transaction(function () use ($user, $amountUsd, $type, $reference, $meta) {
            $lockedUser = User::lockForUpdate()->findOrFail($user->id);
            $exists = WalletTransaction::where('user_id', $lockedUser->id)
                ->where('type', $type)
                ->where('reference', $reference)
                ->exists();

            if ($exists) {
                return;
            }

            $before = (float) $lockedUser->walletBalance;
            $after = $before + $amountUsd;
            if ($after < 0) {
                throw new RuntimeException('insufficient_funds');
            }
            $lockedUser->walletBalance = $after;
            $lockedUser->save();

            $tx = WalletTransaction::create([
                'user_id' => $lockedUser->id,
                'type' => $type,
                'reference' => $reference,
                'amount_usd' => round($amountUsd, 2),
                'balance_before' => round($before, 2),
                'balance_after' => round($after, 2),
                'meta' => $meta ?: null,
            ]);

            $actorUserId = null;
            if (array_key_exists('admin_id', $meta) && $meta['admin_id']) {
                $actorUserId = (int) $meta['admin_id'];
            }
            if ($actorUserId === null) {
                $actorUserId = $lockedUser->id;
            }

            SystemTransaction::create([
                'actor_user_id' => $actorUserId,
                'user_id' => $lockedUser->id,
                'type' => 'wallet',
                'action' => $type,
                'status' => 'completed',
                'subject_type' => WalletTransaction::class,
                'subject_id' => (string) $tx->id,
                'amount_usd' => round($amountUsd, 2),
                'reference' => $reference,
                'reversible' => $type === 'admin_adjustment',
                'before' => [
                    'walletBalance' => round($before, 2),
                ],
                'after' => [
                    'walletBalance' => round($after, 2),
                ],
                'meta' => array_merge($meta, [
                    'wallet_transaction_id' => $tx->id,
                ]),
            ]);
        });
    }
}
