<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CryptoTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'merchant_reference',
        'provider_reference',
        'provider_public_id',
        'from_currency',
        'to_currency',
        'network',
        'from_amount',
        'to_amount',
        'exchange_rate',
        'stamp_charge',
        'processor_fee',
        'platform_fee',
        'payout_destination',
        'bank_code',
        'bank_name',
        'account_number',
        'account_name',
        'deposit_address',
        'deposit_tag',
        'deposit_assigned_at',
        'crypto_received_amount',
        'crypto_txid',
        'fiat_payout_id',
        'fiat_payout_amount',
        'status',
        'error_message',
        'is_test_mode',
        'wallet_credited',
        'wallet_transaction_id',
        'settled_at',
        'metadata',
    ];

    protected $casts = [
        'from_amount' => 'decimal:8',
        'to_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'stamp_charge' => 'decimal:2',
        'processor_fee' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'crypto_received_amount' => 'decimal:8',
        'fiat_payout_amount' => 'decimal:2',
        'is_test_mode' => 'boolean',
        'wallet_credited' => 'boolean',
        'deposit_assigned_at' => 'datetime',
        'settled_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function walletTransaction()
    {
        return $this->belongsTo(WalletTransaction::class);
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'address_assigned'], true);
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'needs_attention'], true);
    }

    public function getEnvironmentAttribute(): string
    {
        return $this->is_test_mode ? 'test' : 'production';
    }

    public function getQuotedRateAttribute(): float
    {
        return (float) $this->exchange_rate;
    }
}
