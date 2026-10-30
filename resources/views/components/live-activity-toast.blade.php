<div x-data="{
    activities: [],
    current: null,
    show: false,
    dismissed: false,
    index: 0,
    async init() {
        await this.fetchActivities();
        setInterval(() => this.fetchActivities(), 60000);
        setTimeout(() => this.cycle(), 2500);
    },
    async fetchActivities() {
        try {
            const res = await fetch('/api/live-activity');
            const data = await res.json();
            if (data && data.success && data.activities && data.activities.length > 0) {
                this.activities = data.activities;
            }
        } catch(e) {}
    },
    cycle() {
        if (this.dismissed || !this.activities || this.activities.length === 0) return;
        this.current = this.activities[this.index % this.activities.length];
        this.index++;
        this.show = true;

        setTimeout(() => {
            this.show = false;
            const nextDelay = Math.floor(Math.random() * 8000) + 12000;
            setTimeout(() => this.cycle(), nextDelay);
        }, 6000);
    },
    close() {
        this.show = false;
        this.dismissed = true;
    }
}" 
x-cloak
x-show="show && current"
x-transition:enter="transition ease-out duration-400 transform"
x-transition:enter-start="opacity-0 -translate-y-4 sm:translate-y-6 scale-95"
x-transition:enter-end="opacity-100 translate-y-0 scale-100"
x-transition:leave="transition ease-in duration-300 transform"
x-transition:leave-start="opacity-100 translate-y-0 scale-100"
x-transition:leave-end="opacity-0 -translate-y-4 sm:translate-y-4 scale-95"
class="fixed top-4 left-3 right-3 sm:top-auto sm:right-auto sm:left-6 sm:bottom-6 z-50 max-w-sm sm:w-80 mx-auto sm:mx-0">

    <div class="bg-white/95 backdrop-blur-md rounded-2xl p-4 shadow-2xl border border-gray-100 flex items-start gap-3.5 relative overflow-hidden group">
        <!-- Accent indicator bar -->
        <div class="absolute top-0 left-0 w-1.5 h-full bg-[#0d9488]"></div>

        <!-- Category Icon -->
        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0 border shadow-sm" :class="current ? current.bg_color : 'bg-teal-50 text-teal-600 border-teal-200'">
            <span x-text="current ? current.icon : '📱'"></span>
        </div>

        <!-- Content Details -->
        <div class="flex-1 min-w-0 pr-4">
            <div class="flex items-center gap-1.5">
                <span class="text-xs font-black text-gray-900 truncate" x-text="current ? current.title : ''"></span>
                <span class="text-[10px] text-teal-600 font-extrabold flex items-center gap-0.5">
                    <svg class="w-3 h-3 text-teal-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    Verified
                </span>
            </div>
            <div class="text-xs font-semibold text-gray-600 mt-0.5 truncate" x-text="current ? current.message : ''"></div>
            <div class="text-[10px] font-bold text-gray-400 mt-1 flex items-center gap-1">
                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span x-text="current ? current.time : ''"></span>
            </div>
        </div>

        <!-- Close Button -->
        <button type="button" @click="close()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition absolute top-2 right-2" title="Dismiss">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
</div>
