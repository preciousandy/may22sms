<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_type',
        'order_id',
        'reason',
        'status',
        'admin_notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getOrderAttribute()
    {
        if ($this->order_type === 'sms') {
            return SmsOrder::find($this->order_id);
        } elseif ($this->order_type === 'boost') {
            return BoostOrder::with('service')->find($this->order_id);
        } elseif ($this->order_type === 'logs') {
            return LogOrder::with('credentials.item')->find($this->order_id);
        }

        return null;
    }
}
