<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'country',
        'phone_number',
        'password',
        'walletBalance',
        'account_status',
        'otp',
        'is_admin',
        'is_support',
        'is_editor',
        'staff_permissions',
        'referral_code',
        'referred_by',
        'two_factor_enabled',
        'force_logout_at',
        'paymentpoint_account_number',
        'paymentpoint_account_name',
        'paymentpoint_bank_name',
        'paymentpoint_nin',
        'strollwallet_account_number',
        'strollwallet_account_name',
        'strollwallet_bank_name',
        'monietude_account_number',
        'monietude_account_name',
        'monietude_bank_name',
        'monietude_provider',
        'google_id',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'is_support' => 'boolean',
        'is_editor' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'staff_permissions' => 'array',
        'force_logout_at' => 'datetime',
    ];

    public function smsOrders()
    {
        return $this->hasMany(SmsOrder::class);
    }

    public function logOrders()
    {
        return $this->hasMany(LogOrder::class);
    }

    public function boostOrders()
    {
        return $this->hasMany(BoostOrder::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function sportyBetCodes()
    {
        return $this->belongsToMany(SportyBetCode::class)
            ->withPivot('amount_paid')
            ->withTimestamps();
    }

    public function savingsPlans()
    {
        return $this->hasMany(SavingsPlan::class);
    }

    public function savingsContributions()
    {
        return $this->hasMany(SavingsContribution::class);
    }

    public function utilityTransactions()
    {
        return $this->hasMany(UtilityTransaction::class);
    }
}
