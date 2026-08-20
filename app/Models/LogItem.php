<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogItem extends Model
{
    protected $fillable = ['title', 'platform', 'description', 'price', 'is_active', 'is_out_of_stock'];

    public function credentials()
    {
        return $this->hasMany(LogCredential::class);
    }

    public function getAvailableStockAttribute()
    {
        if ((bool) ($this->is_out_of_stock ?? false)) {
            return 0;
        }

        if (array_key_exists('available_stock', $this->attributes)) {
            return (int) $this->attributes['available_stock'];
        }

        return $this->credentials()->where('is_sold', false)->count();
    }
}
