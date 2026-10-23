<?php

namespace Database\Seeders;

use App\Models\SavingsCategory;
use Illuminate\Database\Seeder;

class SavingsCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Christmas Savings',
                'slug' => 'christmas-savings',
                'icon' => '🎄',
                'description' => 'Lock funds for the festive season and earn high-yield returns before December.',
                'default_annual_rate' => 16.90,
                'min_deposit_ngn' => 1000.00,
                'min_deposit_usd' => 1.00,
                'min_duration_days' => 60,
                'max_duration_days' => 365,
                'allow_early_withdrawal' => true,
                'allow_partial_withdrawal' => false,
                'early_withdrawal_fee_percent' => 0.00,
                'early_withdrawal_interest_forfeited_percent' => 100.00,
                'maturity_fee_percent' => 1.50,
                'order_index' => 1,
            ],
            [
                'name' => 'Wedding Savings',
                'slug' => 'wedding-savings',
                'icon' => '💍',
                'description' => 'Save towards your dream wedding, venue, photography, and celebration.',
                'default_annual_rate' => 16.90,
                'min_deposit_ngn' => 2000.00,
                'min_deposit_usd' => 2.00,
                'min_duration_days' => 90,
                'max_duration_days' => 365,
                'allow_early_withdrawal' => true,
                'allow_partial_withdrawal' => false,
                'early_withdrawal_fee_percent' => 0.00,
                'early_withdrawal_interest_forfeited_percent' => 100.00,
                'maturity_fee_percent' => 1.50,
                'order_index' => 2,
            ],
            [
                'name' => 'Rent and Housing',
                'slug' => 'rent-and-housing',
                'icon' => '🏠',
                'description' => 'Never get caught off guard by landlord renewals. Save small small monthly for your rent.',
                'default_annual_rate' => 16.90,
                'min_deposit_ngn' => 5000.00,
                'min_deposit_usd' => 3.50,
                'min_duration_days' => 90,
                'max_duration_days' => 365,
                'allow_early_withdrawal' => true,
                'allow_partial_withdrawal' => false,
                'early_withdrawal_fee_percent' => 0.00,
                'early_withdrawal_interest_forfeited_percent' => 100.00,
                'maturity_fee_percent' => 1.50,
                'order_index' => 3,
            ],
            [
                'name' => 'Education',
                'slug' => 'education',
                'icon' => '🎓',
                'description' => 'Tuition fees, certifications, books, and courses for you and your family.',
                'default_annual_rate' => 16.90,
                'min_deposit_ngn' => 1000.00,
                'min_deposit_usd' => 1.00,
                'min_duration_days' => 60,
                'max_duration_days' => 365,
                'allow_early_withdrawal' => true,
                'allow_partial_withdrawal' => false,
                'early_withdrawal_fee_percent' => 0.00,
                'early_withdrawal_interest_forfeited_percent' => 100.00,
                'maturity_fee_percent' => 1.50,
                'order_index' => 4,
            ],
            [
                'name' => 'Travel',
                'slug' => 'travel',
                'icon' => '✈️',
                'description' => 'Vacations, flight tickets, visa processing, and holiday getaways.',
                'default_annual_rate' => 16.90,
                'min_deposit_ngn' => 2000.00,
                'min_deposit_usd' => 1.50,
                'min_duration_days' => 60,
                'max_duration_days' => 365,
                'allow_early_withdrawal' => true,
                'allow_partial_withdrawal' => false,
                'early_withdrawal_fee_percent' => 0.00,
                'early_withdrawal_interest_forfeited_percent' => 100.00,
                'maturity_fee_percent' => 1.50,
                'order_index' => 5,
            ],
            [
                'name' => 'Gadgets',
                'slug' => 'gadgets',
                'icon' => '📱',
                'description' => 'Target new iPhones, MacBooks, gaming consoles, or creator equipment.',
                'default_annual_rate' => 16.90,
                'min_deposit_ngn' => 1000.00,
                'min_deposit_usd' => 1.00,
                'min_duration_days' => 30,
                'max_duration_days' => 180,
                'allow_early_withdrawal' => true,
                'allow_partial_withdrawal' => false,
                'early_withdrawal_fee_percent' => 0.00,
                'early_withdrawal_interest_forfeited_percent' => 100.00,
                'maturity_fee_percent' => 1.50,
                'order_index' => 6,
            ],
            [
                'name' => 'Emergency Savings',
                'slug' => 'emergency-savings',
                'icon' => '🛡️',
                'description' => 'Build a rainy day safety net for unforeseen medical or car expenses.',
                'default_annual_rate' => 16.90,
                'min_deposit_ngn' => 500.00,
                'min_deposit_usd' => 0.50,
                'min_duration_days' => 30,
                'max_duration_days' => 365,
                'allow_early_withdrawal' => true,
                'allow_partial_withdrawal' => false,
                'early_withdrawal_fee_percent' => 0.00,
                'early_withdrawal_interest_forfeited_percent' => 100.00,
                'maturity_fee_percent' => 1.50,
                'order_index' => 7,
            ],
            [
                'name' => 'Custom Savings Goal',
                'slug' => 'custom-savings-goal',
                'icon' => '🎯',
                'description' => 'Create a custom personal financial goal with your own milestone and timeline.',
                'default_annual_rate' => 16.90,
                'min_deposit_ngn' => 500.00,
                'min_deposit_usd' => 0.50,
                'min_duration_days' => 30,
                'max_duration_days' => 365,
                'allow_early_withdrawal' => true,
                'allow_partial_withdrawal' => false,
                'early_withdrawal_fee_percent' => 0.00,
                'early_withdrawal_interest_forfeited_percent' => 100.00,
                'maturity_fee_percent' => 1.50,
                'order_index' => 8,
            ],
        ];

        foreach ($categories as $data) {
            SavingsCategory::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
