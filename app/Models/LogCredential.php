<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogCredential extends Model
{
    protected $fillable = ['log_item_id', 'data', 'is_sold', 'log_order_id', 'user_id'];

    public function item()
    {
        return $this->belongsTo(LogItem::class, 'log_item_id');
    }

    public function order()
    {
        return $this->belongsTo(LogOrder::class, 'log_order_id');
    }
}
