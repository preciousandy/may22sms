<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoRedemption extends Model
{
    protected $fillable = [
        'promo_code_id',
        'user_id',
        'funding_transaction_id',
        'context',
        'subject_type',
        'subject_id',
        'amount_usd',
    ];

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fundingTransaction()
    {
        return $this->belongsTo(FundingTransaction::class);
    }
}
