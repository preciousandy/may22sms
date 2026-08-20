<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogOrder extends Model
{
    protected $fillable = ['user_id', 'promo_code_id', 'promo_discount_usd', 'total_amount', 'total_items', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function credentials()
    {
        return $this->hasMany(LogCredential::class);
    }
}
