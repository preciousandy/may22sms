<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsContribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'savings_plan_id',
        'user_id',
        'reference',
        'wallet_transaction_id',
        'type',
        'amount',
        'principal_before',
        'principal_after',
        'description',
        'status',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'float',
        'principal_before' => 'float',
        'principal_after' => 'float',
        'metadata' => 'array',
    ];

    public function plan()
    {
        return $this->belongsTo(SavingsPlan::class, 'savings_plan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function walletTransaction()
    {
        return $this->belongsTo(WalletTransaction::class, 'wallet_transaction_id');
    }
}
