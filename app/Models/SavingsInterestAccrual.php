<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsInterestAccrual extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'savings_plan_id',
        'accrual_date',
        'eligible_principal',
        'annual_rate',
        'day_interest_amount',
        'cumulative_interest_before',
        'cumulative_interest_after',
        'is_leap_year',
        'created_at',
    ];

    protected $casts = [
        'accrual_date' => 'date:Y-m-d',
        'eligible_principal' => 'float',
        'annual_rate' => 'float',
        'day_interest_amount' => 'float',
        'cumulative_interest_before' => 'float',
        'cumulative_interest_after' => 'float',
        'is_leap_year' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(SavingsPlan::class, 'savings_plan_id');
    }
}
