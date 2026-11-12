<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtilityTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reference',
        'service_type',
        'provider',
        'provider_reference',
        'provider_status',
        'network_or_disco',
        'product_code',
        'product_name',
        'customer_target',
        'customer_name',
        'meter_type',
        'amount_ngn',
        'amount_usd',
        'provider_cost_ngn',
        'provider_cost_usd',
        'selling_price_ngn',
        'fee_ngn',
        'discount_ngn',
        'total_charged_usd',
        'profit_ngn',
        'profit_usd',
        'pricing_snapshot',
        'exchange_rate',
        'status',
        'token',
        'units',
        'request_payload',
        'response_payload',
        'error_message',
        'refunded_at',
        'wallet_transaction_id',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'amount_ngn' => 'decimal:4',
        'amount_usd' => 'decimal:8',
        'provider_cost_ngn' => 'decimal:4',
        'provider_cost_usd' => 'decimal:8',
        'selling_price_ngn' => 'decimal:4',
        'fee_ngn' => 'decimal:4',
        'discount_ngn' => 'decimal:4',
        'total_charged_usd' => 'decimal:8',
        'profit_ngn' => 'decimal:4',
        'profit_usd' => 'decimal:8',
        'pricing_snapshot' => 'array',
        'exchange_rate' => 'decimal:4',
        'request_payload' => 'array',
        'response_payload' => 'array',
        'refunded_at' => 'datetime',
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
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public function isUncertain(): bool
    {
        return $this->status === 'uncertain';
    }

    public function canRefund(): bool
    {
        return in_array($this->status, ['failed', 'uncertain'], true) && is_null($this->refunded_at);
    }

    /**
     * Get a human-readable title for the service.
     */
    public function getServiceLabelAttribute(): string
    {
        return match ($this->service_type) {
            'airtime' => 'Airtime Recharge',
            'data' => 'Data Bundle',
            'electricity' => 'Electricity Bill',
            'betting' => 'Betting Wallet Top-Up',
            default => ucfirst($this->service_type),
        };
    }

    /**
     * Get badge color styling for status.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'completed' => [
                'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'label' => 'Successful',
                'icon' => '',
            ],
            'pending' => [
                'bg' => 'bg-amber-50 text-amber-700 border-amber-200',
                'label' => 'Processing',
                'icon' => '',
            ],
            'failed' => [
                'bg' => 'bg-rose-50 text-rose-700 border-rose-200',
                'label' => 'Failed',
                'icon' => '',
            ],
            'refunded' => [
                'bg' => 'bg-blue-50 text-blue-700 border-blue-200',
                'label' => 'Refunded',
                'icon' => '',
            ],
            'uncertain' => [
                'bg' => 'bg-yellow-50 text-yellow-800 border-yellow-300',
                'label' => 'Pending Verification',
                'icon' => '',
            ],
            default => [
                'bg' => 'bg-gray-50 text-gray-700 border-gray-200',
                'label' => ucfirst($this->status),
                'icon' => '',
            ],
        };
    }
}
