<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'actor_user_id',
        'user_id',
        'type',
        'action',
        'status',
        'subject_type',
        'subject_id',
        'amount_usd',
        'reference',
        'reversible',
        'reverted_at',
        'reverted_by',
        'approved_at',
        'approved_by',
        'before',
        'after',
        'meta',
    ];

    protected $casts = [
        'amount_usd' => 'float',
        'reversible' => 'boolean',
        'reverted_at' => 'datetime',
        'approved_at' => 'datetime',
        'before' => 'array',
        'after' => 'array',
        'meta' => 'array',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

