@extends('layouts.dashboard')

@section('page_title', 'Support Tickets & Help Center')

@section('dashboard_content')
@if(session('success'))
    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-2xl shadow-sm flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="font-bold text-sm">{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl shadow-sm flex items-center gap-3">
        <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="font-bold text-sm">{{ session('error') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl shadow-sm">
        <div class="font-bold text-sm mb-2 flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Please check the form errors:
        </div>
        <ul class="list-disc pl-8 text-sm font-medium space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Stats Header -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
    <div class="bg-gradient-to-br from-teal-900 to-[#0d9488] rounded-2xl p-6 text-white shadow-sm border border-teal-800/20">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-teal-200">Total Tickets</div>
                <div class="text-3xl font-extrabold mt-1">{{ $tickets->total() }}</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-white/10 backdrop-blur flex items-center justify-center">
                <svg class="w-6 h-6 text-teal-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-gray-500">Open Tickets</div>
                <div class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $openCount ?? 0 }}</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-xs font-bold uppercase tracking-wider text-gray-500">Resolved / Closed</div>
                <div class="text-3xl font-extrabold text-gray-700 mt-1">{{ $closedCount ?? 0 }}</div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Ticket Creation Form Side -->
    <div id="sell-logs" class="lg:col-span-1 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" x-data="{
        mode: @js(request()->query('sell_logs') ? 'sell_logs' : 'general'),
        platform: '',
        logType: '',
        qty: '',
        priceEach: '',
        delivery: 'paste_now',
        notes: '',
        sample: '',
        fileName: '',
        filePreview: null,
        fileIsImage: false,
        handleFileSelect(e) {
            const file = e.target.files[0];
            if(!file) {
                this.fileName = '';
                this.filePreview = null;
                this.fileIsImage = false;
                return;
            }
            this.fileName = file.name;
            if(file.type.startsWith('image/')) {
                this.fileIsImage = true;
                const reader = new FileReader();
                reader.onload = (evt) => {
                    this.filePreview = evt.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                this.fileIsImage = false;
                this.filePreview = null;
            }
        },
        removeFile(inputRef) {
            this.fileName = '';
            this.filePreview = null;
            this.fileIsImage = false;
            if(inputRef) inputRef.value = '';
        },
        get subject() {
            const p = (this.platform || 'unknown').trim();
            const t = (this.logType || 'logs').trim();
            const q = (this.qty || '').toString().trim();
            return `Sell Logs - ${p}${q ? ' - ' + q : ''} - ${t}`;
        },
        get message() {
            const lines = [];
            lines.push('SELL LOGS REQUEST');
            lines.push(`Platform: ${this.platform || '-'}`);
            lines.push(`Log type: ${this.logType || '-'}`);
            lines.push(`Quantity: ${this.qty || '-'}`);
            lines.push(`Expected price per account (USD): ${this.priceEach || '-'}`);
            lines.push(`Delivery: ${this.delivery === 'paste_now' ? 'Paste now' : 'Will send after admin replies'}`);
            lines.push('');
            if (this.notes) {
                lines.push('Notes:');
                lines.push(this.notes);
                lines.push('');
            }
            if (this.delivery === 'paste_now' && this.sample) {
                lines.push('Logs / sample format:');
                lines.push(this.sample);
                lines.push('');
            }
            lines.push('Please reply with acceptance, pricing, and where to send the full logs.');
            return lines.join('\n');
        }
    }">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-800">New Support Ticket</h3>
            <span class="text-xs font-bold text-[#0d9488] bg-teal-50 px-2.5 py-1 rounded-full border border-teal-100">24/7 Response</span>
        </div>
        <div class="p-6">
            <div class="flex items-center gap-2 mb-6 p-1 bg-gray-100 rounded-xl">
                <button type="button" @click="mode='general'" class="flex-1 py-2.5 rounded-lg text-xs font-extrabold transition text-center" :class="mode==='general' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'">General Support</button>
                <button type="button" @click="mode='sell_logs'" class="flex-1 py-2.5 rounded-lg text-xs font-extrabold transition text-center" :class="mode==='sell_logs' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'">Sell Logs</button>
            </div>

            <!-- General Support Form -->
            <form action="/tickets" method="POST" enctype="multipart/form-data" class="space-y-4" x-show="mode==='general'">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Category</label>
                    <select name="category" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-sm text-gray-900 transition">
                        <option value="virtual_numbers">📱 Virtual Numbers / SMS</option>
                        <option value="sms_delivery">📩 OTP / Delivery Issue</option>
                        <option value="billing">💳 Billing & Wallet Top Up</option>
                        <option value="account">🔒 Account & Security</option>
                        <option value="general" selected>💬 General Inquiry</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Priority</label>
                    <select name="priority" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-sm text-gray-900 transition">
                        <option value="normal" selected>🟢 Normal</option>
                        <option value="high">🟡 High Priority</option>
                        <option value="urgent">🔴 Urgent</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Subject</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-sm text-gray-900 transition" placeholder="Brief summary of your issue" required>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Message</label>
                    <textarea name="message" rows="5" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-sm text-gray-900 transition" placeholder="Provide full details, order IDs, or descriptions..." required>{{ old('message') }}</textarea>
                </div>

                <!-- Attachment Box -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Attach Image or File (Optional)</label>
                    <div class="relative border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-[#0d9488] transition bg-gray-50/50">
                        <input type="file" name="attachment" x-ref="fileInput1" @change="handleFileSelect($event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*,.pdf,.doc,.docx,.zip,.txt">
                        
                        <template x-if="!fileName">
                            <div class="flex flex-col items-center justify-center gap-1 py-2">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span class="text-xs font-bold text-gray-700">Click or drag image/screenshot here</span>
                                <span class="text-[11px] text-gray-400">PNG, JPG, WEBP, PDF, DOC (Max 10MB)</span>
                            </div>
                        </template>

                        <template x-if="fileName">
                            <div class="flex items-center justify-between gap-3 bg-white p-2.5 rounded-lg border border-teal-200 z-20 relative">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <template x-if="fileIsImage && filePreview">
                                        <img :src="filePreview" class="w-10 h-10 object-cover rounded-lg border border-gray-200 flex-shrink-0">
                                    </template>
                                    <template x-if="!fileIsImage">
                                        <div class="w-10 h-10 rounded-lg bg-teal-50 text-[#0d9488] flex items-center justify-center font-bold text-xs flex-shrink-0">FILE</div>
                                    </template>
                                    <span class="text-xs font-bold text-gray-800 truncate" x-text="fileName"></span>
                                </div>
                                <button type="button" @click.stop="removeFile($refs.fileInput1)" class="p-1 rounded-full hover:bg-rose-50 text-rose-500 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#0d9488] text-white px-6 py-3.5 rounded-xl font-bold shadow-md hover:bg-teal-700 transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    <span>Submit Ticket</span>
                </button>
            </form>

            <!-- Sell Logs Form -->
            <form action="/tickets" method="POST" enctype="multipart/form-data" class="space-y-4" x-show="mode==='sell_logs'" style="display:none;">
                @csrf
                <input type="hidden" name="subject" :value="subject">
                <input type="hidden" name="message" :value="message">
                <input type="hidden" name="category" value="sell_logs">
                <input type="hidden" name="priority" value="high">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Platform</label>
                    <select x-model="platform" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-sm text-gray-900 transition" required>
                        <option value="">Select platform</option>
                        <option value="facebook">Facebook</option>
                        <option value="instagram">Instagram</option>
                        <option value="twitter">Twitter / X</option>
                        <option value="youtube">YouTube</option>
                        <option value="tiktok">TikTok</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Type of logs</label>
                    <input type="text" x-model="logType" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-sm text-gray-900 transition" placeholder="e.g. aged accounts, cookies, tokens" required>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Quantity</label>
                        <input type="number" min="1" x-model="qty" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-sm text-gray-900 transition" placeholder="e.g. 50" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Price each (USD)</label>
                        <input type="number" min="0" step="0.01" x-model="priceEach" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-sm text-gray-900 transition" placeholder="e.g. 0.50" required>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Delivery Method</label>
                    <select x-model="delivery" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-sm text-gray-900 transition">
                        <option value="paste_now">Paste now (in this form)</option>
                        <option value="send_later">Send after admin replies</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Extra notes (optional)</label>
                    <textarea x-model="notes" rows="3" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-sm text-gray-900 transition" placeholder="Country, quality, guarantees, login format..."></textarea>
                </div>

                <div x-show="delivery==='paste_now'" style="display:none;">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Logs / sample format</label>
                    <textarea x-model="sample" rows="5" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-bold text-sm text-gray-900 transition" placeholder="Paste logs here (one per line)"></textarea>
                </div>

                <!-- Attachment Box for Sell Logs -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 mb-2">Attach Sample File / Proof (Optional)</label>
                    <div class="relative border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-[#0d9488] transition bg-gray-50/50">
                        <input type="file" name="attachment" x-ref="fileInput2" @change="handleFileSelect($event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*,.pdf,.doc,.docx,.zip,.txt">
                        
                        <template x-if="!fileName">
                            <div class="flex flex-col items-center justify-center gap-1 py-2">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span class="text-xs font-bold text-gray-700">Attach sample file or proof screenshot</span>
                                <span class="text-[11px] text-gray-400">ZIP, TXT, PNG, JPG (Max 10MB)</span>
                            </div>
                        </template>

                        <template x-if="fileName">
                            <div class="flex items-center justify-between gap-3 bg-white p-2.5 rounded-lg border border-teal-200 z-20 relative">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <template x-if="fileIsImage && filePreview">
                                        <img :src="filePreview" class="w-10 h-10 object-cover rounded-lg border border-gray-200 flex-shrink-0">
                                    </template>
                                    <template x-if="!fileIsImage">
                                        <div class="w-10 h-10 rounded-lg bg-teal-50 text-[#0d9488] flex items-center justify-center font-bold text-xs flex-shrink-0">FILE</div>
                                    </template>
                                    <span class="text-xs font-bold text-gray-800 truncate" x-text="fileName"></span>
                                </div>
                                <button type="button" @click.stop="removeFile($refs.fileInput2)" class="p-1 rounded-full hover:bg-rose-50 text-rose-500 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#0d9488] text-white px-6 py-3.5 rounded-xl font-bold shadow-md hover:bg-teal-700 transition flex items-center justify-center gap-2" :disabled="!platform || !logType || !qty || !priceEach">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    <span>Send Sell Request to Admin</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Ticket List Side -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col justify-between">
        <div>
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">My Support Tickets</h3>
                <span class="text-xs font-bold text-gray-500">{{ $tickets->total() }} Total</span>
            </div>

            @if($tickets->count() === 0)
                <div class="p-16 text-center">
                    <div class="w-16 h-16 rounded-full bg-teal-50 text-[#0d9488] flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <h4 class="text-base font-bold text-gray-900 mb-1">No Tickets Created Yet</h4>
                    <p class="text-xs text-gray-500 max-w-sm mx-auto">Need assistance with your numbers, orders, or account? Fill out the form on the left to reach support.</p>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($tickets as $t)
                        <a href="/tickets/{{ $t->id }}" class="block p-5 hover:bg-teal-50/40 transition">
                            <div class="flex items-start justify-between gap-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs font-extrabold text-teal-800 bg-teal-50 px-2.5 py-0.5 rounded-md border border-teal-100">#{{ $t->id }}</span>
                                        <span class="font-bold text-gray-900 text-sm hover:text-[#0d9488] transition">{{ $t->subject }}</span>
                                    </div>

                                    <div class="flex items-center gap-3 text-xs text-gray-500 flex-wrap pt-1">
                                        @if($t->category)
                                            <span class="bg-gray-100 text-gray-700 font-bold px-2 py-0.5 rounded text-[11px] capitalize">{{ str_replace('_', ' ', $t->category) }}</span>
                                        @endif

                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Updated {{ ($t->last_message_at ?? $t->updated_at)->diffForHumans() }}
                                        </span>

                                        <span class="flex items-center gap-1 text-gray-600 font-medium">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                            {{ $t->messages_count ?? 1 }} {{ Str::plural('msg', $t->messages_count ?? 1) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="text-xs font-extrabold px-3 py-1 rounded-full border {{ $t->status === 'open' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-700 border-gray-200' }}">
                                        {{ strtoupper($t->status) }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        @if($tickets->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $tickets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
