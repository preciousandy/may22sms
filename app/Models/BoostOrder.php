<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoostOrder extends Model
{
    protected $fillable = [
        'user_id',
        'boost_service_id',
        'promo_code_id',
        'jap_order_id',
        'link',
        'quantity',
        'charge',
        'promo_discount_usd',
        'status',
        'start_count',
        'remains',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(BoostService::class, 'boost_service_id');
    }
}
