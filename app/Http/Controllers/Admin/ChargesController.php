<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ChargesController extends Controller
{
    public function index()
    {
        $fundingChargeEnabled = (int) (Setting::where('key', 'funding_charge_enabled')->value('value') ?? 0) === 1;
        $globalChargePercent = (float) (Setting::where('key', 'funding_charge_percentage')->value('value') ?? 0);

        $paymentpointCharge = (float) (Setting::where('key', 'payment_method_paymentpoint_charge_percent')->value('value') ?? $globalChargePercent);
        $pocketfiCharge = (float) (Setting::where('key', 'payment_method_pocketfi_charge_percent')->value('value') ?? $globalChargePercent);
        $strollwalletCharge = (float) (Setting::where('key', 'payment_method_strollwallet_charge_percent')->value('value') ?? $globalChargePercent);
        $cryptoCharge = (float) (Setting::where('key', 'payment_method_crypto_charge_percent')->value('value') ?? $globalChargePercent);

        return view('admin.charges', compact(
            'fundingChargeEnabled',
            'globalChargePercent',
            'paymentpointCharge',
            'pocketfiCharge',
            'strollwalletCharge',
            'cryptoCharge'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'funding_charge_enabled' => 'nullable|in:0,1',
            'funding_charge_percentage' => 'nullable|numeric|min:0|max:100',
            'payment_method_paymentpoint_charge_percent' => 'nullable|numeric|min:0|max:100',
            'payment_method_pocketfi_charge_percent' => 'nullable|numeric|min:0|max:100',
            'payment_method_strollwallet_charge_percent' => 'nullable|numeric|min:0|max:100',
            'payment_method_crypto_charge_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        Setting::updateOrCreate(
            ['key' => 'funding_charge_enabled'],
            ['value' => (string) ((int) $request->funding_charge_enabled)]
        );

        $keys = [
            'funding_charge_percentage',
            'payment_method_paymentpoint_charge_percent',
            'payment_method_pocketfi_charge_percent',
            'payment_method_strollwallet_charge_percent',
            'payment_method_crypto_charge_percent',
        ];

        foreach ($keys as $key) {
            if ($request->has($key) && $request->input($key) !== null) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => (string) max(0, (float) $request->input($key))]
                );
            }
        }

        Cache::forget('settings:view_defaults:v2');

        return redirect()->back()->with('success', 'Funding charge percentages updated successfully!');
    }
}
