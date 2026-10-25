@extends('layouts.dashboard')

@section('page_title', ($serviceData['title'] ?? 'Service') . ' - Coming Soon')

@section('dashboard_content')

<div class="space-y-6 max-w-4xl mx-auto" x-data="{
    subscribed: localStorage.getItem('cs_notified_{{ $service }}') === '1',
    email: @js(auth()->user()->email ?? ''),
    loading: false,
    showToast: false,
    notifyMe() {
        this.loading = true;
        setTimeout(() => {
            this.loading = false;
            this.subscribed = true;
            this.showToast = true;
            localStorage.setItem('cs_notified_{{ $service }}', '1');
            setTimeout(() => this.showToast = false, 4000);
        }, 600);
    }
}">

    <!-- Toast Notification for Early Access -->
    <div x-show="showToast" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed top-6 right-6 z-50 bg-slate-950 text-white border border-teal-500/40 px-5 py-3.5 rounded-2xl shadow-2xl flex items-center gap-3 backdrop-blur-md"
         style="display: none;">
        <span class="w-8 h-8 rounded-xl bg-teal-500 text-white flex items-center justify-center font-black text-sm">🎉</span>
        <div>
            <div class="text-xs font-black text-white">Priority Access Reserved!</div>
            <div class="text-[11px] font-semibold text-slate-300">We'll alert your email the moment this service goes live.</div>
        </div>
    </div>

    @if(isset($isAvailable) && !$isAvailable)
        <div class="p-4 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-900 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <div class="font-black text-xs uppercase tracking-wider text-amber-950">Service Temporarily Unavailable</div>
                <div class="text-xs text-amber-900 mt-0.5">{{ $maintenanceMessage ?? 'This service is temporarily unavailable. Please try again later.' }}</div>
            </div>
        </div>
    @endif

    <!-- OPay Style Coming Soon Hero Banner -->
    <div class="relative overflow-hidden rounded-3xl p-6 sm:p-10 text-white shadow-xl border border-slate-800"
         style="background: linear-gradient(135deg, #090d16 0%, #0f172a 60%, #0d9488 100%) !important; background-color: #0f172a !important; color: #ffffff !important;">
        <!-- Ambient background glows -->
        <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full blur-3xl pointer-events-none" style="background-color: rgba(13, 148, 136, 0.25);"></div>
        <div class="absolute -left-16 -bottom-16 w-64 h-64 rounded-full blur-3xl pointer-events-none" style="background-color: rgba(250, 204, 21, 0.15);"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-3 max-w-xl">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[#facc15] text-[11px] font-black uppercase tracking-wider shadow-xs"
                          style="background-color: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.25);">
                        <span class="w-2 h-2 rounded-full bg-[#facc15] animate-ping"></span>
                        ⚡ {{ $serviceData['badge'] ?? 'NEW' }} FEATURE
                    </span>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-emerald-300 text-[11px] font-black uppercase tracking-wider"
                          style="background-color: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.25);">
                        {{ $serviceData['category'] ?? 'Fintech & Utility' }}
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black leading-tight tracking-tight" style="color: #ffffff !important;">
                    {{ $serviceData['title'] }}
                </h1>
                
                <p class="text-xs sm:text-sm font-medium leading-relaxed" style="color: #cbd5e1 !important;">
                    {{ $serviceData['desc'] }}
                </p>

                <!-- Priority Notification CTA -->
                <div class="pt-2">
                    <template x-if="!subscribed">
                        <form @submit.prevent="notifyMe()" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 max-w-md">
                            <input 
                                type="email" 
                                x-model="email" 
                                placeholder="Your notification email" 
                                class="flex-1 bg-white/10 border border-white/25 rounded-2xl px-4 py-3 text-xs font-bold text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#facc15] backdrop-blur-md"
                                required>
                            <button 
                                type="submit" 
                                :disabled="loading"
                                class="bg-[#facc15] hover:bg-yellow-400 active:scale-95 text-slate-950 font-black text-xs uppercase tracking-wider px-6 py-3 rounded-2xl shadow-lg transition-all shrink-0 cursor-pointer disabled:opacity-60"
                                style="color: #020617 !important;">
                                <span x-show="!loading">Notify Me</span>
                                <span x-show="loading" style="display: none;">Saving...</span>
                            </button>
                        </form>
                    </template>

                    <template x-if="subscribed">
                        <div class="inline-flex items-center gap-2 bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 text-xs font-black px-4 py-2.5 rounded-2xl">
                            <span>✓</span>
                            <span>You're on the early-access VIP list for this service!</span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Elevated Feature Icon Badge -->
            <div class="flex flex-col items-center justify-center p-6 sm:p-8 rounded-3xl shrink-0 text-center shadow-xl"
                 style="background-color: rgba(15, 23, 42, 0.85); border: 1px solid rgba(51, 65, 85, 0.8); min-width: 180px;">
                <div class="w-20 h-20 rounded-3xl bg-gradient-to-tr from-[#0d9488] to-teal-400 text-white flex items-center justify-center text-4xl shadow-xl shadow-teal-500/20 mb-3 animate-bounce">
                    {{ $serviceData['icon'] }}
                </div>
                <div class="text-xs font-black text-white">Launching Soon</div>
                <div class="text-[10px] font-bold text-slate-400 mt-0.5">Automated 24/7 API</div>
            </div>
        </div>
    </div>

    <!-- Feature Preview & Roadmap Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-slate-200/80 space-y-6">
        <div>
            <div class="text-xs font-black text-[#0d9488] uppercase tracking-wider mb-1">Architecture Preview</div>
            <h2 class="text-lg sm:text-xl font-black text-slate-900">What to expect when this goes live:</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            @foreach($serviceData['features'] ?? [] as $feature)
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-3 hover:bg-teal-50/50 hover:border-teal-200 transition-all">
                    <span class="w-6 h-6 rounded-lg bg-teal-100 text-[#0d9488] flex items-center justify-center font-black text-xs shrink-0 mt-0.5">
                        ✓
                    </span>
                    <span class="text-xs sm:text-sm font-bold text-slate-800 leading-snug">
                        {{ $feature }}
                    </span>
                </div>
            @endforeach
        </div>

        <!-- Navigation Action Buttons -->
        <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <a href="/dashboard" class="inline-flex items-center gap-2 text-xs font-black text-slate-700 hover:text-slate-950 px-4 py-2.5 rounded-xl hover:bg-slate-100 transition">
                <span>&larr;</span>
                <span>Back to Dashboard</span>
            </a>

            <div class="flex items-center gap-2">
                <a href="/sms-verify" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-black transition">
                    📱 SMS Verify
                </a>
                <a href="/logs-store" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-black transition">
                    📄 Logs Store
                </a>
                <a href="/funding" class="px-4 py-2 rounded-xl bg-[#0d9488] hover:bg-teal-700 text-white text-xs font-black transition shadow-xs">
                    💳 Add Money
                </a>
            </div>
        </div>
    </div>

</div>

@endsection
