@auth
<div x-data="reviewModal" class="relative z-40">

    <!-- Floating Rate Us Badge Button -->
    <button type="button" 
            @click="openModal()" 
            class="fixed bottom-24 left-6 z-40 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-white font-extrabold text-xs px-4 py-2.5 rounded-full shadow-xl border border-amber-300/40 flex items-center gap-2 transition-all duration-300 hover:scale-105 group cursor-pointer">
        <span class="text-sm">⭐</span>
        <span class="hidden sm:inline">Rate May22SMS</span>
        <span class="sm:hidden">Rate Us</span>
    </button>

    <!-- Review Modal Backdrop -->
    <div id="review-modal-backdrop"
         x-show="open" 
         x-cloak
         @keydown.escape.window="open = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-md"
         @click.self="open = false">

        <!-- Dialog Card -->
        <div class="relative max-w-md w-full bg-white text-gray-900 rounded-3xl p-6 sm:p-8 shadow-2xl border border-gray-200 overflow-hidden z-50">

            <!-- Close Button -->
            <button type="button" 
                    @click="open = false" 
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition z-20 cursor-pointer"
                    aria-label="Close review modal">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <!-- Main Rating Form Body -->
            <form @submit.prevent="submitReview()">
                <!-- Header -->
                <div class="text-center space-y-1.5">
                    <div class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-black uppercase tracking-wider border border-amber-200">
                        ⭐ Rate Your Experience
                    </div>
                    <h3 class="text-xl font-black text-gray-900 pt-2">How's your experience with May22SMS?</h3>
                    <p class="text-xs text-gray-500">Your rating helps us improve our services for you!</p>
                </div>

                <!-- Interactive Star Rating Bar & Selected Rating Indicator -->
                <div class="my-5 flex flex-col items-center justify-center gap-2">
                    <div class="flex items-center justify-center gap-1.5">
                        <!-- 1 Star -->
                        <button type="button" 
                                @click="rating = 1" 
                                @mouseenter="hoverRating = 1" 
                                @mouseleave="hoverRating = 0"
                                class="p-1 text-3xl focus:outline-none transition-transform duration-150 transform hover:scale-125 cursor-pointer"
                                title="1 Star">
                            <svg class="w-10 h-10 transition duration-200" 
                                 :class="(hoverRating ? hoverRating >= 1 : rating >= 1) ? 'text-amber-400 fill-amber-400 drop-shadow-[0_0_10px_rgba(251,191,36,0.8)] scale-110' : 'text-gray-300 fill-gray-200'" 
                                 viewBox="0 0 24 24">
                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                            </svg>
                        </button>

                        <!-- 2 Stars -->
                        <button type="button" 
                                @click="rating = 2" 
                                @mouseenter="hoverRating = 2" 
                                @mouseleave="hoverRating = 0"
                                class="p-1 text-3xl focus:outline-none transition-transform duration-150 transform hover:scale-125 cursor-pointer"
                                title="2 Stars">
                            <svg class="w-10 h-10 transition duration-200" 
                                 :class="(hoverRating ? hoverRating >= 2 : rating >= 2) ? 'text-amber-400 fill-amber-400 drop-shadow-[0_0_10px_rgba(251,191,36,0.8)] scale-110' : 'text-gray-300 fill-gray-200'" 
                                 viewBox="0 0 24 24">
                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                            </svg>
                        </button>

                        <!-- 3 Stars -->
                        <button type="button" 
                                @click="rating = 3" 
                                @mouseenter="hoverRating = 3" 
                                @mouseleave="hoverRating = 0"
                                class="p-1 text-3xl focus:outline-none transition-transform duration-150 transform hover:scale-125 cursor-pointer"
                                title="3 Stars">
                            <svg class="w-10 h-10 transition duration-200" 
                                 :class="(hoverRating ? hoverRating >= 3 : rating >= 3) ? 'text-amber-400 fill-amber-400 drop-shadow-[0_0_10px_rgba(251,191,36,0.8)] scale-110' : 'text-gray-300 fill-gray-200'" 
                                 viewBox="0 0 24 24">
                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                            </svg>
                        </button>

                        <!-- 4 Stars -->
                        <button type="button" 
                                @click="rating = 4" 
                                @mouseenter="hoverRating = 4" 
                                @mouseleave="hoverRating = 0"
                                class="p-1 text-3xl focus:outline-none transition-transform duration-150 transform hover:scale-125 cursor-pointer"
                                title="4 Stars">
                            <svg class="w-10 h-10 transition duration-200" 
                                 :class="(hoverRating ? hoverRating >= 4 : rating >= 4) ? 'text-amber-400 fill-amber-400 drop-shadow-[0_0_10px_rgba(251,191,36,0.8)] scale-110' : 'text-gray-300 fill-gray-200'" 
                                 viewBox="0 0 24 24">
                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                            </svg>
                        </button>

                        <!-- 5 Stars -->
                        <button type="button" 
                                @click="rating = 5" 
                                @mouseenter="hoverRating = 5" 
                                @mouseleave="hoverRating = 0"
                                class="p-1 text-3xl focus:outline-none transition-transform duration-150 transform hover:scale-125 cursor-pointer"
                                title="5 Stars">
                            <svg class="w-10 h-10 transition duration-200" 
                                 :class="(hoverRating ? hoverRating >= 5 : rating >= 5) ? 'text-amber-400 fill-amber-400 drop-shadow-[0_0_10px_rgba(251,191,36,0.8)] scale-110' : 'text-gray-300 fill-gray-200'" 
                                 viewBox="0 0 24 24">
                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Selected Rating Label Indicator -->
                    <div class="text-xs font-black text-amber-800 bg-amber-100/90 px-4 py-1.5 rounded-full border border-amber-300 shadow-sm transition-all duration-200 mt-1 flex items-center gap-1.5">
                        <span class="text-amber-500">⭐</span>
                        <span x-text="(hoverRating || rating) + ' / 5 Stars'"></span>
                        <span class="font-bold text-gray-700" x-text="
                            (hoverRating || rating) === 5 ? '— Excellent! 🚀' :
                            (hoverRating || rating) === 4 ? '— Very Good! 👍' :
                            (hoverRating || rating) === 3 ? '— Good 🙂' :
                            (hoverRating || rating) === 2 ? '— Fair 😐' : '— Needs Improvement 🙁'
                        "></span>
                    </div>
                </div>

                <!-- Service Category Selection -->
                <div class="mb-4">
                    <label class="block text-[11px] font-extrabold text-gray-500 uppercase tracking-wider mb-1">Select Service Rated</label>
                    <select x-model="serviceType" class="w-full text-xs font-extrabold rounded-xl border border-gray-300 bg-white text-gray-900 px-3.5 py-2.5 focus:border-[#0d9488] focus:ring-0">
                        <option value="sms">📱 Virtual SMS Numbers</option>
                        <option value="funding">💳 Wallet Funding</option>
                        <option value="logs">🛒 Store Account Logs</option>
                        <option value="boosting">🚀 Social Media Boosting</option>
                        <option value="general">💬 General Service & Platform</option>
                    </select>
                </div>

                <!-- Review Text Input -->
                <div class="mb-5">
                    <label class="block text-[11px] font-extrabold text-gray-500 uppercase tracking-wider mb-1">Your Review / Comments (Optional)</label>
                    <textarea x-model="reviewText" rows="3" class="w-full text-xs font-semibold rounded-2xl border border-gray-300 bg-white text-gray-900 p-3.5 focus:border-[#0d9488] focus:ring-0" placeholder="Tell us what you loved or how we can serve you better..."></textarea>
                </div>

                <!-- Error Alert -->
                <div x-show="errorMsg" class="mb-4 text-xs font-bold text-rose-600 bg-rose-50 p-3 rounded-xl border border-rose-200 text-center" x-text="errorMsg"></div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-3">
                    <button type="button" 
                            @click="open = false" 
                            class="w-1/3 py-3 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-100 transition cursor-pointer">
                        Maybe Later
                    </button>
                    <button type="submit" 
                            :disabled="submitting" 
                            class="w-2/3 py-3 rounded-xl text-xs font-black text-white bg-gradient-to-r from-[#0d9488] to-teal-600 hover:from-teal-600 hover:to-teal-700 transition shadow-lg flex items-center justify-center gap-2 cursor-pointer">
                        <span x-show="submitting" class="inline-block">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </span>
                        <span>Submit Review</span>
                    </button>
                </div>
            </form>

            <!-- Thank You Confirmation Screen -->
            <div x-show="submitted" class="mt-4 text-center py-6 space-y-3 bg-emerald-50 rounded-2xl p-4 border border-emerald-100">
                <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-2xl shadow-inner animate-bounce">
                    🎉
                </div>
                <h3 class="text-lg font-black text-gray-900">Thank You!</h3>
                <p class="text-xs font-semibold text-gray-600">Your feedback has been submitted successfully. We appreciate your support!</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        if (!Alpine.store('reviewModalData')) {
            Alpine.data('reviewModal', () => ({
                open: false,
                rating: 5,
                hoverRating: 0,
                serviceType: 'general',
                reviewText: '',
                submitting: false,
                submitted: false,
                errorMsg: null,

                init() {
                    const path = window.location.pathname;
                    if (path.includes('/sms')) {
                        this.serviceType = 'sms';
                    } else if (path.includes('/funding')) {
                        this.serviceType = 'funding';
                    } else if (path.includes('/logs')) {
                        this.serviceType = 'logs';
                    } else if (path.includes('/boosting')) {
                        this.serviceType = 'boosting';
                    }
                },

                openModal() {
                    this.open = true;
                    this.submitted = false;
                    this.errorMsg = null;
                },

                async submitReview() {
                    if (this.submitting) return;
                    this.submitting = true;
                    this.errorMsg = null;

                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        const payload = {
                            rating: parseInt(this.rating, 10) || 5,
                            service_type: this.serviceType || 'general',
                            review_text: this.reviewText || ''
                        };

                        const res = await fetch('/api/reviews', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await res.json();
                        if (res.ok && data && data.success) {
                            this.submitted = true;
                            setTimeout(() => {
                                this.open = false;
                                this.submitted = false;
                                this.reviewText = '';
                            }, 2500);
                        } else {
                            this.errorMsg = (data && data.message) ? data.message : 'Error submitting review. Please try again.';
                        }
                    } catch (e) {
                        console.error('Submit review error:', e);
                        this.errorMsg = 'An error occurred. Please try again.';
                    } finally {
                        this.submitting = false;
                    }
                }
            }));
        }
    });
</script>
@endauth
