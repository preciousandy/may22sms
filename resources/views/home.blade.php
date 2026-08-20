@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-950 text-white overflow-hidden">
    <!-- Animated Background -->
    <div class="fixed inset-0 z-0 overflow-hidden">
        <div class="absolute -top-40 -left-40 w-[40rem] h-[40rem] rounded-full bg-[#0d9488]/30 blur-3xl animate-pulse"></div>
        <div class="absolute top-1/2 -right-40 w-[45rem] h-[45rem] rounded-full bg-[#facc15]/25 blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
        <div class="absolute bottom-0 left-1/3 w-[35rem] h-[35rem] rounded-full bg-gray-700/25 blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <!-- Navigation -->
    <nav class="relative z-50 border-b border-white/10 bg-gray-950/80 backdrop-blur-2xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="h-20 flex items-center justify-between">
                <a href="/" class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-white shadow-xl shadow-[#0d9488]/30 overflow-hidden flex items-center justify-center">
                        <img src="{{ asset('logo/favicon-512.png') }}" alt="{{ $siteName ?? 'Logo' }}" class="h-10 w-10 object-contain">
                    </div>
                    <div>
                        <div class="text-2xl font-black bg-gradient-to-r from-[#0d9488] to-[#facc15] bg-clip-text text-transparent">{{ $siteName ?? 'May22SMS' }}</div>
                        <div class="text-sm text-gray-400">Digital services reimagined</div>
                    </div>
                </a>

                <div class="hidden md:flex items-center gap-8 text-base font-bold text-gray-300">
                    <a href="#services" class="hover:text-white transition-colors">Services</a>
                    <a href="#features" class="hover:text-white transition-colors">Features</a>
                    <a href="#pricing" class="hover:text-white transition-colors">Pricing</a>
                </div>

                <div class="hidden md:flex items-center gap-4">
                    @auth
                        <a href="/dashboard" class="bg-white/10 hover:bg-white/20 border border-white/10 px-5 py-3 rounded-xl font-black transition-all hover:scale-105">
                            Dashboard
                        </a>
                    @else
                        <a href="/login" class="text-white/80 hover:text-white font-bold px-4 py-3 rounded-xl transition-colors">
                            Log in
                        </a>
                        <a href="/register" class="bg-gradient-to-r from-[#0d9488] to-teal-700 hover:from-teal-500 hover:to-teal-600 text-white px-6 py-3 rounded-xl font-black shadow-xl shadow-[#0d9488]/40 transition-all hover:scale-105">
                            Get Started Free
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative z-10 pt-24 pb-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Announcement Banner -->
            @if(($announcementEnabled ?? false) && !empty($announcementText ?? ''))
                <div class="mb-10 inline-flex items-center gap-3 bg-gradient-to-r from-[#facc15]/20 via-[#facc15]/10 to-transparent border border-[#facc15]/30 px-6 py-4 rounded-full">
                    <div class="h-2.5 w-2.5 rounded-full bg-[#facc15] animate-pulse"></div>
                    <span class="text-yellow-100 font-extrabold">{{ $announcementText }}</span>
                </div>
            @endif

            <div class="grid lg:grid-cols-1 gap-16 items-center">
                <!-- Left Content -->
                <div class="relative">
                    <div class="inline-flex items-center gap-2 bg-white/5 border border-white/10 rounded-full px-5 py-2.5 text-sm font-extrabold text-gray-300 mb-8">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#0d9488] opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-[#0d9488]"></span>
                        </span>
                        Trusted by 50,000+ users
                    </div>

                    <h1 class="text-5xl sm:text-6xl lg:text-7xl xl:text-8xl font-black leading-[0.9] tracking-tight mb-8">
                        <span class="block text-white">Supercharge</span>
                        <span class="block bg-gradient-to-r from-[#0d9488] to-[#facc15] bg-clip-text text-transparent">Your Digital Life</span>
                    </h1>

                    <p class="text-xl text-gray-400 max-w-2xl mb-10 leading-relaxed">
                        {{ $homeHeroSubtitle ?? 'One platform for SMS verification, logs, and boosting. Instant delivery, transparent tracking, and beautiful UI.' }}
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 mb-16">
                        @auth
                            <a href="/dashboard" class="group bg-gradient-to-r from-[#0d9488] to-teal-700 hover:from-teal-500 hover:to-teal-600 text-white px-10 py-5 rounded-2xl font-black text-lg shadow-xl shadow-[#0d9488]/40 transition-all hover:scale-105 inline-flex items-center justify-center gap-3">
                                Go to Dashboard
                                <svg class="w-6 h-6 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </a>
                        @else
                            <a href="/register" class="group bg-gradient-to-r from-[#0d9488] to-teal-700 hover:from-teal-500 hover:to-teal-600 text-white px-10 py-5 rounded-2xl font-black text-lg shadow-xl shadow-[#0d9488]/40 transition-all hover:scale-105 inline-flex items-center justify-center gap-3">
                                Start Free Trial
                                <svg class="w-6 h-6 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </a>
                        @endauth
                        <a href="#services" class="bg-white/10 hover:bg-white/20 border border-white/10 text-white px-10 py-5 rounded-2xl font-black text-lg transition-all hover:scale-105">
                            Explore Services
                        </a>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-6">
                        <div class="p-6 bg-white/5 border border-white/10 rounded-3xl">
                            <div class="text-4xl font-black bg-gradient-to-r from-[#0d9488] to-teal-500 bg-clip-text text-transparent">50K+</div>
                            <div class="text-sm font-bold text-gray-400 mt-1">Active Users</div>
                        </div>
                        <div class="p-6 bg-white/5 border border-white/10 rounded-3xl">
                            <div class="text-4xl font-black bg-gradient-to-r from-[#facc15] to-orange-400 bg-clip-text text-transparent">1M+</div>
                            <div class="text-sm font-bold text-gray-400 mt-1">Orders Processed</div>
                        </div>
                        <div class="p-6 bg-white/5 border border-white/10 rounded-3xl">
                            <div class="text-4xl font-black bg-gradient-to-r from-gray-400 to-gray-200 bg-clip-text text-transparent">99.9%</div>
                            <div class="text-sm font-bold text-gray-400 mt-1">Uptime</div>
                        </div>
                    </div>
                </div>

                <!-- Right Content - Animated Cards -->
                <div class="relative lg:pl-12">
                    <div class="relative">
                        <div class="absolute -inset-4 bg-gradient-to-r from-[#0d9488]/30 to-[#facc15]/30 rounded-[3rem] blur-3xl animate-pulse"></div>
                        
                        <!--<div class="relative rounded-[2.5rem] border border-white/10 bg-white/5 backdrop-blur-xl p-8">
                           
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gradient-to-br from-[#0d9488]/20 to-teal-700/10 border border-[#0d9488]/30 rounded-2xl p-6">
                                    <div class="w-12 h-12 rounded-xl bg-[#0d9488]/20 flex items-center justify-center mb-4">
                                        <svg class="w-7 h-7 text-[#0d9488]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                    </div>
                                    <div class="text-sm font-extrabold text-white/70">SMS Verified</div>
                                    <div class="text-3xl font-black text-[#0d9488]">2,847</div>
                                </div>

                                <div class="bg-gradient-to-br from-[#facc15]/20 to-yellow-600/10 border border-[#facc15]/30 rounded-2xl p-6">
                                    <div class="w-12 h-12 rounded-xl bg-[#facc15]/20 flex items-center justify-center mb-4">
                                        <svg class="w-7 h-7 text-[#facc15]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                    </div>
                                    <div class="text-sm font-extrabold text-white/70">Active Boosts</div>
                                    <div class="text-3xl font-black text-[#facc15]">426</div>
                                </div>

                                <div class="col-span-2 bg-gradient-to-br from-gray-500/20 to-gray-600/10 border border-gray-400/30 rounded-2xl p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="w-12 h-12 rounded-xl bg-[#facc15]/20 flex items-center justify-center">
                                            <svg class="w-7 h-7 text-[#facc15]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                        <span class="text-xs font-extrabold text-[#facc15] bg-[#facc15]/20 px-3 py-1 rounded-full">Live</span>
                                    </div>
                                    <div class="text-sm font-extrabold text-white/70">Wallet Balance</div>
                                    <div class="text-4xl font-black text-[#facc15]">$14,238.50</div>
                                </div>
                            </div>
                        </div>-->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="relative z-10 border-t border-white/10 bg-gray-950/70 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-32">
            <div class="text-center max-w-4xl mx-auto mb-20">
                <div class="inline-flex items-center gap-2 bg-gradient-to-r from-[#0d9488]/20 to-transparent border border-[#0d9488]/30 px-4 py-2 rounded-full mb-6">
                    <div class="w-2 h-2 rounded-full bg-[#0d9488]"></div>
                    <span class="text-sm font-extrabold text-[#0d9488] uppercase tracking-widest">Our Services</span>
                </div>
                <h2 class="text-5xl sm:text-6xl font-black mb-6 bg-gradient-to-r from-white via-[#0d9488] to-white bg-clip-text text-transparent">
                    Everything You Need
                </h2>
                <p class="text-xl text-gray-400">From SMS verification to social boosting — we've got you covered.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- SMS Verification -->
                <div class="group p-10 rounded-3xl border border-white/10 bg-white/5 hover:bg-white/10 hover:border-[#0d9488]/40 transition-all duration-500 hover:-translate-y-4">
                    <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-[#0d9488] to-teal-700 flex items-center justify-center mb-8 shadow-2xl shadow-[#0d9488]/40 group-hover:scale-110 transition-transform duration-500">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black text-white mb-4">SMS Verification</h3>
                    <p class="text-gray-400 mb-8 leading-relaxed">Buy virtual numbers from 100+ countries. Check SMS codes instantly and manage your orders.</p>
                    <a href="{{ auth()->check() ? '/sms-verify' : '/register' }}" class="inline-flex items-center gap-2 font-black text-[#0d9488] group-hover:text-teal-300">
                        Get Started
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                </div>

                <!-- Logs Store -->
                <div class="group p-10 rounded-3xl border border-white/10 bg-white/5 hover:bg-white/10 hover:border-[#facc15]/40 transition-all duration-500 hover:-translate-y-4">
                    <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-[#facc15] to-orange-500 flex items-center justify-center mb-8 shadow-2xl shadow-[#facc15]/40 group-hover:scale-110 transition-transform duration-500">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black text-white mb-4">Logs Store</h3>
                    <p class="text-gray-400 mb-8 leading-relaxed">Browse stock, add to cart, apply promo codes, and get instant delivery to your dashboard.</p>
                    <a href="{{ auth()->check() ? '/logs-store' : '/register' }}" class="inline-flex items-center gap-2 font-black text-[#facc15] group-hover:text-yellow-200">
                        Browse Logs
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                </div>

                <!-- Social Boosting -->
                <div class="group p-10 rounded-3xl border border-white/10 bg-white/5 hover:bg-white/10 hover:border-gray-400/40 transition-all duration-500 hover:-translate-y-4">
                    <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-gray-500 to-gray-700 flex items-center justify-center mb-8 shadow-2xl shadow-gray-500/40 group-hover:scale-110 transition-transform duration-500">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-black text-white mb-4">Social Boosting</h3>
                    <p class="text-gray-400 mb-8 leading-relaxed">Grow your social presence with fast, reliable boosting services and real-time tracking.</p>
                    <a href="{{ auth()->check() ? '/social-boosting' : '/register' }}" class="inline-flex items-center gap-2 font-black text-gray-300 group-hover:text-white">
                        Start Boosting
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="relative z-10 border-t border-white/10 bg-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-32">
            <div class="text-center max-w-4xl mx-auto mb-20">
                <h2 class="text-5xl sm:text-6xl font-black mb-6">Why Choose Us</h2>
                <p class="text-xl text-gray-400">We've built a platform that puts users first.</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="p-8 rounded-3xl border border-white/10 bg-white/5 hover:bg-white/10 transition-all">
                    <div class="text-4xl mb-6">⚡</div>
                    <h4 class="text-xl font-black mb-3">Instant Delivery</h4>
                    <p class="text-gray-400">Get your orders delivered to your dashboard within seconds.</p>
                </div>
                <div class="p-8 rounded-3xl border border-white/10 bg-white/5 hover:bg-white/10 transition-all">
                    <div class="text-4xl mb-6">🔒</div>
                    <h4 class="text-xl font-black mb-3">Secure & Private</h4>
                    <p class="text-gray-400">Your data is protected with industry-standard encryption.</p>
                </div>
                <div class="p-8 rounded-3xl border border-white/10 bg-white/5 hover:bg-white/10 transition-all">
                    <div class="text-4xl mb-6">💳</div>
                    <h4 class="text-xl font-black mb-3">Wallet System</h4>
                    <p class="text-gray-400">Fund once, use anywhere. Track every transaction clearly.</p>
                </div>
                <div class="p-8 rounded-3xl border border-white/10 bg-white/5 hover:bg-white/10 transition-all">
                    <div class="text-4xl mb-6">💬</div>
                    <h4 class="text-xl font-black mb-3">24/7 Support</h4>
                    <p class="text-gray-400">Get help whenever you need it via our support tickets.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative z-10 border-t border-white/10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-32">
            <div class="relative rounded-[3rem] border border-white/10 bg-gradient-to-br from-[#0d9488]/20 via-[#facc15]/10 to-gray-700/20 p-1 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-[#0d9488]/30 via-[#facc15]/20 to-gray-700/30 animate-pulse"></div>
                <div class="relative rounded-[2.7rem] bg-gray-950/90 backdrop-blur-xl p-12 sm:p-16 text-center">
                    <h2 class="text-4xl sm:text-5xl lg:text-6xl font-black mb-6">
                        Ready to Start?
                    </h2>
                    <p class="text-xl text-gray-400 max-w-2xl mx-auto mb-10">
                        Join thousands of users who trust us for their digital service needs. Create your account today and get started for free.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        @auth
                            <a href="/dashboard" class="bg-gradient-to-r from-[#0d9488] to-teal-700 text-white px-10 py-5 rounded-2xl font-black text-lg shadow-2xl shadow-[#0d9488]/40 transition-all hover:scale-105">
                                Open Dashboard
                            </a>
                        @else
                            <a href="/register" class="bg-gradient-to-r from-[#0d9488] to-teal-700 text-white px-10 py-5 rounded-2xl font-black text-lg shadow-2xl shadow-[#0d9488]/40 transition-all hover:scale-105">
                                Create Free Account
                            </a>
                            <a href="/login" class="bg-white/10 border border-white/10 text-white px-10 py-5 rounded-2xl font-black text-lg transition-all hover:bg-white/20 hover:scale-105">
                                Log In
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-white/10 bg-gray-950/90">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
                <div class="lg:col-span-1">
                    <a href="/" class="flex items-center gap-3 mb-6">
                        <div class="h-10 w-10 rounded-2xl flex items-center justify-center overflow-hidden">
                            <img src="{{ asset('logo/favicon-512.png') }}" alt="{{ $siteName ?? 'Logo' }}" class="h-20 w-30 object-contain">
                        </div>
                        <span class="text-xl font-black">{{ $siteName ?? 'May22SMS' }}</span>
                    </a>
                    <p class="text-gray-400 mb-6">Digital services in one place. Fast, simple, reliable.</p>
                </div>

                <div>
                    <h4 class="text-lg font-black mb-6">Services</h4>
                    <ul class="space-y-3">
                        <li><a href="{{ auth()->check() ? '/sms-verify' : '/register' }}" class="text-gray-400 hover:text-white transition-colors">SMS Verification</a></li>
                        <li><a href="{{ auth()->check() ? '/logs-store' : '/register' }}" class="text-gray-400 hover:text-white transition-colors">Logs Store</a></li>
                        <li><a href="{{ auth()->check() ? '/social-boosting' : '/register' }}" class="text-gray-400 hover:text-white transition-colors">Social Boosting</a></li>
                        <li><a href="{{ auth()->check() ? '/wallet' : '/register' }}" class="text-gray-400 hover:text-white transition-colors">Wallet</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-black mb-6">Company</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Careers</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">Blog</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-black mb-6">Social</h4>
                    <div class="flex gap-4">
                        <a href="https://chat.whatsapp.com/FCD1Iqs14iPBG4DpiT4Vec?mode=gi_t" target="_blank" class="w-12 h-12 bg-white/10 hover:bg-white/20 border border-white/10 rounded-xl flex items-center justify-center transition-all">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.488-.494-.67-.503-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                        </a>
                        <a href="https://t.me/may22sms" target="_blank" class="w-12 h-12 bg-white/10 hover:bg-white/20 border border-white/10 rounded-xl flex items-center justify-center transition-all">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M16.658 9.931l-3.376 16.34c-.535 1.625-2.276 1.496-3.003.604l-6.665-8.647-4.192-1.406c-1.657-.585-1.675-1.647.355-2.37l19.405-7.927c1.81-.724 3.298-.234 3.892 1.403.775 2.122 3.663 13.685 3.663 13.685.22 1.276-1.086 1.82-2.093 1.28l-8.872-5.883z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t border-white/10 pt-8 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-gray-500">&copy; {{ date('Y') }} {{ $siteName ?? 'May22SMS' }}. All rights reserved.</p>
                <p class="text-gray-600 text-sm">Built with ❤️ for speed, clarity, and repeat use.</p>
            </div>
        </div>
    </footer>
</div>
@endsection
