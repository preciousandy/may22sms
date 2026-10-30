<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SavingsPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $attributes = [
        'early_break_fee_percent' => 2.50,
        'early_break_interest_forfeit_percent' => 100.00,
    ];

    protected $fillable = [
        'user_id',
        'category_id',
        'reference',
        'name',
        'description',
        'currency',
        'target_amount',
        'principal_amount',
        'accrued_interest',
        'paid_interest',
        'forfeited_interest',
        'total_withdrawn',
        'annual_interest_rate',
        'interest_commencement_date',
        'last_accrual_date',
        'start_date',
        'maturity_date',
        'duration_days',
        'allow_early_withdrawal',
        'allow_partial_withdrawal',
        'early_break_fee_percent',
        'early_break_interest_forfeit_percent',
        'maturity_fee_percent',
        'compounding_frequency',
        'status',
        'matured_at',
        'broken_at',
        'closed_at',
        'payout_statement',
        'metadata',
    ];

    protected $casts = [
        'target_amount' => 'float',
        'principal_amount' => 'float',
        'accrued_interest' => 'float',
        'paid_interest' => 'float',
        'forfeited_interest' => 'float',
        'total_withdrawn' => 'float',
        'annual_interest_rate' => 'float',
        'interest_commencement_date' => 'date',
        'last_accrual_date' => 'date',
        'start_date' => 'date',
        'maturity_date' => 'date',
        'duration_days' => 'integer',
        'allow_early_withdrawal' => 'boolean',
        'allow_partial_withdrawal' => 'boolean',
        'early_break_fee_percent' => 'float',
        'early_break_interest_forfeit_percent' => 'float',
        'maturity_fee_percent' => 'float',
        'matured_at' => 'datetime',
        'broken_at' => 'datetime',
        'closed_at' => 'datetime',
        'payout_statement' => 'array',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(SavingsCategory::class, 'category_id');
    }

    public function contributions()
    {
        return $this->hasMany(SavingsContribution::class, 'savings_plan_id')->latest();
    }

    public function interestAccruals()
    {
        return $this->hasMany(SavingsInterestAccrual::class, 'savings_plan_id')->latest('accrual_date');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isMatured(): bool
    {
        if ($this->status === 'matured') {
            return true;
        }

        if ($this->isActive()) {
            $maturity = Carbon::parse($this->maturity_date)->startOfDay();
            return Carbon::today()->greaterThanOrEqualTo($maturity);
        }

        return false;
    }

    public function getDaysRemainingAttribute(): int
    {
        $today = Carbon::today()->startOfDay();
        $maturity = Carbon::parse($this->maturity_date)->startOfDay();
        if ($today->greaterThanOrEqualTo($maturity)) {
            return 0;
        }
        return (int) $today->diffInDays($maturity);
    }

    public function getEffectiveBreakFeePercentAttribute(): float
    {
        if ($this->relationLoaded('category') && $this->category && $this->category->early_withdrawal_fee_percent !== null) {
            return (float) $this->category->early_withdrawal_fee_percent;
        }

        if ($this->category_id) {
            $cat = $this->category ?: SavingsCategory::find($this->category_id);
            if ($cat && $cat->early_withdrawal_fee_percent !== null) {
                return (float) $cat->early_withdrawal_fee_percent;
            }
        }

        return (float) ($this->early_break_fee_percent ?? 2.50);
    }

    public function getDaysElapsedAttribute(): int
    {
        $start = Carbon::parse($this->start_date);
        $today = Carbon::today();
        return max(0, (int) $start->diffInDays($today));
    }

    public function getProgressPercentAttribute(): float
    {
        if ($this->target_amount && $this->target_amount > 0) {
            return min(100.0, round(($this->principal_amount / $this->target_amount) * 100, 1));
        }

        // Fallback to timeline progress if no target amount
        if ($this->duration_days > 0) {
            return min(100.0, round(($this->days_elapsed / $this->duration_days) * 100, 1));
        }

        return 0.0;
    }

    /**
     * Calculate projected interest at full maturity based on current principal.
     * Convention: Principal * (Rate / 100) * (duration_days / 365)
     */
    public function getProjectedInterestAttribute(): float
    {
        $days = (int) $this->duration_days;
        $principal = (float) $this->principal_amount;
        $rate = (float) $this->annual_interest_rate;

        return round($principal * ($rate / 100.0) * ($days / 365.0), 4);
    }

    /**
     * Estimated net payout at maturity after deducting the 1.5% maturity fee from interest.
     */
    public function getProjectedMaturityPayoutAttribute(): float
    {
        $projInterest = $this->projected_interest;
        $fee = round($projInterest * ($this->maturity_fee_percent / 100.0), 4);
        return round($this->principal_amount + ($projInterest - $fee), 2);
    }

    /**
     * Calculate early break summary disclosure.
     */
    public function calculateEarlyBreakSummary(): array
    {
        $principal = (float) $this->principal_amount;
        $accruedInterest = (float) $this->accrued_interest;

        // Interest forfeited: 100% of accrued interest
        $forfeitPercent = 100.00;
        if (isset($this->early_break_interest_forfeit_percent) && $this->early_break_interest_forfeit_percent !== null) {
            $forfeitPercent = (float) $this->early_break_interest_forfeit_percent;
        }
        $forfeitedInterest = round($accruedInterest * ($forfeitPercent / 100.0), 8);
        $retainedInterest = round(max(0.0, $accruedInterest - $forfeitedInterest), 8);

        // Early-break fee = Eligible principal * feePercent (default 2.50% or admin-adjusted saving option fee)
        $feePercent = $this->effective_break_fee_percent;
        $earlyBreakFee = round($principal * ($feePercent / 100.0), 2);

        // Net payout = Eligible principal - Early-break fee
        $netPayout = max(0.0, round(($principal - $earlyBreakFee) + $retainedInterest, 2));

        return [
            'plan_name' => $this->name,
            'principal' => $principal,
            'accrued_interest' => $accruedInterest,
            'forfeited_interest' => $forfeitedInterest,
            'retained_interest' => $retainedInterest,
            'early_break_fee' => $earlyBreakFee,
            'early_break_fee_percent' => $feePercent,
            'net_payout' => $netPayout,
            'penalty_rate' => $forfeitPercent,
        ];
    }
}
