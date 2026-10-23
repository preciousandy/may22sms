@extends('layouts.dashboard')

@section('page_title', 'Tutorials & How-To Guides')

@section('dashboard_content')

<div class="space-y-8 max-w-6xl mx-auto" x-data="{
    activeTab: @js($activeTab ?? 'sms'),
    search: '',
    faqOpen: null,
    toggleFaq(id) {
        this.faqOpen = this.faqOpen === id ? null : id;
    }
}">

    <!-- Top Hero Banner: Tutorials & Help Center -->
    <div class="relative overflow-hidden rounded-3xl p-6 sm:p-10 text-white shadow-xl border border-slate-800"
         style="background: linear-gradient(135deg, #090d16 0%, #0f172a 60%, #0d9488 100%) !important; background-color: #0f172a !important; color: #ffffff !important;">
        <!-- Ambient background glow spheres -->
        <div class="absolute -right-16 -top-16 w-72 h-72 rounded-full blur-3xl pointer-events-none" style="background-color: rgba(13, 148, 136, 0.25);"></div>
        <div class="absolute -left-16 -bottom-16 w-72 h-72 rounded-full blur-3xl pointer-events-none" style="background-color: rgba(99, 102, 241, 0.2);"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-3 max-w-2xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[#facc15] text-[11px] font-black uppercase tracking-wider"
                          style="background-color: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.25);">
                        🎓 User Knowledge Base &amp; Tutorials
                    </span>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-emerald-300 text-[11px] font-black uppercase tracking-wider"
                          style="background-color: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.25);">
                        Step-by-Step Guides
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black leading-tight tracking-tight" style="color: #ffffff !important;">
                    How to Use Every May22SMS Service
                </h1>
                
                <p class="text-xs sm:text-sm font-medium leading-relaxed" style="color: #cbd5e1 !important;">
                    New to the platform or need a refresher? Follow these simple, illustrated tutorials to verify phone numbers, buy social accounts, boost profiles, and fund your wallet seamlessly.
                </p>

                <!-- Value Proposition Badges -->
                <div class="flex flex-wrap items-center gap-3 pt-2 text-xs font-bold text-slate-300">
                    <span class="flex items-center gap-1.5 bg-white/10 px-3 py-1.5 rounded-xl border border-white/15">
                        <span class="text-emerald-400">✓</span> 100% Automated
                    </span>
                    <span class="flex items-center gap-1.5 bg-white/10 px-3 py-1.5 rounded-xl border border-white/15">
                        <span class="text-amber-400">🛡</span> Auto-Refund Protection
                    </span>
                    <span class="flex items-center gap-1.5 bg-white/10 px-3 py-1.5 rounded-xl border border-white/15">
                        <span class="text-sky-400">⚡</span> &lt; 60s Fast Delivery
                    </span>
                </div>
            </div>

            <!-- Elevated Quick Action Card -->
            <div class="flex flex-col items-center justify-center p-6 rounded-3xl shrink-0 text-center shadow-xl"
                 style="background-color: rgba(15, 23, 42, 0.85); border: 1px solid rgba(51, 65, 85, 0.8); min-width: 200px;">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-[#0d9488] to-teal-400 text-white flex items-center justify-center text-3xl shadow-lg shadow-teal-500/20 mb-3">
                    📚
                </div>
                <div class="text-sm font-black text-white">Need Live Help?</div>
                <div class="text-[11px] font-semibold text-slate-400 mt-0.5">Our support team is 24/7</div>
                <a href="/tickets" class="mt-3 inline-block px-4 py-2 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 text-xs font-black transition">
                    Open Support Ticket
                </a>
            </div>
        </div>
    </div>

    <!-- Service Selection Tab Bar (OPay App Style Pill Navigation) -->
    <div class="bg-white rounded-3xl p-3 shadow-xs border border-slate-200/80 sticky top-4 z-20">
        <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
            <!-- 1. SMS Verification -->
            <button type="button" 
                    @click="activeTab = 'sms'"
                    :class="activeTab === 'sms' ? 'bg-[#0d9488] text-white shadow-md shadow-teal-600/20' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="px-4 py-2.5 rounded-2xl font-black text-xs shrink-0 transition flex items-center gap-2 cursor-pointer">
                <span>📱</span>
                <span>SMS Verifications</span>
            </button>

            <!-- 2. Logs Store -->
            <button type="button" 
                    @click="activeTab = 'logs'"
                    :class="activeTab === 'logs' ? 'bg-[#0d9488] text-white shadow-md shadow-teal-600/20' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="px-4 py-2.5 rounded-2xl font-black text-xs shrink-0 transition flex items-center gap-2 cursor-pointer">
                <span>📄</span>
                <span>Logs &amp; Accounts</span>
            </button>

            <!-- 3. Social Boosting -->
            <button type="button" 
                    @click="activeTab = 'boosting'"
                    :class="activeTab === 'boosting' ? 'bg-[#0d9488] text-white shadow-md shadow-teal-600/20' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="px-4 py-2.5 rounded-2xl font-black text-xs shrink-0 transition flex items-center gap-2 cursor-pointer">
                <span>🚀</span>
                <span>Social Boosting</span>
            </button>

            <!-- 4. Wallet Funding -->
            <button type="button" 
                    @click="activeTab = 'funding'"
                    :class="activeTab === 'funding' ? 'bg-[#0d9488] text-white shadow-md shadow-teal-600/20' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="px-4 py-2.5 rounded-2xl font-black text-xs shrink-0 transition flex items-center gap-2 cursor-pointer">
                <span>💳</span>
                <span>Add Money / Funding</span>
            </button>

            <!-- 5. Save Small Small -->
            <button type="button" 
                    @click="activeTab = 'savings'"
                    :class="activeTab === 'savings' ? 'bg-[#0d9488] text-white shadow-md shadow-teal-600/20' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="px-4 py-2.5 rounded-2xl font-black text-xs shrink-0 transition flex items-center gap-2 cursor-pointer">
                <span>💰</span>
                <span>Save Small Small</span>
            </button>

            <!-- 6. Daily Streak -->
            <button type="button" 
                    @click="activeTab = 'streak'"
                    :class="activeTab === 'streak' ? 'bg-[#0d9488] text-white shadow-md shadow-teal-600/20' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="px-4 py-2.5 rounded-2xl font-black text-xs shrink-0 transition flex items-center gap-2 cursor-pointer">
                <span>🎁</span>
                <span>Daily Rewards</span>
            </button>

            <!-- 6. SportyBet & Betting -->
            <button type="button" 
                    @click="activeTab = 'sporty'"
                    :class="activeTab === 'sporty' ? 'bg-[#0d9488] text-white shadow-md shadow-teal-600/20' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="px-4 py-2.5 rounded-2xl font-black text-xs shrink-0 transition flex items-center gap-2 cursor-pointer">
                <span>⚽</span>
                <span>SportyBet &amp; Betting</span>
            </button>

            <!-- 7. Coming Soon Utilities -->
            <button type="button" 
                    @click="activeTab = 'utilities'"
                    :class="activeTab === 'utilities' ? 'bg-[#0d9488] text-white shadow-md shadow-teal-600/20' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="px-4 py-2.5 rounded-2xl font-black text-xs shrink-0 transition flex items-center gap-2 cursor-pointer">
                <span>⚡</span>
                <span>Fintech Utilities</span>
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: SMS VERIFICATIONS TUTORIAL -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'sms'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
        
        <!-- Summary & CTA Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-teal-100 text-[#0d9488] flex items-center justify-center text-lg">📱</span>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900">How to Receive SMS &amp; Verify Any App</h2>
                </div>
                <p class="text-xs sm:text-sm text-slate-600 max-w-2xl leading-relaxed">
                    Rent disposable or private virtual phone numbers from USA, UK, Nigeria, or 100+ countries to receive OTP codes for WhatsApp, Telegram, Google, Claude, OpenAI, and more.
                </p>
                <div class="flex flex-wrap items-center gap-3 pt-2 text-xs font-bold text-slate-500">
                    <span class="text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg">⏱ Average Time: 30-90 seconds</span>
                    <span class="text-teal-600 bg-teal-50 px-2.5 py-1 rounded-lg">💵 Starts from: $0.10 / ₦150</span>
                    <span class="text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg">🛡 Auto-Refund: 100% Guaranteed</span>
                </div>
            </div>
            <a href="/sms-verify" class="px-6 py-3.5 rounded-2xl bg-[#0d9488] hover:bg-teal-700 text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-teal-600/20 text-center shrink-0 transition active:scale-95">
                Launch SMS Verify →
            </a>
        </div>

        <!-- Step-by-Step Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Step 1 -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-teal-100 text-[#0d9488]">Step 01</span>
                    <span class="text-xs font-bold text-slate-400">Prep Your Wallet</span>
                </div>
                <h3 class="text-base font-black text-slate-900">Check Your Wallet Balance</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Make sure you have enough funds in your May22SMS wallet balance. Numbers start from as little as $0.10. If empty, click <strong>"Add Money"</strong> to fund instantly via Monietude virtual bank transfer, card, or crypto.
                </p>
                <!-- Mini Simulation Card -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-600">Current Wallet Balance:</span>
                    <span class="font-black text-emerald-600">₦{{ number_format((auth()->user()->walletBalance ?? 0) * 1500, 2) }}</span>
                </div>
                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200/70 text-[11px] text-amber-900 font-medium">
                    💡 <strong>Pro-Tip:</strong> You are only charged if the SMS code is actually received! If no SMS arrives, you get a 100% refund.
                </div>
            </div>

            <!-- Step 2 -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-teal-100 text-[#0d9488]">Step 02</span>
                    <span class="text-xs font-bold text-slate-400">Select Service &amp; Country</span>
                </div>
                <h3 class="text-base font-black text-slate-900">Choose App &amp; Region</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Head to the <strong>SMS Verify</strong> page. Pick your server (USA DaisySMS Server or Global Server). Use the search bar to find your target service (e.g. <em>WhatsApp, OpenAI, Telegram, TikTok</em>) and select your preferred country.
                </p>
                <!-- Mini Simulation Card -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs font-mono">
                    <span class="font-bold text-slate-700">🇺🇸 United States • OpenAI / ChatGPT</span>
                    <span class="font-black text-[#0d9488]">$0.80</span>
                </div>
                <div class="p-3 rounded-xl bg-teal-50 border border-teal-200/70 text-[11px] text-teal-900 font-medium">
                    💡 <strong>Pro-Tip:</strong> USA numbers from DaisySMS are clean non-VoIP physical SIM lines, ideal for strict verification apps like WhatsApp and Claude.
                </div>
            </div>

            <!-- Step 3 -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-teal-100 text-[#0d9488]">Step 03</span>
                    <span class="text-xs font-bold text-slate-400">Copy &amp; Request Code</span>
                </div>
                <h3 class="text-base font-black text-slate-900">Copy Number into Your App</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Click <strong>"Rent Number"</strong>. Your private virtual number will appear instantly on screen with a countdown timer. Click the <strong>"Copy"</strong> button, open your target app, and paste the number to request the OTP.
                </p>
                <!-- Mini Simulation Card -->
                <div class="p-3.5 rounded-2xl bg-slate-900 text-white flex items-center justify-between text-xs font-mono">
                    <span class="font-black text-teal-300">+1 (205) 555-0149</span>
                    <span class="px-2 py-0.5 rounded bg-teal-500 text-slate-950 font-black text-[10px]">COPIED</span>
                </div>
                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200/70 text-[11px] text-amber-900 font-medium">
                    ⚠️ <strong>Important:</strong> Do not close or navigate away from the page while waiting for the code. The system checks every 2 seconds.
                </div>
            </div>

            <!-- Step 4 -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-teal-100 text-[#0d9488]">Step 04</span>
                    <span class="text-xs font-bold text-slate-400">Receive OTP &amp; Auto-Refund</span>
                </div>
                <h3 class="text-base font-black text-slate-900">Grab Your OTP &amp; Complete Verification</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Within 5 to 60 seconds, your verification code will flash on the card with a loud chime and 1-click copy button. If the code does not arrive within the timer, click <strong>"Cancel &amp; Refund"</strong> (or let it expire) and your money will be instantly credited back.
                </p>
                <!-- Mini Simulation Card -->
                <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-900 flex items-center justify-between text-xs">
                    <span class="font-bold">Received OTP Code:</span>
                    <span class="text-lg font-mono font-black text-emerald-700 tracking-widest bg-white px-3 py-1 rounded-lg border border-emerald-200">849201</span>
                </div>
                <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200/70 text-[11px] text-emerald-900 font-medium">
                    🎉 <strong>Success:</strong> Enter the code in your app to complete sign-up!
                </div>
            </div>
        </div>

        <!-- FAQs for SMS -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-slate-200/80 space-y-4">
            <h3 class="text-lg font-black text-slate-900">Frequently Asked Questions — SMS Verifications</h3>
            
            <div class="space-y-2">
                <div class="border border-slate-200 rounded-2xl overflow-hidden">
                    <button @click="toggleFaq('sms1')" class="w-full p-4 text-left font-bold text-xs sm:text-sm text-slate-900 flex items-center justify-between bg-slate-50 hover:bg-slate-100 transition">
                        <span>What happens if the SMS code never arrives?</span>
                        <span x-text="faqOpen === 'sms1' ? '−' : '+'" class="text-base font-black"></span>
                    </button>
                    <div x-show="faqOpen === 'sms1'" class="p-4 text-xs text-slate-600 bg-white leading-relaxed border-t border-slate-100">
                        You are 100% protected. If an SMS does not arrive within 10 to 20 minutes (depending on provider), or if you click "Cancel &amp; Refund", the entire order price is immediately returned to your May22SMS wallet balance. You never pay for unreceived SMS.
                    </div>
                </div>

                <div class="border border-slate-200 rounded-2xl overflow-hidden">
                    <button @click="toggleFaq('sms2')" class="w-full p-4 text-left font-bold text-xs sm:text-sm text-slate-900 flex items-center justify-between bg-slate-50 hover:bg-slate-100 transition">
                        <span>Can I reuse the same number later?</span>
                        <span x-text="faqOpen === 'sms2' ? '−' : '+'" class="text-base font-black"></span>
                    </button>
                    <div x-show="faqOpen === 'sms2'" class="p-4 text-xs text-slate-600 bg-white leading-relaxed border-t border-slate-100">
                        These are temporary one-time-use activation numbers designed to protect your privacy and bypass phone gates. Once an activation completes or expires, that specific line is released and cannot be reused for new activations.
                    </div>
                </div>

                <div class="border border-slate-200 rounded-2xl overflow-hidden">
                    <button @click="toggleFaq('sms3')" class="w-full p-4 text-left font-bold text-xs sm:text-sm text-slate-900 flex items-center justify-between bg-slate-50 hover:bg-slate-100 transition">
                        <span>What is the difference between USA DaisySMS and Global Server?</span>
                        <span x-text="faqOpen === 'sms3' ? '−' : '+'" class="text-base font-black"></span>
                    </button>
                    <div x-show="faqOpen === 'sms3'" class="p-4 text-xs text-slate-600 bg-white leading-relaxed border-t border-slate-100">
                        USA DaisySMS Server uses real USA physical cellular carriers (Verizon, AT&amp;T, T-Mobile) and works on strict apps that block virtual VoIP numbers. The Global Server offers numbers across 100+ countries worldwide (UK, Nigeria, Germany, Russia, India, etc.).
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: DIGITAL LOGS & ACCOUNTS TUTORIAL -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'logs'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
        
        <!-- Summary & CTA Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg">📄</span>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900">How to Buy &amp; Access Digital Social Logs</h2>
                </div>
                <p class="text-xs sm:text-sm text-slate-600 max-w-2xl leading-relaxed">
                    Purchase aged, verified social media accounts (TikTok, Instagram, Twitter/X, Snapchat, Facebook, Gmail, Telegram) with instant automated delivery of login credentials, cookies, and 2FA recovery secrets.
                </p>
                <div class="flex flex-wrap items-center gap-3 pt-2 text-xs font-bold text-slate-500">
                    <span class="text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-lg">⚡ Instant Vault Delivery</span>
                    <span class="text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg">🔐 Format: Username:Pass:2FA:Mail</span>
                    <span class="text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg">🎥 Includes Video Guides</span>
                </div>
            </div>
            <a href="/logs-store" class="px-6 py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-indigo-600/20 text-center shrink-0 transition active:scale-95">
                Open Logs Store →
            </a>
        </div>

        <!-- Step-by-Step Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Step 1 -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-indigo-100 text-indigo-700">Step 01</span>
                    <span class="text-xs font-bold text-slate-400">Filter By Platform</span>
                </div>
                <h3 class="text-base font-black text-slate-900">Select Platform Pill (TikTok, IG, Twitter...)</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Use the top filter bar in the Logs Store to select your favorite platform: <strong>TikTok, Instagram, Twitter/X, Snapchat, Facebook, YouTube, Telegram</strong>, or <strong>Email</strong>. The list instantly filters to show in-stock accounts.
                </p>
                <!-- Mini Simulation Card -->
                <div class="flex flex-wrap gap-1.5 p-2 rounded-2xl bg-slate-50 border border-slate-200 text-[11px] font-bold">
                    <span class="px-2.5 py-1 rounded-lg bg-indigo-600 text-white">TikTok (45)</span>
                    <span class="px-2.5 py-1 rounded-lg bg-white text-slate-700 border border-slate-200">Instagram (82)</span>
                    <span class="px-2.5 py-1 rounded-lg bg-white text-slate-700 border border-slate-200">Twitter/X (31)</span>
                </div>
                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200/70 text-[11px] text-amber-900 font-medium">
                    💡 <strong>Pro-Tip:</strong> Check the account details! Some accounts come with 1k-10k followers, while others are blank aged accounts from 2018-2023.
                </div>
            </div>

            <!-- Step 2 -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-indigo-100 text-indigo-700">Step 02</span>
                    <span class="text-xs font-bold text-slate-400">Choose Quantity &amp; Cart</span>
                </div>
                <h3 class="text-base font-black text-slate-900">Select Quantity with Stepper (- / +)</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Use the inline <code>- [Qty] +</code> stepper to choose how many accounts you want. The total price updates in real-time. Click <strong>"Buy Now"</strong> for instant checkout or <strong>"Add to Cart"</strong> to bundle multiple categories together.
                </p>
                <!-- Mini Simulation Card -->
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <button class="w-6 h-6 rounded bg-slate-200 text-slate-700 font-bold">−</button>
                        <span class="font-bold text-slate-900">2 pcs</span>
                        <button class="w-6 h-6 rounded bg-slate-200 text-slate-700 font-bold">+</button>
                    </div>
                    <span class="font-black text-indigo-600">Total: $5.00 / ₦7,500</span>
                </div>
                <div class="p-3 rounded-xl bg-indigo-50 border border-indigo-200/70 text-[11px] text-indigo-900 font-medium">
                    💡 <strong>Pro-Tip:</strong> Buying in bulk? If an item only has 3 in stock, the stepper automatically prevents you from selecting more than available inventory.
                </div>
            </div>

            <!-- Step 3 -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-indigo-100 text-indigo-700">Step 03</span>
                    <span class="text-xs font-bold text-slate-400">Instant Vault Delivery</span>
                </div>
                <h3 class="text-base font-black text-slate-900">Unlock Credentials in "My Purchased Vault"</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Immediately after payment, your accounts are decrypted and placed in your <strong>"My Purchased Vault"</strong> tab. You will see the Username, Password, Linked Email, and 2FA Secret Key with 1-click copy buttons.
                </p>
                <!-- Mini Simulation Card -->
                <div class="p-3.5 rounded-2xl bg-slate-900 text-emerald-300 font-mono text-[11px] space-y-1">
                    <div>user_alpha99 : Passw0rd123! : JBSWY3DPEHPK3PXP</div>
                    <div class="text-[10px] text-slate-400">Format: username:password:2FA_secret</div>
                </div>
                <div class="p-3 rounded-xl bg-teal-50 border border-teal-200/70 text-[11px] text-teal-900 font-medium">
                    💡 <strong>Pro-Tip:</strong> You also receive an email receipt with your purchased accounts securely attached so you never lose them.
                </div>
            </div>

            <!-- Step 4 -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-indigo-100 text-indigo-700">Step 04</span>
                    <span class="text-xs font-bold text-slate-400">Login &amp; 2FA Codes</span>
                </div>
                <h3 class="text-base font-black text-slate-900">How to Generate 2FA Verification Codes</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    If the account requires a 2FA code during login, copy the 16-32 character secret key (e.g. <code>JBSWY3DPE...</code>) and paste it into <a href="https://2fa.live" target="_blank" class="underline text-indigo-600 font-bold">2fa.live</a> or Google Authenticator. It will generate your 6-digit login token instantly.
                </p>
                <!-- Mini Simulation Card -->
                <div class="p-3 rounded-2xl bg-indigo-50 border border-indigo-200 flex items-center justify-between text-xs">
                    <span class="font-bold text-indigo-950">2FA Live Code:</span>
                    <span class="font-mono font-black text-indigo-700 text-base">519 284</span>
                </div>
                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200/70 text-[11px] text-amber-900 font-medium">
                    ⚠️ <strong>Security Rule:</strong> Change the password and linked recovery email within 24 hours of purchase to make the account fully yours.
                </div>
            </div>
        </div>

        <!-- Dynamic Category Video Guides (If available) -->
        @if(isset($categoryVideos) && $categoryVideos->count() > 0)
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-slate-200/80 space-y-4">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🎥</span>
                    <h3 class="text-lg font-black text-slate-900">Official Video Walkthroughs for Social Logs</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($categoryVideos as $plat => $vids)
                        @foreach($vids as $vid)
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-indigo-100 text-indigo-700">{{ $plat }}</span>
                                    <span class="text-[10px] font-bold text-slate-400">Video Guide</span>
                                </div>
                                <h4 class="text-xs font-black text-slate-900">{{ $vid->title }}</h4>
                                @if($vid->description)
                                    <p class="text-[11px] text-slate-500 leading-snug line-clamp-2">{{ $vid->description }}</p>
                                @endif
                                @if($vid->video_url)
                                    <a href="{{ $vid->video_url }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-black text-indigo-600 hover:text-indigo-800">
                                        <span>Watch Tutorial</span>
                                        <span>↗</span>
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        @endif

    </div>

    <!-- ========================================================================= -->
    <!-- TAB 3: SOCIAL MEDIA BOOSTING TUTORIAL -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'boosting'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
        
        <!-- Summary & CTA Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-lg">🚀</span>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900">How to Boost Followers, Likes, Views &amp; Shares</h2>
                </div>
                <p class="text-xs sm:text-sm text-slate-600 max-w-2xl leading-relaxed">
                    Automate genuine engagement on your TikTok, Instagram, YouTube, Twitter/X, Telegram, or Facebook accounts with fast delivery and live progress tracking.
                </p>
                <div class="flex flex-wrap items-center gap-3 pt-2 text-xs font-bold text-slate-500">
                    <span class="text-rose-600 bg-rose-50 px-2.5 py-1 rounded-lg">⚡ Instant API Dispatch</span>
                    <span class="text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg">📊 Live Progress Tracking Bar</span>
                    <span class="text-purple-600 bg-purple-50 px-2.5 py-1 rounded-lg">🌐 5,000+ Global SMM Services</span>
                </div>
            </div>
            <a href="/social-boosting" class="px-6 py-3.5 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-rose-600/20 text-center shrink-0 transition active:scale-95">
                Start Boosting Now →
            </a>
        </div>

        <!-- Step-by-Step Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Step 1 -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-rose-100 text-rose-700">Step 01</span>
                    <span class="text-xs font-bold text-slate-400">Filter Platform</span>
                </div>
                <h3 class="text-base font-black text-slate-900">Pick Your Target Platform</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Click any popular platform pill: <strong>TikTok, Instagram, Twitter/X, Snapchat, YouTube, Telegram, Facebook</strong>. The service dropdown automatically filters thousands of services down to your specific platform.
                </p>
                <!-- Mini Simulation Card -->
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 text-xs font-bold flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                    <span>Selected: Instagram &bull; Real High-Retention Followers</span>
                </div>
                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200/70 text-[11px] text-amber-900 font-medium">
                    ⚠️ <strong>CRITICAL RULE:</strong> Make sure your account is set to <strong>PUBLIC</strong>. Private accounts will fail and cannot receive followers or likes!
                </div>
            </div>

            <!-- Step 2 -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-rose-100 text-rose-700">Step 02</span>
                    <span class="text-xs font-bold text-slate-400">Target Link</span>
                </div>
                <h3 class="text-base font-black text-slate-900">Paste Public Profile or Post Link</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Paste the exact link. For <strong>Followers</strong>, paste your public profile link (e.g. <code>https://instagram.com/your_username</code>). For <strong>Likes/Views</strong>, paste the specific post or video link (e.g. <code>https://tiktok.com/@user/video/12345</code>).
                </p>
                <!-- Mini Simulation Card -->
                <div class="p-3 rounded-2xl bg-slate-900 text-white font-mono text-[11px]">
                    https://www.tiktok.com/@mybrand/video/7391823901
                </div>
                <div class="p-3 rounded-xl bg-teal-50 border border-teal-200/70 text-[11px] text-teal-900 font-medium">
                    💡 <strong>Pro-Tip:</strong> Double check that the link opens in an incognito window without asking you to log in.
                </div>
            </div>

            <!-- Step 3 -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-rose-100 text-rose-700">Step 03</span>
                    <span class="text-xs font-bold text-slate-400">Enter Quantity</span>
                </div>
                <h3 class="text-base font-black text-slate-900">Set Quantity with Quick Presets</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Type your desired quantity or click the quick presets: <code>+500</code>, <code>+1,000</code>, <code>+2,500</code>, <code>+5,000</code>, <code>+10,000</code>. The live cost preview calculates your exact charge in USD and NGN.
                </p>
                <!-- Mini Simulation Card -->
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-700">Quantity: 1,000 Followers</span>
                    <span class="font-black text-rose-600">Total: $1.20 / ₦1,800</span>
                </div>
                <div class="p-3 rounded-xl bg-rose-50 border border-rose-200/70 text-[11px] text-rose-900 font-medium">
                    💡 <strong>Pro-Tip:</strong> Each service displays Min and Max quantity limits (e.g., Min 100, Max 50,000). Stay within this range!
                </div>
            </div>

            <!-- Step 4 -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-rose-100 text-rose-700">Step 04</span>
                    <span class="text-xs font-bold text-slate-400">Track Live Progress</span>
                </div>
                <h3 class="text-base font-black text-slate-900">Monitor Real-Time Fulfillment</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Click <strong>"Submit Order"</strong>. Your order is dispatched directly to high-speed delivery nodes. In the <strong>"Live Orders Tracker"</strong>, you can watch the animated progress bar update from <em>Pending</em> &rarr; <em>In Progress</em> &rarr; <em>Completed</em>.
                </p>
                <!-- Mini Simulation Card -->
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5 text-xs">
                    <div class="flex justify-between font-bold">
                        <span>Progress: 850 / 1,000 Delivered</span>
                        <span class="text-emerald-600">85%</span>
                    </div>
                    <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                        <div class="bg-emerald-500 h-full rounded-full" style="width: 85%;"></div>
                    </div>
                </div>
                <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200/70 text-[11px] text-emerald-900 font-medium">
                    🎉 <strong>Auto-Refill:</strong> For guaranteed services, if any drops occur, our system triggers automatic refills.
                </div>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- TAB 4: WALLET FUNDING & ADD MONEY TUTORIAL -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'funding'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
        
        <!-- Summary & CTA Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg">💳</span>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900">How to Fund Your Wallet &amp; Instant Bank Transfer</h2>
                </div>
                <p class="text-xs sm:text-sm text-slate-600 max-w-2xl leading-relaxed">
                    Deposit Nigerian Naira (NGN) or USD using your dedicated automated Monietude virtual bank account, instant debit card checkout, or crypto deposits.
                </p>
                <div class="flex flex-wrap items-center gap-3 pt-2 text-xs font-bold text-slate-500">
                    <span class="text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg">⚡ Instant Credit (&lt; 10 secs)</span>
                    <span class="text-teal-600 bg-teal-50 px-2.5 py-1 rounded-lg">🏦 Dedicated Wema / Sterling Account</span>
                    <span class="text-purple-600 bg-purple-50 px-2.5 py-1 rounded-lg">🪙 Zero-Fee Crypto Swap</span>
                </div>
            </div>
            <a href="/funding" class="px-6 py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-emerald-600/20 text-center shrink-0 transition active:scale-95">
                Add Money Now →
            </a>
        </div>

        <!-- Step-by-Step Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Step 1: Virtual Account -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-700">Method 01 (Recommended)</span>
                    <span class="text-xs font-bold text-emerald-600">Fastest</span>
                </div>
                <h3 class="text-base font-black text-slate-900">Dedicated Virtual Bank Account</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Every registered user gets a personal, dedicated Nigerian virtual bank account (Wema Bank or Sterling). Just open your bank app (OPay, Kuda, PalmPay, GTBank, Zenith, Access), transfer any amount to your account number, and your May22SMS wallet is credited in <strong>under 10 seconds</strong> automatically!
                </p>
                <!-- Mini Simulation Card -->
                <div class="p-3.5 rounded-2xl bg-slate-900 text-white flex items-center justify-between text-xs">
                    <div>
                        <div class="text-[10px] text-slate-400 font-bold uppercase">Bank Name: WEMA BANK</div>
                        <div class="text-base font-mono font-black text-emerald-400">9281729381</div>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-emerald-500 text-slate-950 font-black text-[10px]">COPY</span>
                </div>
                <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200/70 text-[11px] text-emerald-900 font-medium">
                    💡 <strong>Pro-Tip:</strong> Save this account number as a beneficiary in your banking app! You can send money to it at 2:00 AM and it credits automatically with zero human delay.
                </div>
            </div>

            <!-- Step 2: Card & Instant Online Gateway -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-teal-100 text-[#0d9488]">Method 02</span>
                    <span class="text-xs font-bold text-slate-400">Card / USSD</span>
                </div>
                <h3 class="text-base font-black text-slate-900">Card &amp; Online Checkout (PocketFi / PaymentPoint)</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Prefer using your debit card or USSD? Select <strong>PocketFi</strong> or <strong>PaymentPoint</strong>, enter your amount (e.g. ₦1,000), and click Proceed. Complete 3D Secure OTP verification and you are returned back to your dashboard with funds credited.
                </p>
                <!-- Mini Simulation Card -->
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-700">Accepted: Mastercard, Visa, Verve</span>
                    <span class="font-black text-teal-700">3D Secure</span>
                </div>
                <div class="p-3 rounded-xl bg-teal-50 border border-teal-200/70 text-[11px] text-teal-900 font-medium">
                    💡 <strong>Pro-Tip:</strong> If your bank's OTP server is slow, always use Method 01 (Virtual Account Transfer) which does not rely on card networks.
                </div>
            </div>

            <!-- Step 3: Crypto -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-amber-100 text-amber-700">Method 03</span>
                    <span class="text-xs font-bold text-slate-400">Web3 / Crypto</span>
                </div>
                <h3 class="text-base font-black text-slate-900">Crypto Deposits (USDT TRC20 / BEP20)</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Select <strong>Crypto</strong>, choose your preferred token (USDT TRC20 or BEP20), and generate your one-time payment address with QR code. Transfer the exact amount from Binance, Bybit, TrustWallet, or MetaMask. After 1 blockchain confirmation, your wallet is credited.
                </p>
                <!-- Mini Simulation Card -->
                <div class="p-3.5 rounded-2xl bg-slate-900 text-amber-300 font-mono text-[11px] truncate">
                    TQ3Z9...b8FwK (USDT TRC20)
                </div>
                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200/70 text-[11px] text-amber-900 font-medium">
                    ⚠️ <strong>Warning:</strong> Always ensure you send via the matching network (TRC20 for TRON, BEP20 for BSC). Do not send ERC20 to a TRC20 address.
                </div>
            </div>

            <!-- Step 4: Currency Toggle -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-purple-100 text-purple-700">Feature</span>
                    <span class="text-xs font-bold text-slate-400">Multi-Currency</span>
                </div>
                <h3 class="text-base font-black text-slate-900">Switch Between USD ($) and Naira (₦)</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    You can toggle your display currency at any time using the <strong>USD / NGN</strong> toggle pill at the top of your screen. All prices across SMS, Logs, and Boost services update smoothly using the official live exchange rate.
                </p>
                <!-- Mini Simulation Card -->
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-700">Exchange Rate:</span>
                    <span class="font-black text-purple-700">1 USD = ₦{{ number_format($ngnPerUsd ?? 1500, 2) }}</span>
                </div>
                <div class="p-3 rounded-xl bg-purple-50 border border-purple-200/70 text-[11px] text-purple-900 font-medium">
                    💡 <strong>Pro-Tip:</strong> Your underlying funds are safe regardless of currency fluctuations.
                </div>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- TAB 5: DAILY REWARDS & STREAK TUTORIAL -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'streak'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
        
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg">🎁</span>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900">How to Earn Free Daily Check-In Streak Bonuses</h2>
                </div>
                <p class="text-xs sm:text-sm text-slate-600 max-w-2xl leading-relaxed">
                    Check in every day to claim free funds credited directly to your wallet balance. Keep your streak alive to unlock bonus multipliers!
                </p>
                <div class="flex flex-wrap items-center gap-3 pt-2 text-xs font-bold text-slate-500">
                    <span class="text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg">🔥 Streak Multiplier: Up to 7 Days</span>
                    <span class="text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg">💰 100% Free Daily Balance</span>
                    <span class="text-teal-600 bg-teal-50 px-2.5 py-1 rounded-lg">⏰ Resets Every 24 Hours</span>
                </div>
            </div>
            <a href="/dashboard#daily-gift" class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-black text-xs uppercase tracking-wider shadow-lg shadow-orange-500/20 text-center shrink-0 transition active:scale-95">
                Claim Today's Bonus →
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-3">
                <span class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center font-black text-sm">01</span>
                <h4 class="text-sm font-black text-slate-900">Log In Every Day</h4>
                <p class="text-xs text-slate-600 leading-relaxed">Open your May22SMS dashboard once every 24 hours. The Daily Check-in card will bounce with an open gift box.</p>
            </div>
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-3">
                <span class="w-10 h-10 rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center font-black text-sm">02</span>
                <h4 class="text-sm font-black text-slate-900">Click Claim Gift</h4>
                <p class="text-xs text-slate-600 leading-relaxed">Tap the claim button. Your random reward is instantly transferred into your spendable wallet balance.</p>
            </div>
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-3">
                <span class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-black text-sm">03</span>
                <h4 class="text-sm font-black text-slate-900">Keep Your Streak</h4>
                <p class="text-xs text-slate-600 leading-relaxed">Checking in for 7 consecutive days unlocks higher prize tiers and free SMS tokens.</p>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- TAB 6: SPORTYBET & BETTING TUTORIAL -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'sporty'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
        
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-slate-200/80 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-red-100 text-red-600 flex items-center justify-center text-lg">⚽</span>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900">How to Use SportyBet Codes &amp; Bet Wallets</h2>
                </div>
                <p class="text-xs sm:text-sm text-slate-600 max-w-2xl leading-relaxed">
                    Purchase verified high-odds booking codes and load them directly into your SportyBet app with zero hassle.
                </p>
            </div>
            <a href="/sporty-bet-codes" class="px-6 py-3.5 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-rose-600/20 text-center shrink-0 transition active:scale-95">
                Browse SportyBet Codes →
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-3">
                <span class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center font-black text-sm">01</span>
                <h4 class="text-sm font-black text-slate-900">Explore Available Matches</h4>
                <p class="text-xs text-slate-600 leading-relaxed">Browse curated high-odds tickets with detailed match fixtures, odds calculation, and tipster analysis.</p>
            </div>
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-3">
                <span class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center font-black text-sm">02</span>
                <h4 class="text-sm font-black text-slate-900">Unlock Booking Code</h4>
                <p class="text-xs text-slate-600 leading-relaxed">Click purchase to unlock the official SportyBet alphanumeric booking code (e.g. <code>BC82K9A</code>).</p>
            </div>
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-3">
                <span class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-black text-sm">03</span>
                <h4 class="text-sm font-black text-slate-900">Load in SportyBet App</h4>
                <p class="text-xs text-slate-600 leading-relaxed">Open the SportyBet app, tap "Load Bet Slip", paste the code, and stake your ticket instantly.</p>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- TAB: SAVE SMALL SMALL (TARGET GOAL SAVINGS) TUTORIAL -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'savings'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
        
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-slate-200/80 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#0d9488] to-teal-400 text-white flex items-center justify-center text-2xl shadow-md shadow-teal-500/20">💰</span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900">Save Small Small (Target Savings)</h2>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800">16.9% P.A.</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Grow disciplined savings for your life goals while earning high-yield daily interest.</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('savings.index') }}" class="px-4 py-2.5 rounded-xl bg-[#0d9488] hover:bg-teal-700 text-white font-bold text-xs transition shadow-sm flex items-center gap-1.5">
                        <span>Open Savings App &rarr;</span>
                    </a>
                    <a href="{{ route('savings.calculator') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                        <span>🧮 Calculator</span>
                    </a>
                </div>
            </div>

            <p class="text-xs sm:text-sm text-slate-600 max-w-3xl leading-relaxed">
                Save Small Small allows you to set up dedicated, locked savings plans for any milestone — Christmas, weddings, house rent, gadgets, or emergency reserves. Funds are securely locked from your main wallet balance and accrue interest daily at up to 16.9% per annum.
            </p>
        </div>

        <!-- 4 Step Visual Workflow -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Step 1 -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-3 relative overflow-hidden">
                <div class="w-10 h-10 rounded-2xl bg-teal-100 text-[#0d9488] flex items-center justify-center font-black text-sm">01</div>
                <h4 class="text-sm font-black text-slate-900">Pick a Financial Goal</h4>
                <p class="text-xs text-slate-600 leading-relaxed">Choose from preset goal categories (Rent, Christmas, Gadgets, Travel, Emergency) or define a custom target amount and timeline (30 to 365 days).</p>
            </div>

            <!-- Step 2 -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-3 relative overflow-hidden">
                <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-black text-sm">02</div>
                <h4 class="text-sm font-black text-slate-900">Lock In Main Wallet Funds</h4>
                <p class="text-xs text-slate-600 leading-relaxed">Transfer initial deposit directly from your available May22SMS wallet balance. Every transfer is debited atomically with zero external gateway charges.</p>
            </div>

            <!-- Step 3 -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-3 relative overflow-hidden">
                <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-black text-sm">03</div>
                <h4 class="text-sm font-black text-slate-900">Earn Daily Interest Accruals</h4>
                <p class="text-xs text-slate-600 leading-relaxed">Interest is computed daily: <code class="bg-slate-100 px-1 py-0.5 rounded text-[11px] font-mono">Principal &times; 16.9% &divide; 365</code> and credited into your plan ledger automatically every midnight.</p>
            </div>

            <!-- Step 4 -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-3 relative overflow-hidden">
                <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center font-black text-sm">04</div>
                <h4 class="text-sm font-black text-slate-900">Automated Maturity Payout</h4>
                <p class="text-xs text-slate-600 leading-relaxed">On maturity date, 100% of your principal plus net earned profit (minus a tiny 1.5% fee on earned interest only) is credited back to your main wallet automatically.</p>
            </div>
        </div>

        <!-- Savings Features & Terms Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Break Savings Disclosure -->
            <div class="bg-amber-50/60 rounded-3xl p-6 border border-amber-200/80 space-y-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">⚠️</span>
                    <h3 class="text-sm font-black text-amber-950">Early Break Policy &amp; Transparency</h3>
                </div>
                <p class="text-xs text-amber-900 leading-relaxed">
                    Life happens! If you experience an emergency before your plan's maturity date, you can use the <strong>Break Savings</strong> button on your plan dashboard.
                </p>
                <div class="bg-white/80 p-3.5 rounded-2xl border border-amber-200 text-xs text-amber-950 space-y-1.5 font-medium">
                    <div class="flex items-center justify-between">
                        <span>Original Principal Returned:</span>
                        <span class="font-bold text-emerald-700">100% (Guaranteed)</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Accrued Interest:</span>
                        <span class="font-bold text-rose-600">Forfeited upon early liquidation</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Early Break Principal Fee:</span>
                        <span class="font-bold text-emerald-700">₦0.00 (Zero fee)</span>
                    </div>
                </div>
            </div>

            <!-- Maturity Fee Transparency -->
            <div class="bg-teal-50/60 rounded-3xl p-6 border border-teal-200/80 space-y-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🛡</span>
                    <h3 class="text-sm font-black text-teal-950">Maturity Fee Breakdown</h3>
                </div>
                <p class="text-xs text-teal-900 leading-relaxed">
                    We believe in fair financial products. At maturity, our platform charges a modest <strong>1.5% fee on earned interest only</strong> — never on your principal deposit.
                </p>
                <div class="bg-white/80 p-3.5 rounded-2xl border border-teal-200 text-xs text-teal-950 space-y-1.5 font-medium">
                    <div class="flex items-center justify-between">
                        <span>If you earn ₦10,000 profit:</span>
                        <span class="font-bold text-slate-800">Gross interest</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>1.5% Platform Maintenance Fee:</span>
                        <span class="font-bold text-rose-600">-₦150.00</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Net Profit Payout:</span>
                        <span class="font-bold text-emerald-700">+₦9,850.00 + Full Principal</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- TAB 7: COMING SOON FINTECH UTILITIES TUTORIAL -->
    <!-- ========================================================================= -->
    <div x-show="activeTab === 'utilities'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
        
        <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-slate-200/80 space-y-4">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-teal-100 text-[#0d9488] flex items-center justify-center text-lg">⚡</span>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900">Upcoming Fintech &amp; Utility Services</h2>
            </div>
            <p class="text-xs sm:text-sm text-slate-600 max-w-3xl leading-relaxed">
                We are actively integrating automated Nigerian bill payments, airtime/data top-ups, and crypto-to-fiat conversion directly into your May22SMS wallet.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Crypto to Naira -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-2xl">🪙</span>
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-emerald-100 text-emerald-800">COMING SOON</span>
                </div>
                <h4 class="text-sm font-black text-slate-900">Receive Crypto &amp; Swap to Naira</h4>
                <p class="text-xs text-slate-600 leading-relaxed">Generate a deposit address for USDT, BTC, or ETH. It automatically converts to Naira and deposits into your bank account or wallet.</p>
                <a href="/services/crypto-to-naira" class="inline-block text-xs font-black text-[#0d9488] hover:underline">Read Feature Roadmap &rarr;</a>
            </div>

            <!-- Airtime & Data -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-2xl">📶</span>
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-emerald-100 text-emerald-800">COMING SOON</span>
                </div>
                <h4 class="text-sm font-black text-slate-900">VTU Airtime &amp; Cheap Data</h4>
                <p class="text-xs text-slate-600 leading-relaxed">Instant recharge for MTN, Airtel, Glo, and 9mobile at 3-5% discount with automated top-up in under 5 seconds.</p>
                <a href="/services/airtime-data" class="inline-block text-xs font-black text-[#0d9488] hover:underline">Read Feature Roadmap &rarr;</a>
            </div>

            <!-- Electricity -->
            <div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-2xl">⚡</span>
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase bg-emerald-100 text-emerald-800">COMING SOON</span>
                </div>
                <h4 class="text-sm font-black text-slate-900">Electricity Tokens</h4>
                <p class="text-xs text-slate-600 leading-relaxed">Prepaid meter recharge for IKEDC, EKEDC, AEDC, IBEDC, PHED, and more with instant token SMS and zero service fee.</p>
                <a href="/services/electricity" class="inline-block text-xs font-black text-[#0d9488] hover:underline">Read Feature Roadmap &rarr;</a>
            </div>
        </div>

    </div>

    <!-- Need Help Sticky Footer Card -->
    <div class="rounded-3xl p-6 sm:p-8 bg-slate-900 text-white shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base sm:text-lg font-black text-white">Still have questions about a service?</h3>
            <p class="text-xs text-slate-400 mt-1">Our live support desk operates 24/7 with an average response time of under 15 minutes.</p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <a href="/tickets" class="px-5 py-2.5 rounded-xl bg-teal-500 hover:bg-teal-400 text-slate-950 font-black text-xs transition">
                Create Support Ticket
            </a>
            <a href="/dashboard" class="px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs transition">
                Back to Dashboard
            </a>
        </div>
    </div>

</div>

@endsection
