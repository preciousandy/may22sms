<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SportyBetCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'amount',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'amount' => 'decimal:2',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('amount_paid')
            ->withTimestamps();
    }
}
