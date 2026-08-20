@extends('layouts.admin')

@section('admin_content')
@push('header_title')
    Manage Log Category Video Guides & Links
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

<!-- Header Action & Navigation Bar -->
<div class="mb-6 flex flex-wrap items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-100 shadow-sm">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-400 to-yellow-500 flex items-center justify-center text-gray-950 font-black text-xl shadow-md">
            🎬
        </div>
        <div>
            <h3 class="text-lg font-black text-gray-900">Category Video Guides & Tutorials</h3>
            <p class="text-xs font-semibold text-gray-500">Upload video files or provide video links (YouTube, Vimeo, MP4) for Facebook, TikTok, Instagram, Twitter, YouTube & Other log categories.</p>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <a href="/admin/logs" class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 font-extrabold text-xs px-4 py-2.5 rounded-xl transition">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Logs Store
        </a>
    </div>
</div>

<div x-data="{ activeTab: 'all', openCreateModal: false, openEditModal: false, editItem: null }" class="space-y-6">

    <!-- Filter & Add Bar -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <!-- Category Filter Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-2 sm:pb-0 scrollbar-none">
            <button @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-gray-900 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'" class="px-4 py-2 rounded-xl text-xs font-extrabold transition whitespace-nowrap">
                All Categories ({{ $videos->count() }})
            </button>
            @foreach(['facebook', 'tiktok', 'instagram', 'twitter', 'youtube', 'discord', 'other'] as $p)
                @php $pCount = $videos->where('platform', $p)->count(); @endphp
                <button @click="activeTab = '{{ $p }}'" :class="activeTab === '{{ $p }}' ? 'bg-[#0d9488] text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200'" class="px-3.5 py-2 rounded-xl text-xs font-extrabold capitalize transition whitespace-nowrap flex items-center gap-1.5">
                    <span>
                        @if($p == 'facebook') 📘
                        @elseif($p == 'tiktok') 🎵
                        @elseif($p == 'instagram') 📸
                        @elseif($p == 'twitter') 🐦
                        @elseif($p == 'youtube') ▶️
                        @elseif($p == 'discord') 💬
                        @else 📦 @endif
                    </span>
                    {{ $p }}
                    @if($pCount > 0)
                        <span class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] font-black bg-white/20 text-current">{{ $pCount }}</span>
                    @endif
                </button>
            @endforeach
        </div>

        <button @click="openCreateModal = true" class="inline-flex items-center gap-2 bg-[#0d9488] hover:bg-[#0f766e] text-white font-extrabold text-xs uppercase tracking-wider px-5 py-3 rounded-xl shadow-md transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add New Video / Link
        </button>
    </div>

    <!-- Video Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($videos as $video)
            <div x-show="activeTab === 'all' || activeTab === '{{ strtolower($video->platform) }}'" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col hover:shadow-md transition">
                <!-- Video Header/Badge -->
                <div class="p-5 pb-3 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-gray-50 to-white">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-black uppercase tracking-wider bg-gray-900 text-amber-400">
                        @if($video->platform == 'facebook') 📘
                        @elseif($video->platform == 'tiktok') 🎵
                        @elseif($video->platform == 'instagram') 📸
                        @elseif($video->platform == 'twitter') 🐦
                        @elseif($video->platform == 'youtube') ▶️
                        @elseif($video->platform == 'discord') 💬
                        @else 📦 @endif
                        {{ $video->platform }}
                    </span>

                    <form action="/admin/log-videos/{{ $video->id }}/toggle" method="POST">
                        @csrf
                        <button type="submit" title="Click to toggle enable/disable" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black transition {{ $video->is_active ? 'bg-emerald-100 text-emerald-800 border border-emerald-300 hover:bg-emerald-200' : 'bg-rose-100 text-rose-800 border border-rose-300 hover:bg-rose-200' }}">
                            <span class="w-2 h-2 rounded-full {{ $video->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                            {{ $video->is_active ? 'Enabled' : 'Disabled' }}
                        </button>
                    </form>
                </div>

                <!-- Content -->
                <div class="p-5 flex-1 space-y-3">
                    <h4 class="font-black text-gray-900 text-base leading-snug">{{ $video->title }}</h4>

                    <!-- Video Preview Container -->
                    <div class="rounded-xl overflow-hidden bg-black/90 aspect-video relative flex items-center justify-center border border-gray-200 shadow-inner group">
                        @if($video->is_uploaded_file)
                            <video src="{{ $video->playable_url }}" controls class="w-full h-full object-cover"></video>
                        @elseif($video->is_embeddable)
                            <iframe src="{{ $video->playable_url }}" class="w-full h-full border-0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        @elseif($video->video_url)
                            <div class="p-4 text-center">
                                <svg class="w-10 h-10 text-amber-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                <p class="text-xs text-gray-300 font-bold break-all line-clamp-2 mb-2">{{ $video->video_url }}</p>
                                <a href="{{ $video->video_url }}" target="_blank" class="inline-flex items-center gap-1 bg-amber-400 hover:bg-yellow-400 text-black text-xs font-black px-3 py-1.5 rounded-lg transition">
                                    Open External Link ↗
                                </a>
                            </div>
                        @else
                            <span class="text-xs text-gray-400 font-bold">No video output attached</span>
                        @endif
                    </div>

                    <div class="text-[11px] font-bold text-gray-500 flex items-center justify-between pt-1">
                        <span>Source: {{ $video->is_uploaded_file ? 'Direct File Upload' : 'External Link' }}</span>
                        <span>Added: {{ $video->created_at->format('M d, Y') }}</span>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="px-5 py-3.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between gap-2">
                    <button 
                        @click="
                            editItem = {
                                id: {{ $video->id }},
                                platform: @js($video->platform),
                                title: @js($video->title),
                                video_url: @js($video->video_url ?? ''),
                            };
                            openEditModal = true;
                        " 
                        class="inline-flex items-center gap-1.5 text-xs font-black text-teal-700 hover:text-teal-900 bg-teal-50 hover:bg-teal-100 border border-teal-200 px-3.5 py-1.5 rounded-xl transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit
                    </button>

                    <form action="/admin/log-videos/{{ $video->id }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this video guide?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-black text-rose-700 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 border border-rose-200 px-3.5 py-1.5 rounded-xl transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white p-12 rounded-3xl shadow-sm border border-gray-100 text-center">
                <div class="text-5xl mb-3">🎬</div>
                <h4 class="text-lg font-black text-gray-900 mb-1">No Video Guides Added Yet</h4>
                <p class="text-xs text-gray-500 font-medium mb-4">Add video tutorials or links for Facebook, TikTok, Instagram & other log categories to help users.</p>
                <button @click="openCreateModal = true" class="inline-flex items-center gap-2 bg-[#0d9488] hover:bg-[#0f766e] text-white font-extrabold text-xs uppercase tracking-wider px-5 py-2.5 rounded-xl shadow-md transition">
                    Add First Video Guide
                </button>
            </div>
        @endforelse
    </div>

    <!-- Create Modal -->
    <div x-show="openCreateModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;" @keydown.escape.window="openCreateModal = false">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl relative border border-gray-100" @click.away="openCreateModal = false">
            <div class="flex items-center justify-between mb-6 border-b border-gray-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-black">
                        🎬
                    </div>
                    <div>
                        <h3 class="text-base font-black text-gray-900">Add Category Video / Link</h3>
                        <p class="text-xs text-gray-500 font-semibold">Attach video guide to a log category</p>
                    </div>
                </div>
                <button @click="openCreateModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="/admin/log-videos" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Log Category / Platform *</label>
                    <select name="platform" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-[#0d9488] focus:ring-[#0d9488] text-sm font-bold capitalize" required>
                        @foreach(['facebook', 'tiktok', 'instagram', 'twitter', 'youtube', 'discord', 'other'] as $plat)
                            <option value="{{ $plat }}">{{ ucfirst($plat) }} Logs</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Video Title *</label>
                    <input type="text" name="title" placeholder="e.g. How to login & import 2FA cookies on Facebook" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-[#0d9488] focus:ring-[#0d9488] text-sm font-semibold" required>
                </div>

                <div x-data="{ mode: 'url' }" class="space-y-3 pt-2 border-t border-gray-100">
                    <div class="flex items-center gap-4 text-xs font-black">
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="video_source_type" value="url" x-model="mode" class="text-[#0d9488] focus:ring-[#0d9488]">
                            <span>Video Link (YouTube, Vimeo, MP4 URL)</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="radio" name="video_source_type" value="file" x-model="mode" class="text-[#0d9488] focus:ring-[#0d9488]">
                            <span>Direct Video File Upload</span>
                        </label>
                    </div>

                    <div x-show="mode === 'url'">
                        <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Video URL Link</label>
                        <input type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=... or https://vimeo.com/..." class="w-full rounded-xl border-gray-300 shadow-sm focus:border-[#0d9488] focus:ring-[#0d9488] text-sm font-semibold">
                        <p class="text-[11px] text-gray-500 mt-1 font-medium">YouTube & Vimeo links will automatically display as interactive embedded players.</p>
                    </div>

                    <div x-show="mode === 'file'">
                        <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Upload Video File (.mp4, .webm, .mov - Max 50MB)</label>
                        <input type="file" name="video_file" accept="video/mp4,video/webm,video/ogg,video/quicktime" class="w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-teal-50 file:text-[#0d9488] hover:file:bg-teal-100">
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                    <button type="button" @click="openCreateModal = false" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-extrabold text-xs hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#0d9488] hover:bg-[#0f766e] text-white font-black text-xs uppercase tracking-wider shadow-md transition">
                        Save Video Guide
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="openEditModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;" @keydown.escape.window="openEditModal = false">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl relative border border-gray-100" @click.away="openEditModal = false">
            <div class="flex items-center justify-between mb-6 border-b border-gray-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-800 flex items-center justify-center font-black">
                        ✏️
                    </div>
                    <div>
                        <h3 class="text-base font-black text-gray-900">Edit Category Video / Link</h3>
                        <p class="text-xs text-gray-500 font-semibold">Update details or change video source</p>
                    </div>
                </div>
                <button @click="openEditModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <template x-if="editItem">
                <form :action="'/admin/log-videos/' + editItem.id + '/update'" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Log Category / Platform *</label>
                        <select name="platform" x-model="editItem.platform" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-[#0d9488] focus:ring-[#0d9488] text-sm font-bold capitalize" required>
                            @foreach(['facebook', 'tiktok', 'instagram', 'twitter', 'youtube', 'discord', 'other'] as $plat)
                                <option value="{{ $plat }}">{{ ucfirst($plat) }} Logs</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Video Title *</label>
                        <input type="text" name="title" x-model="editItem.title" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-[#0d9488] focus:ring-[#0d9488] text-sm font-semibold" required>
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Video URL Link</label>
                        <input type="url" name="video_url" x-model="editItem.video_url" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-[#0d9488] focus:ring-[#0d9488] text-sm font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold text-gray-700 uppercase tracking-wider mb-1">Replace Uploaded Video File (Optional)</label>
                        <input type="file" name="video_file" accept="video/mp4,video/webm,video/ogg,video/quicktime" class="w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-teal-50 file:text-[#0d9488] hover:file:bg-teal-100">
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                        <button type="button" @click="openEditModal = false" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-extrabold text-xs hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#0d9488] hover:bg-[#0f766e] text-white font-black text-xs uppercase tracking-wider shadow-md transition">
                            Update Changes
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>

@endsection
