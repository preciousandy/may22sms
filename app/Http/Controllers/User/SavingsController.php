<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SavingsCategory;
use App\Models\SavingsPlan;
use App\Services\SavingsService;
use Illuminate\Http\Request;
use Throwable;

class SavingsController extends Controller
{
    protected SavingsService $savingsService;

    public function __construct(SavingsService $savingsService)
    {
        $this->savingsService = $savingsService;
    }

    /**
     * User's Save Small Small portfolio dashboard.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $status = $request->query('status', 'all');

        $query = SavingsPlan::with('category')
            ->where('user_id', $user->id)
            ->latest();

        if ($status !== 'all' && in_array($status, ['active', 'matured', 'broken'], true)) {
            $query->where('status', $status);
        }

        $plans = $query->paginate(12)->withQueryString();

        // Portfolio metrics (calculated in user's preferred currency)
        $userPlans = SavingsPlan::where('user_id', $user->id)->get();
        $totalSavingsPrincipal = (float) $userPlans->where('status', 'active')->sum('principal_amount');
        $totalAccruedInterest = (float) $userPlans->where('status', 'active')->sum('accrued_interest');
        $totalPaidInterest = (float) $userPlans->sum('paid_interest');
        $activePlansCount = $userPlans->where('status', 'active')->count();

        $categories = SavingsCategory::where('is_enabled', true)
            ->orderBy('order_index')
            ->get();

        $ngnPerUsd = $this->savingsService->getNgnPerUsd();

        return view('user.savings.index', compact(
            'plans',
            'categories',
            'totalSavingsPrincipal',
            'totalAccruedInterest',
            'totalPaidInterest',
            'activePlansCount',
            'status',
            'ngnPerUsd'
        ));
    }

    /**
     * View for creating a new savings plan.
     */
    public function create(Request $request)
    {
        $categories = SavingsCategory::where('is_enabled', true)
            ->orderBy('order_index')
            ->get();

        $selectedCategorySlug = $request->query('category');
        $selectedCategory = $selectedCategorySlug 
            ? $categories->firstWhere('slug', $selectedCategorySlug) 
            : $categories->first();

        $ngnPerUsd = $this->savingsService->getNgnPerUsd();
        $walletBalance = (float) (auth()->user()->walletBalance ?? 0);

        return view('user.savings.create', compact('categories', 'selectedCategory', 'ngnPerUsd', 'walletBalance'));
    }

    /**
     * Store a newly created savings plan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:savings_categories,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'currency' => 'required|in:NGN,USD',
            'initial_deposit' => 'required|numeric|min:0.01',
            'target_amount' => 'nullable|numeric|min:0.01',
            'duration_days' => 'required|integer|min:7|max:730',
        ]);

        try {
            $plan = $this->savingsService->createPlan(auth()->user(), $validated);

            return redirect()->route('savings.show', $plan->id)
                ->with('success', "🎉 '{$plan->name}' created successfully with {$plan->currency} " . number_format($plan->principal_amount, 2) . " locked in!");
        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * View details of an individual savings plan.
     */
    public function show(int $id)
    {
        $plan = SavingsPlan::with(['category', 'contributions.walletTransaction', 'interestAccruals'])
            ->findOrFail($id);

        if ($plan->user_id !== auth()->id() && ! (auth()->user()->is_admin ?? false)) {
            abort(403, 'Unauthorized access to this savings plan.');
        }

        $ngnPerUsd = $this->savingsService->getNgnPerUsd();
        $walletBalance = (float) (auth()->user()->walletBalance ?? 0);
        $earlyBreakSummary = $plan->calculateEarlyBreakSummary();

        return view('user.savings.show', compact('plan', 'ngnPerUsd', 'walletBalance', 'earlyBreakSummary'));
    }

    /**
     * Top-up an active savings plan.
     */
    public function contribute(Request $request, int $id)
    {
        $plan = SavingsPlan::findOrFail($id);

        if ($plan->user_id !== auth()->id() && ! (auth()->user()->is_admin ?? false)) {
            abort(403, 'Unauthorized access to this savings plan.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        try {
            $contribution = $this->savingsService->addContribution($plan, auth()->user(), (float) $validated['amount']);

            return redirect()->route('savings.show', $plan->id)
                ->with('success', "✓ Added {$plan->currency} " . number_format($contribution->amount, 2) . " to {$plan->name}!");
        } catch (Throwable $e) {
            return redirect()->route('savings.show', $plan->id)->with('error', $e->getMessage());
        }
    }

    /**
     * Break / early withdrawal from a savings plan.
     */
    public function breakPlan(Request $request, int $id)
    {
        $plan = SavingsPlan::findOrFail($id);

        if ($plan->user_id !== auth()->id() && ! (auth()->user()->is_admin ?? false)) {
            abort(403, 'Unauthorized access to this savings plan.');
        }

        $request->validate([
            'confirm_early_break' => 'required|accepted',
        ]);

        try {
            $result = $this->savingsService->breakPlan($plan, auth()->user());
            $netPayout = $result['summary']['net_payout'] ?? 0;

            return redirect()->route('savings.show', $plan->id)
                ->with('success', "Savings plan broken. Net payout of {$plan->currency} " . number_format($netPayout, 2) . " has been returned to your main wallet.");
        } catch (Throwable $e) {
            return redirect()->route('savings.show', $plan->id)->with('error', $e->getMessage());
        }
    }

    /**
     * Interactive interest & maturity returns calculator.
     */
    public function calculator()
    {
        $categories = SavingsCategory::where('is_enabled', true)->orderBy('order_index')->get();
        $ngnPerUsd = $this->savingsService->getNgnPerUsd();

        return view('user.savings.calculator', compact('categories', 'ngnPerUsd'));
    }
}
