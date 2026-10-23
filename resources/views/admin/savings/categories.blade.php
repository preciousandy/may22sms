@extends('layouts.admin')

@section('admin_content')
@push('header_title', 'Savings Categories & Commercial Terms')

<div class="space-y-6" x-data="{
    showNewCategoryModal: false,
    editingCategory: null,
    editData: {}
}">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.savings.index') }}" class="text-xs font-bold text-gray-500 hover:text-gray-900">&larr; Savings Dashboard</a>
            </div>
            <h1 class="text-2xl font-black text-gray-900 mt-1">Savings Categories &amp; Interest Engine</h1>
            <p class="text-xs text-gray-500 mt-1">Turn interest on or off globally, or toggle 16.9% P.A. on specific categories for pure target savings.</p>
        </div>

        <button type="button" @click="showNewCategoryModal = true" class="px-4 py-2.5 rounded-xl bg-[#0d9488] hover:bg-teal-700 text-white font-bold text-xs uppercase tracking-wider transition shadow-sm flex items-center gap-1.5 cursor-pointer">
            <span>+ Create Category</span>
        </button>
    </div>

    <!-- Master Global Interest Switch Card -->
    <div class="rounded-3xl p-6 sm:p-7 text-white shadow-xl border border-slate-700/80 flex flex-col md:flex-row md:items-center justify-between gap-6"
         style="background: linear-gradient(135deg, #090d16 0%, #0f172a 60%, {{ $globalInterestEnabled ? '#0d9488' : '#334155' }} 100%) !important;">
        <div class="space-y-2 max-w-2xl">
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $globalInterestEnabled ? 'bg-emerald-400 text-slate-950' : 'bg-amber-400 text-slate-950' }}">
                    {{ $globalInterestEnabled ? '● Global Interest: ACTIVE' : '○ Global Interest: OFF (Pure Savings Mode)' }}
                </span>
                <span class="text-xs text-slate-400 font-bold">• Master System Switch</span>
            </div>

            <h2 class="text-lg sm:text-2xl font-black text-white leading-tight">
                {{ $globalInterestEnabled ? '16.9% P.A. Interest Engine is Active' : 'Pure Goal Savings (Users Get Back Exactly What They Save)' }}
            </h2>

            <p class="text-xs text-slate-300 leading-relaxed">
                @if($globalInterestEnabled)
                    Platform interest is enabled. Categories with active interest below will earn interest (e.g. 16.9% P.A.) paid daily. You can selectively turn off interest on specific categories below to make them pure target savings.
                @else
                    All categories are currently operating in <strong>Zero-Interest Pure Target Mode</strong>. When users save, they receive back exactly 100% of their deposited money upon maturity with ₦0 fee and zero interest.
                @endif
            </p>
        </div>

        <form action="{{ route('admin.savings.toggle-global-interest') }}" method="POST" class="shrink-0">
            @csrf
            <input type="hidden" name="global_interest_enabled" value="{{ $globalInterestEnabled ? '0' : '1' }}">
            <button type="submit" 
                    class="w-full sm:w-auto px-5 py-3.5 rounded-2xl font-black text-xs uppercase tracking-wider transition shadow-lg cursor-pointer flex items-center justify-center gap-2 {{ $globalInterestEnabled ? 'bg-amber-400 hover:bg-amber-300 text-slate-950' : 'bg-emerald-400 hover:bg-emerald-300 text-slate-950' }}">
                <span>{{ $globalInterestEnabled ? '⏸ Turn OFF All Interest (Pure Savings)' : '⚡ Turn ON Interest Engine (16.9% P.A.)' }}</span>
            </button>
        </form>
    </div>

    <!-- Alert / Notice -->
    <div class="p-4 rounded-2xl bg-teal-50 border border-teal-200 text-teal-900 text-xs font-medium flex items-center gap-2.5">
        <span class="text-lg">🛡️</span>
        <div>
            <strong>Financial Contract Protection:</strong> Changing interest toggles or rates applies to <em>new savings plans</em> created thereafter. Existing active plans maintain their agreed rate until maturity.
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">
            ✓ {{ session('success') }}
        </div>
    @endif

    <!-- Categories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($categories as $category)
            @php
                $hasInterest = $globalInterestEnabled && ($category->is_interest_enabled ?? true);
            @endphp
            <div class="bg-white p-5 rounded-3xl border {{ $hasInterest ? 'border-teal-200 shadow-sm' : 'border-gray-200 shadow-xs' }} flex flex-col justify-between space-y-4 hover:border-teal-400 transition">
                <div class="space-y-3">
                    <div class="flex items-start justify-between">
                        <span class="text-3xl">{{ $category->icon }}</span>
                        <div class="flex items-center gap-1.5">
                            <!-- Category Interest Badge -->
                            @if($hasInterest)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800">
                                    {{ $category->default_annual_rate }}% P.A.
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-slate-100 text-slate-700">
                                    0% (Pure Goal)
                                </span>
                            @endif

                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase {{ $category->is_enabled ? 'bg-gray-100 text-gray-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ $category->is_enabled ? 'Active' : 'Hidden' }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-black text-gray-900">{{ $category->name }}</h3>
                        <p class="text-[11px] text-gray-500 line-clamp-2 mt-0.5">{{ $category->description ?? 'No description provided.' }}</p>
                    </div>

                    <!-- Interest Model Summary Box -->
                    <div class="p-3 rounded-2xl {{ $hasInterest ? 'bg-teal-50/70 border border-teal-100 text-teal-900' : 'bg-slate-50 border border-slate-100 text-slate-800' }} text-xs space-y-1">
                        <div class="font-extrabold flex items-center justify-between">
                            <span>Payout Rule:</span>
                            <span class="{{ $hasInterest ? 'text-emerald-700' : 'text-slate-700' }}">
                                {{ $hasInterest ? 'Principal + ' . $category->default_annual_rate . '% Profit' : 'Save & Get Exact Deposit Back' }}
                            </span>
                        </div>
                        <div class="text-[10px] {{ $hasInterest ? 'text-teal-700' : 'text-slate-500' }}">
                            {{ $hasInterest ? 'Daily interest accrued at ' . $category->default_annual_rate . '% P.A.' : 'Zero interest, zero fee — pure target vault.' }}
                        </div>
                    </div>

                    <div class="p-3 rounded-2xl bg-gray-50 border border-gray-100 space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Min Deposit:</span>
                            <span class="font-bold text-gray-800">₦{{ number_format($category->min_deposit_ngn, 0) }} / ${{ number_format($category->min_deposit_usd, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Duration Range:</span>
                            <span class="font-bold text-gray-800">{{ $category->min_duration_days }} - {{ $category->max_duration_days }} days</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Maturity Fee:</span>
                            <span class="font-bold text-gray-800">{{ $hasInterest ? $category->maturity_fee_percent . '% of profit' : '₦0.00 (Zero Fee)' }}</span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 space-y-2">
                    <div class="flex items-center justify-between text-[11px]">
                        <span class="text-gray-400 font-bold">{{ $category->plans_count }} Plans</span>
                        
                        <!-- Quick 1-Click Interest Toggle Form -->
                        <form action="{{ route('admin.savings.categories.toggle-interest', $category->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider transition cursor-pointer {{ $category->is_interest_enabled ? 'bg-amber-100 hover:bg-amber-200 text-amber-900' : 'bg-emerald-100 hover:bg-emerald-200 text-emerald-900' }}">
                                {{ $category->is_interest_enabled ? 'Turn OFF 16.9%' : 'Turn ON 16.9%' }}
                            </button>
                        </form>
                    </div>

                    <button type="button" 
                            @click="editingCategory = {{ $category->id }}; editData = @js($category);"
                            class="w-full py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold text-xs transition cursor-pointer text-center">
                        Edit Full Terms &amp; Rules
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Edit Category Modal -->
    <div x-show="editingCategory !== null" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;">
        <div @click.away="editingCategory = null" class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-base font-black text-gray-900">Edit Category Terms</h3>
                <button type="button" @click="editingCategory = null" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
            </div>

            <form :action="'/admin/savings/categories/' + editingCategory" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700">Category Name</label>
                        <input type="text" name="name" x-model="editData.name" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-bold" required>
                    </div>
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700">Icon Emoji</label>
                        <input type="text" name="icon" x-model="editData.icon" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs" required>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-gray-700">Description</label>
                    <textarea name="description" x-model="editData.description" rows="2" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs"></textarea>
                </div>

                <!-- Interest Engine Toggle on Category -->
                <div class="p-3.5 rounded-2xl border transition" :class="editData.is_interest_enabled ? 'bg-teal-50/80 border-teal-200' : 'bg-slate-50 border-slate-200'">
                    <label class="flex items-center justify-between cursor-pointer">
                        <div class="space-y-0.5">
                            <div class="font-black text-xs" :class="editData.is_interest_enabled ? 'text-teal-950' : 'text-slate-800'">
                                <span x-text="editData.is_interest_enabled ? '⚡ Interest Enabled (16.9% P.A.)' : '⚪ Pure Target Savings (0% Interest)'"></span>
                            </div>
                            <div class="text-[10px] text-gray-500">
                                <span x-text="editData.is_interest_enabled ? 'Users earn daily returns at the annual rate configured below.' : 'Users save purely towards milestone targets. They receive back 100% of their exact deposit at maturity.'"></span>
                            </div>
                        </div>
                        <input type="checkbox" name="is_interest_enabled" value="1" x-model="editData.is_interest_enabled" class="rounded text-[#0d9488] scale-125">
                    </label>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700">Annual Return Rate (% P.A.)</label>
                        <input type="number" step="0.01" name="default_annual_rate" x-model="editData.default_annual_rate" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-bold text-emerald-600" required>
                    </div>
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700">Maturity Fee (% of profit)</label>
                        <input type="number" step="0.01" name="maturity_fee_percent" x-model="editData.maturity_fee_percent" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700">Min Deposit (NGN ₦)</label>
                        <input type="number" step="0.01" name="min_deposit_ngn" x-model="editData.min_deposit_ngn" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs" required>
                    </div>
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700">Min Deposit (USD $)</label>
                        <input type="number" step="0.01" name="min_deposit_usd" x-model="editData.min_deposit_usd" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700">Min Duration (Days)</label>
                        <input type="number" name="min_duration_days" x-model="editData.min_duration_days" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs" required>
                    </div>
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700">Max Duration (Days)</label>
                        <input type="number" name="max_duration_days" x-model="editData.max_duration_days" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700">Early Break Fee (% of principal)</label>
                        <input type="number" step="0.01" name="early_withdrawal_fee_percent" x-model="editData.early_withdrawal_fee_percent" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs" required>
                    </div>
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700">Forfeited Interest (%)</label>
                        <input type="number" step="0.01" name="early_withdrawal_interest_forfeited_percent" x-model="editData.early_withdrawal_interest_forfeited_percent" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs" required>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_enabled" value="1" :checked="editData.is_enabled" class="rounded text-[#0d9488]">
                        <span class="font-bold text-gray-700">Enabled for users</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="allow_early_withdrawal" value="1" :checked="editData.allow_early_withdrawal" class="rounded text-[#0d9488]">
                        <span class="font-bold text-gray-700">Allow early break</span>
                    </label>
                </div>

                <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
                    <button type="button" @click="editingCategory = null" class="w-1/2 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-bold">
                        Cancel
                    </button>
                    <button type="submit" class="w-1/2 py-2.5 rounded-xl bg-[#0d9488] hover:bg-teal-700 text-white font-bold shadow-md">
                        Save Terms
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create Category Modal -->
    <div x-show="showNewCategoryModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;">
        <div @click.away="showNewCategoryModal = false" class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-base font-black text-gray-900">Create New Savings Category</h3>
                <button type="button" @click="showNewCategoryModal = false" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
            </div>

            <form action="{{ route('admin.savings.categories.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700">Category Name</label>
                        <input type="text" name="name" placeholder="e.g. Relocation Fund" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-bold" required>
                    </div>
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700">Icon Emoji</label>
                        <input type="text" name="icon" value="🎯" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs" required>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-gray-700">Description</label>
                    <textarea name="description" rows="2" placeholder="Brief explanation of this savings goal" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs"></textarea>
                </div>

                <!-- Interest Engine Toggle on Category -->
                <div class="p-3.5 rounded-2xl bg-teal-50/80 border border-teal-200 space-y-1">
                    <label class="flex items-center justify-between cursor-pointer">
                        <div>
                            <div class="font-black text-teal-950 text-xs">Enable Annual Interest (e.g. 16.9% P.A.)</div>
                            <div class="text-[10px] text-teal-800">If unchecked, users save purely towards their goal and receive their exact deposit at maturity.</div>
                        </div>
                        <input type="checkbox" name="is_interest_enabled" value="1" checked class="rounded text-[#0d9488] scale-125">
                    </label>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700">Annual Return (% P.A.)</label>
                        <input type="number" step="0.01" name="default_annual_rate" value="16.90" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-bold text-emerald-600" required>
                    </div>
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700">Maturity Fee (% of profit)</label>
                        <input type="number" step="0.01" name="maturity_fee_percent" value="1.50" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700">Min Deposit (NGN ₦)</label>
                        <input type="number" step="0.01" name="min_deposit_ngn" value="1000" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs" required>
                    </div>
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700">Min Deposit (USD $)</label>
                        <input type="number" step="0.01" name="min_deposit_usd" value="1.00" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700">Min Duration (Days)</label>
                        <input type="number" name="min_duration_days" value="30" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs" required>
                    </div>
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700">Max Duration (Days)</label>
                        <input type="number" name="max_duration_days" value="365" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700">Early Break Fee (% of principal)</label>
                        <input type="number" step="0.01" name="early_withdrawal_fee_percent" value="0.00" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs" required>
                    </div>
                    <div class="space-y-1">
                        <label class="font-bold text-gray-700">Forfeited Interest (%)</label>
                        <input type="number" step="0.01" name="early_withdrawal_interest_forfeited_percent" value="100.00" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs" required>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_enabled" value="1" checked class="rounded text-[#0d9488]">
                        <span class="font-bold text-gray-700">Enabled for users</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="allow_early_withdrawal" value="1" checked class="rounded text-[#0d9488]">
                        <span class="font-bold text-gray-700">Allow early break</span>
                    </label>
                </div>

                <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
                    <button type="button" @click="showNewCategoryModal = false" class="w-1/2 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-bold">
                        Cancel
                    </button>
                    <button type="submit" class="w-1/2 py-2.5 rounded-xl bg-[#0d9488] hover:bg-teal-700 text-white font-bold shadow-md">
                        Create Category
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection
