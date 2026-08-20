<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FundingTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'promo_code_id',
        'payment_method',
        'provider',
        'provider_invoice_id',
        'provider_payment_id',
        'payment_url',
        'pay_currency',
        'currency',
        'amount',
        'amount_usd',
        'rate_ngn_per_usd',
        'reference',
        'status',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
