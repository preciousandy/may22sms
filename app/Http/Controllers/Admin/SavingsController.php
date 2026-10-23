<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SavingsAuditLog;
use App\Models\SavingsCategory;
use App\Models\SavingsContribution;
use App\Models\SavingsInterestAccrual;
use App\Models\SavingsPlan;
use App\Models\Setting;
use App\Services\SavingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SavingsController extends Controller
{
    protected SavingsService $savingsService;

    public function __construct(SavingsService $savingsService)
    {
        $this->savingsService = $savingsService;
    }

    /**
     * Admin Overview Dashboard for Save Small Small.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = SavingsPlan::with(['user', 'category'])->latest();

        if ($status && in_array($status, ['active', 'matured', 'broken'], true)) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $plans = $query->paginate(20)->withQueryString();

        // System Liabilities & Analytics
        $allPlans = SavingsPlan::all();
        $totalCustomerPrincipal = (float) $allPlans->where('status', 'active')->sum('principal_amount');
        $totalAccruedInterestLiability = (float) $allPlans->where('status', 'active')->sum('accrued_interest');
        $totalPaidInterest = (float) $allPlans->sum('paid_interest');
        $activePlansCount = $allPlans->where('status', 'active')->count();
        $maturedPlansCount = $allPlans->where('status', 'matured')->count();
        $brokenPlansCount = $allPlans->where('status', 'broken')->count();

        $categories = SavingsCategory::orderBy('order_index')->get();

        return view('admin.savings.index', compact(
            'plans',
            'categories',
            'totalCustomerPrincipal',
            'totalAccruedInterestLiability',
            'totalPaidInterest',
            'activePlansCount',
            'maturedPlansCount',
            'brokenPlansCount',
            'status',
            'search'
        ));
    }

    /**
     * View detailed plan, ledger entries, interest accruals, and audit trail.
     */
    public function show(int $id)
    {
        $plan = SavingsPlan::with([
            'user',
            'category',
            'contributions.walletTransaction',
            'interestAccruals' => fn($q) => $q->latest('accrual_date')->limit(30),
        ])->findOrFail($id);

        $auditLogs = SavingsAuditLog::with('actor')
            ->where('savings_plan_id', $plan->id)
            ->latest('id')
            ->limit(50)
            ->get();

        return view('admin.savings.show', compact('plan', 'auditLogs'));
    }

    /**
     * Categories management view.
     */
    public function categories()
    {
        $categories = SavingsCategory::withCount('plans')->orderBy('order_index')->get();
        $globalInterestEnabled = Setting::where('key', 'savings_global_interest_enabled')->value('value') !== '0';

        return view('admin.savings.categories', compact('categories', 'globalInterestEnabled'));
    }

    /**
     * Toggle global interest on/off across all savings categories.
     */
    public function toggleGlobalInterest(Request $request)
    {
        $enabled = $request->boolean('global_interest_enabled');
        Setting::updateOrCreate(
            ['key' => 'savings_global_interest_enabled'],
            ['value' => $enabled ? '1' : '0']
        );

        SavingsAuditLog::record(
            null,
            null,
            auth()->id(),
            'global_interest_toggled',
            null,
            ['enabled' => $enabled]
        );

        $msg = $enabled 
            ? "Global interest enabled! Categories with active interest rates will now accrue returns for new plans." 
            : "Global interest turned OFF! All new savings plans will now operate as Pure Target Savings (0.0% interest — users get back exactly what they save at maturity).";

        return back()->with('success', $msg);
    }

    /**
     * Quick toggle category interest on/off.
     */
    public function toggleCategoryInterest(int $id)
    {
        $category = SavingsCategory::findOrFail($id);
        $oldState = (bool) ($category->is_interest_enabled ?? true);
        $newState = ! $oldState;

        $category->is_interest_enabled = $newState;
        $category->save();

        SavingsAuditLog::record(
            null,
            null,
            auth()->id(),
            'category_interest_toggled',
            ['is_interest_enabled' => $oldState],
            ['is_interest_enabled' => $newState, 'category' => $category->name]
        );

        $msg = $newState 
            ? "Interest turned ON for '{$category->name}' ({$category->default_annual_rate}% P.A.)." 
            : "Interest turned OFF for '{$category->name}'. Users will now save as Pure Target Savings (0.0% interest — get back only what they save).";

        return back()->with('success', $msg);
    }

    /**
     * Store a new savings category.
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'icon' => 'required|string|max:10',
            'description' => 'nullable|string|max:500',
            'default_annual_rate' => 'required|numeric|min:0|max:100',
            'min_deposit_ngn' => 'required|numeric|min:10',
            'min_deposit_usd' => 'required|numeric|min:0.1',
            'min_duration_days' => 'required|integer|min:7',
            'max_duration_days' => 'required|integer|min:7',
            'early_withdrawal_fee_percent' => 'required|numeric|min:0|max:100',
            'early_withdrawal_interest_forfeited_percent' => 'required|numeric|min:0|max:100',
            'maturity_fee_percent' => 'required|numeric|min:0|max:100',
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(4);
        $validated['is_enabled'] = $request->boolean('is_enabled', true);
        $validated['is_interest_enabled'] = $request->boolean('is_interest_enabled', true);
        $validated['allow_early_withdrawal'] = $request->boolean('allow_early_withdrawal', true);
        $validated['allow_partial_withdrawal'] = $request->boolean('allow_partial_withdrawal', false);

        $category = SavingsCategory::create($validated);

        SavingsAuditLog::record(
            null,
            null,
            auth()->id(),
            'category_created',
            null,
            ['id' => $category->id, 'name' => $category->name, 'is_interest_enabled' => $category->is_interest_enabled]
        );

        return back()->with('success', "Category '{$category->name}' created successfully.");
    }

    /**
     * Update an existing savings category terms.
     */
    public function updateCategory(Request $request, int $id)
    {
        $category = SavingsCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'icon' => 'required|string|max:10',
            'description' => 'nullable|string|max:500',
            'default_annual_rate' => 'required|numeric|min:0|max:100',
            'min_deposit_ngn' => 'required|numeric|min:10',
            'min_deposit_usd' => 'required|numeric|min:0.1',
            'min_duration_days' => 'required|integer|min:7',
            'max_duration_days' => 'required|integer|min:7',
            'early_withdrawal_fee_percent' => 'required|numeric|min:0|max:100',
            'early_withdrawal_interest_forfeited_percent' => 'required|numeric|min:0|max:100',
            'maturity_fee_percent' => 'required|numeric|min:0|max:100',
        ]);

        $old = $category->toArray();

        $validated['is_enabled'] = $request->boolean('is_enabled');
        $validated['is_interest_enabled'] = $request->boolean('is_interest_enabled');
        $validated['allow_early_withdrawal'] = $request->boolean('allow_early_withdrawal');
        $validated['allow_partial_withdrawal'] = $request->boolean('allow_partial_withdrawal');

        $category->update($validated);

        SavingsAuditLog::record(
            null,
            null,
            auth()->id(),
            'category_updated',
            $old,
            $category->toArray()
        );

        return back()->with('success', "Category '{$category->name}' terms updated. Existing customer contracts remain protected.");
    }

    /**
     * Financial Reconciliation & Audit Trail.
     */
    public function reconciliation()
    {
        $totalDeposited = (float) SavingsContribution::whereIn('type', ['deposit', 'additional_deposit'])->sum('amount');
        $totalWithdrawn = (float) SavingsContribution::whereIn('type', ['early_withdrawal', 'maturity_payout'])->sum('amount');
        $totalActivePrincipal = (float) SavingsPlan::where('status', 'active')->sum('principal_amount');
        $totalAccruedInterest = (float) SavingsPlan::where('status', 'active')->sum('accrued_interest');
        $totalPaidInterest = (float) SavingsPlan::sum('paid_interest');
        $totalForfeitedInterest = (float) SavingsPlan::sum('forfeited_interest');

        // Net liability = Current active principal + accrued interest
        $totalLiability = $totalActivePrincipal + $totalAccruedInterest;

        $auditLogs = SavingsAuditLog::with(['actor', 'plan', 'user'])
            ->latest('id')
            ->paginate(30);

        return view('admin.savings.reconciliation', compact(
            'totalDeposited',
            'totalWithdrawn',
            'totalActivePrincipal',
            'totalAccruedInterest',
            'totalPaidInterest',
            'totalForfeitedInterest',
            'totalLiability',
            'auditLogs'
        ));
    }
}
