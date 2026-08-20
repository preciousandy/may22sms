<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsOrder extends Model
{
    protected $fillable = [
        'user_id',
        'promo_code_id',
        'provider',
        'order_id',
        'phone_number',
        'country',
        'operator',
        'product',
        'price',
        'promo_discount_usd',
        'status',
        'sms_code',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
