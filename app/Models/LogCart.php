<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'log_item_id',
        'quantity',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(LogItem::class, 'log_item_id');
    }

    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->item->price;
    }
}
