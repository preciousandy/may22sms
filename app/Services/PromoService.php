<?php

namespace App\Services;

use App\Models\FundingTransaction;
use App\Models\PromoCode;
use App\Models\PromoRedemption;
use App\Models\User;
use App\Models\WalletTransaction;

class PromoService
{
    public function resolveEligiblePromo(?string $input, User $user, string $context, ?string &$reason = null): ?PromoCode
    {
        $reason = null;
        $code = strtoupper(trim((string) $input));
        if ($code === '') {
            return null;
        }

        $promo = PromoCode::where('code', $code)->first();
        if (! $promo) {
            $reason = 'Invalid promo code.';
            return null;
        }

        if (! $promo->is_active) {
            $reason = 'This promo code is disabled.';
            return null;
        }

        if ($promo->expires_at && $promo->expires_at->isPast()) {
            $reason = 'This promo code has expired.';
            return null;
        }

        if ($promo->max_uses !== null && (int) $promo->used_count >= (int) $promo->max_uses) {
            $reason = 'This promo code has reached its usage limit.';
            return null;
        }

        if (! (bool) $promo->is_public) {
            $reason = 'This promo code is not available.';
            return null;
        }

        $appliesTo = (array) ($promo->applies_to ?? []);
        if (empty($appliesTo) || ! in_array($context, $appliesTo, true)) {
            $reason = 'This promo code does not apply to this service.';
            return null;
        }

        $alreadyRedeemed = PromoRedemption::where('promo_code_id', $promo->id)
            ->where('user_id', $user->id)
            ->exists();
        if ($alreadyRedeemed) {
            $reason = 'You have already used this promo code.';
            return null;
        }

        return $promo;
    }

    public function calculateDiscountUsd(PromoCode $promo, float $amountUsd): float
    {
        if ($amountUsd <= 0) {
            return 0.0;
        }

        $discount = 0.0;
        if ($promo->type === 'fixed') {
            $discount = (float) $promo->value;
        } else {
            $discount = $amountUsd * (((float) $promo->value) / 100);
        }

        $discount = round($discount, 2);
        if ($discount <= 0) {
            return 0.0;
        }

        return min($amountUsd, $discount);
    }

    public function redeemForOrder(User $user, PromoCode $promo, string $context, string $subjectType, string $subjectId, float $amountUsd): void
    {
        $amountUsd = round((float) $amountUsd, 2);

        PromoRedemption::create([
            'promo_code_id' => $promo->id,
            'user_id' => $user->id,
            'funding_transaction_id' => null,
            'context' => $context,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'amount_usd' => $amountUsd > 0 ? $amountUsd : null,
        ]);

        $promo->increment('used_count');
    }

    public function applyFundingBonus(FundingTransaction $fundingTx): void
    {
        if (! $fundingTx->promo_code_id) {
            return;
        }

        $user = $fundingTx->user;
        if (! $user) {
            return;
        }

        $code = PromoCode::find($fundingTx->promo_code_id);
        if (! $code || ! $code->is_active) {
            return;
        }

        if ($code->expires_at && $code->expires_at->isPast()) {
            return;
        }

        if ($code->max_uses !== null && (int) $code->used_count >= (int) $code->max_uses) {
            return;
        }

        $alreadyPaid = WalletTransaction::where('type', 'promo_bonus')
            ->where('reference', 'PROMO-'.(string) $fundingTx->id)
            ->exists();
        if ($alreadyPaid) {
            return;
        }

        $alreadyRedeemed = PromoRedemption::where('promo_code_id', $code->id)
            ->where('user_id', $user->id)
            ->exists();
        if ($alreadyRedeemed) {
            return;
        }

        $amount = 0.0;
        if ($code->type === 'fixed') {
            $amount = (float) $code->value;
        } else {
            $amount = ((float) $fundingTx->amount_usd) * (((float) $code->value) / 100);
        }

        $amount = round($amount, 2);
        if ($amount <= 0) {
            return;
        }

        app(WalletService::class)->credit($user, $amount, 'promo_bonus', 'PROMO-'.(string) $fundingTx->id, [
            'promo_code' => $code->code,
            'promo_code_id' => $code->id,
            'funding_transaction_id' => $fundingTx->id,
        ]);

        PromoRedemption::create([
            'promo_code_id' => $code->id,
            'user_id' => $user->id,
            'funding_transaction_id' => $fundingTx->id,
            'context' => 'funding',
            'subject_type' => FundingTransaction::class,
            'subject_id' => (string) $fundingTx->id,
            'amount_usd' => $amount,
        ]);

        $code->increment('used_count');
    }
}
