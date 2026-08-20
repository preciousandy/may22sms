@extends('layouts.admin')

@section('admin_content')
@push('header_title')
    Manage Logs & Account Store
@endpush

@if(session('success'))
    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-2xl shadow-sm flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="font-bold text-sm">{{ session('success') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl shadow-sm">
        <ul class="list-disc pl-5 font-bold text-sm space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Quick Navigation Bar for Log Category Videos -->
<div class="mb-6 flex flex-wrap items-center justify-between gap-4 bg-white p-4 sm:p-5 rounded-2xl border border-gray-100 shadow-sm">
    <div class="flex items-center gap-3.5">
        <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
        </div>
        <div>
            <h4 class="font-black text-sm text-gray-900">Log Category Video Guides & Links</h4>
            <p class="text-xs text-gray-500 font-medium">Upload videos or add tutorial links for Facebook, TikTok, Instagram, Twitter, and custom categories.</p>
        </div>
    </div>
    <a href="/admin/log-videos" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-400 to-yellow-500 hover:from-amber-500 hover:to-yellow-600 text-gray-950 font-black text-xs uppercase tracking-wider px-4 py-2.5 rounded-xl shadow-sm transition">
        Manage Category Videos
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
    </a>
</div>

<!-- Overview Metrics Bar -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Log Categories</div>
            <div class="text-2xl font-black text-gray-900 mt-1">{{ number_format($logItems->count()) }}</div>
            <div class="text-xs font-bold text-teal-600 mt-0.5">Active categories</div>
        </div>
        <div class="p-3 bg-teal-50 text-[#0d9488] rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Total Available Stock</div>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($logItems->sum('available_stock')) }}</div>
            <div class="text-xs font-bold text-emerald-700 mt-0.5">Accounts ready for purchase</div>
        </div>
        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Out of Stock Items</div>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ number_format($logItems->filter(fn($i) => $i->is_out_of_stock || $i->available_stock <= 0)->count()) }}</div>
            <div class="text-xs font-bold text-amber-700 mt-0.5">Requires inventory upload</div>
        </div>
        <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <div class="text-xs font-extrabold text-gray-500 uppercase tracking-wider">Hidden Items</div>
            <div class="text-2xl font-black text-gray-900 mt-1">{{ number_format($logItems->filter(fn($i) => !$i->is_active)->count()) }}</div>
            <div class="text-xs font-bold text-gray-500 mt-0.5">Disabled on store</div>
        </div>
        <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Create New Log Item Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden lg:col-span-1 flex flex-col justify-between">
        <div>
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80">
                <h3 class="text-base font-black text-gray-900">Create Log Category</h3>
            </div>
            <div class="p-6">
                <form action="/admin/logs" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Title</label>
                        <input type="text" name="title" required class="w-full text-xs font-semibold rounded-xl border border-gray-200 px-3 py-2.5 focus:border-[#0d9488] focus:ring-0" placeholder="e.g. Aged Facebook Account 2018">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Platform</label>
                        <select name="platform" class="w-full text-xs font-bold rounded-xl border border-gray-200 px-3 py-2.5 focus:border-[#0d9488] focus:ring-0">
                            <option value="facebook">Facebook 📘</option>
                            <option value="instagram">Instagram 📸</option>
                            <option value="twitter">Twitter / X 🐦</option>
                            <option value="youtube">YouTube ▶️</option>
                            <option value="tiktok">TikTok 🎵</option>
                            <option value="discord">Discord 👾</option>
                            <option value="other">Other 📦</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Price ($ USD)</label>
                        <input type="number" step="0.01" min="0.01" name="price" required class="w-full text-xs font-bold rounded-xl border border-gray-200 px-3 py-2.5 focus:border-[#0d9488] focus:ring-0" placeholder="5.00">
                    </div>
                    <div>
                        <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Description</label>
                        <textarea name="description" rows="3" class="w-full text-xs font-semibold rounded-xl border border-gray-200 p-3 focus:border-[#0d9488] focus:ring-0" placeholder="Include cookies? Email access? Date created?"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-[#0d9488] hover:bg-[#0f766e] text-white px-4 py-3 rounded-xl font-black text-xs uppercase tracking-wider transition shadow-md">
                        Create Listing Category
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-gray-100" x-data="{ openBulk: false }">
                    <button @click="openBulk = !openBulk" class="w-full text-left text-xs font-extrabold text-gray-900 uppercase tracking-wider flex items-center justify-between">
                        <span>Bulk Create Multiple Categories</span>
                        <span class="text-xs text-[#0d9488] font-bold" x-text="openBulk ? 'Hide ▲' : 'Show ▼'"></span>
                    </button>
                    <div x-show="openBulk" class="mt-4 bg-gray-50 p-4 rounded-xl border border-gray-200" style="display: none;">
                        <form action="/admin/logs/bulk" method="POST" class="space-y-3" enctype="multipart/form-data">
                            @csrf
                            <label class="block text-[11px] font-bold text-gray-600">Paste Format: title|platform|price|description</label>
                            <textarea id="bulkItems" name="bulk_items" rows="6" class="w-full rounded-xl border border-gray-200 p-2.5 text-xs font-mono" placeholder="Aged Facebook Account 2018|facebook|5.00|Includes cookies&#10;Aged Instagram 2019|instagram|4.50|Email included"></textarea>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Or upload CSV/TXT file</label>
                                <input type="file" name="bulk_file" accept=".txt,.csv,text/plain,text/csv" class="block w-full text-xs">
                            </div>
                            <button type="submit" class="w-full bg-gray-900 text-white px-4 py-2.5 rounded-xl font-extrabold text-xs hover:bg-gray-800 transition">Bulk Create</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Existing Log Items List -->
    <div class="lg:col-span-2 space-y-6">
        @foreach($logItems as $item)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/80 flex flex-wrap justify-between items-center gap-3">
                <div class="flex items-center gap-3">
                    <span class="text-2xl shrink-0">
                        @if($item->platform == 'facebook') 📘
                        @elseif($item->platform == 'instagram') 📸
                        @elseif($item->platform == 'twitter') 🐦
                        @elseif($item->platform == 'youtube') ▶️
                        @elseif($item->platform == 'tiktok') 🎵
                        @elseif($item->platform == 'discord') 👾
                        @else 📦 @endif
                    </span>
                    <div>
                        <h3 class="font-extrabold text-gray-900 text-base">{{ $item->title }}</h3>
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                            {{ $item->platform }} · <span class="text-[#0d9488] font-black">${{ number_format($item->price, 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3 text-right">
                    <div class="text-xs font-extrabold {{ $item->available_stock > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ number_format($item->available_stock) }} in stock{{ $item->is_out_of_stock ? ' (Out of Stock)' : '' }}
                    </div>
                    
                    <form action="/admin/logs/{{ $item->id }}/status" method="POST">
                        @csrf
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" onchange="this.form.submit()" class="sr-only peer" {{ $item->is_active ? 'checked' : '' }}>
                            <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#0d9488]"></div>
                        </label>
                    </form>
                </div>
            </div>
            
            <div class="p-6 bg-white" x-data="{ open: false, edit: false }">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <button @click="open = !open" class="inline-flex items-center gap-1.5 text-xs font-extrabold text-[#0d9488] hover:text-[#0f766e] bg-teal-50 px-3.5 py-2 rounded-xl transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Add Stock (Paste Credentials)
                    </button>
                    <button @click="edit = !edit" class="text-xs font-extrabold text-gray-700 hover:text-gray-900 bg-gray-100 px-3.5 py-2 rounded-xl transition">
                        <span x-text="edit ? 'Close Edit' : 'Edit Category'"></span>
                    </button>
                </div>
                
                <!-- Stock Upload Box -->
                <div x-show="open" class="mt-4 bg-gray-50 p-4 rounded-2xl border border-gray-200" style="display: none;">
                    <form action="/admin/logs/{{ $item->id }}/stock" method="POST" enctype="multipart/form-data">
                        @csrf
                        <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-2">Paste Credentials (One account per line)</label>
                        <textarea x-ref="creds" name="credentials" rows="4" class="w-full rounded-xl border border-gray-200 p-3 text-xs font-mono focus:border-[#0d9488] focus:ring-0" placeholder="username:password:cookie&#10;username2:password:cookie2"></textarea>
                        <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 items-center">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Or upload .txt file</label>
                                <input type="file" name="credentials_file" accept=".txt,text/plain" class="block w-full text-xs">
                            </div>
                            <div class="text-right">
                                <button type="submit" class="bg-gray-900 text-white px-5 py-2 rounded-xl text-xs font-extrabold hover:bg-gray-800 transition">Save Stock</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Edit Item Form -->
                <div x-show="edit" class="mt-4 bg-gray-50 p-4 rounded-2xl border border-gray-200" style="display: none;">
                    <form action="/admin/logs/{{ $item->id }}/update" method="POST" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Title</label>
                                <input type="text" name="title" value="{{ old('title', $item->title) }}" class="w-full text-xs font-semibold rounded-xl border border-gray-200 px-3 py-2 focus:border-[#0d9488] focus:ring-0" required>
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Platform</label>
                                <select name="platform" class="w-full text-xs font-bold rounded-xl border border-gray-200 px-3 py-2 focus:border-[#0d9488] focus:ring-0" required>
                                    @php($pf = old('platform', $item->platform))
                                    <option value="facebook" {{ $pf === 'facebook' ? 'selected' : '' }}>Facebook</option>
                                    <option value="instagram" {{ $pf === 'instagram' ? 'selected' : '' }}>Instagram</option>
                                    <option value="twitter" {{ $pf === 'twitter' ? 'selected' : '' }}>Twitter / X</option>
                                    <option value="youtube" {{ $pf === 'youtube' ? 'selected' : '' }}>YouTube</option>
                                    <option value="tiktok" {{ $pf === 'tiktok' ? 'selected' : '' }}>TikTok</option>
                                    <option value="discord" {{ $pf === 'discord' ? 'selected' : '' }}>Discord</option>
                                    <option value="other" {{ $pf === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Price ($)</label>
                                <input type="number" step="0.01" min="0.01" name="price" value="{{ old('price', (float) $item->price) }}" class="w-full text-xs font-bold rounded-xl border border-gray-200 px-3 py-2 focus:border-[#0d9488] focus:ring-0" required>
                            </div>
                            <div>
                                <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Description</label>
                                <input type="text" name="description" value="{{ old('description', $item->description) }}" class="w-full text-xs font-semibold rounded-xl border border-gray-200 px-3 py-2 focus:border-[#0d9488] focus:ring-0">
                            </div>
                        </div>
                        <div class="text-right pt-2">
                            <button type="submit" class="bg-gray-900 text-white px-5 py-2 rounded-xl text-xs font-extrabold hover:bg-gray-800 transition">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach

        @if($logItems->isEmpty())
            <div class="bg-white p-12 rounded-2xl shadow-sm text-center border border-gray-100">
                <p class="font-bold text-gray-500">No log categories created yet.</p>
            </div>
        @endif
    </div>
</div>
@endsection
