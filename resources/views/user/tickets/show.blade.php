@extends('layouts.dashboard')

@section('page_title', 'Ticket #' . $ticket->id . ' - ' . $ticket->subject)

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
            Please check the errors:
        </div>
        <ul class="list-disc pl-8 text-sm font-medium space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="max-w-4xl mx-auto space-y-6" x-data="{
    activeModalImg: null,
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
    }
}">

    <!-- Back button & Ticket Header Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50 flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-3">
                <a href="/tickets" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-extrabold text-teal-800 bg-teal-50 px-2 py-0.5 rounded border border-teal-100">Ticket #{{ $ticket->id }}</span>
                        @if($ticket->category)
                            <span class="text-[11px] font-extrabold text-gray-600 bg-gray-200 px-2 py-0.5 rounded capitalize">{{ str_replace('_', ' ', $ticket->category) }}</span>
                        @endif
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mt-1">{{ $ticket->subject }}</h3>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-xs font-extrabold px-3 py-1.5 rounded-full border {{ $ticket->status === 'open' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-700 border-gray-200' }}">
                    {{ strtoupper($ticket->status) }}
                </span>

                @if($ticket->status === 'open')
                    <form action="/tickets/{{ $ticket->id }}/close" method="POST">
                        @csrf
                        <button type="submit" class="bg-gray-900 text-white text-xs font-bold px-4 py-2 rounded-xl hover:bg-gray-800 transition shadow-sm">
                            Close Ticket
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Chat Messages Container -->
        <div class="p-6 space-y-6 bg-gray-50/50 min-h-[320px] max-h-[600px] overflow-y-auto">
            @foreach($ticket->messages as $m)
                <div class="flex flex-col {{ $m->is_admin ? 'items-start' : 'items-end' }}">
                    <div class="flex items-center gap-2 mb-1 px-1">
                        <span class="text-[11px] font-extrabold {{ $m->is_admin ? 'text-teal-700' : 'text-gray-500' }}">
                            {{ $m->is_admin ? 'May22SMS Support' : 'You' }}
                        </span>
                        <span class="text-[11px] text-gray-400">• {{ $m->created_at->format('M d, Y H:i') }}</span>
                    </div>

                    <div class="max-w-[85%] sm:max-w-[75%] rounded-2xl p-4 shadow-sm {{ $m->is_admin ? 'bg-white border border-gray-200 text-gray-900 rounded-tl-sm' : 'bg-gradient-to-r from-[#0d9488] to-teal-700 text-white rounded-tr-sm' }}">
                        @if($m->message)
                            <div class="text-sm leading-relaxed whitespace-pre-wrap font-medium">{{ $m->message }}</div>
                        @endif

                        <!-- Attachment Render -->
                        @if($m->attachment_path)
                            <div class="mt-3 pt-3 border-t {{ $m->is_admin ? 'border-gray-100' : 'border-teal-600/40' }}">
                                @if($m->isImage())
                                    <div class="relative group cursor-pointer overflow-hidden rounded-xl border {{ $m->is_admin ? 'border-gray-200 bg-gray-50' : 'border-teal-600 bg-teal-800/30' }}" @click="activeModalImg = '{{ $m->attachment_url }}'">
                                        <img src="{{ $m->attachment_url }}" alt="{{ $m->attachment_name }}" class="max-h-60 w-full object-cover group-hover:scale-105 transition duration-300">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg>
                                            Click to Enlarge
                                        </div>
                                    </div>
                                    <div class="mt-1 text-[11px] opacity-80 truncate">{{ $m->attachment_name }}</div>
                                @else
                                    <a href="{{ $m->attachment_url }}" target="_blank" download="{{ $m->attachment_name }}" class="inline-flex items-center gap-3 p-3 rounded-xl border text-xs font-bold transition {{ $m->is_admin ? 'bg-gray-50 border-gray-200 text-gray-800 hover:bg-gray-100' : 'bg-teal-800/40 border-teal-500 text-white hover:bg-teal-800/60' }}">
                                        <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </div>
                                        <div class="overflow-hidden">
                                            <div class="truncate">{{ $m->attachment_name }}</div>
                                            <div class="text-[10px] opacity-75 font-normal">Click to Download File</div>
                                        </div>
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Reply Form Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h4 class="text-sm font-extrabold text-gray-800 uppercase tracking-wider">Send Reply</h4>
            <span class="text-xs text-gray-500 font-medium">Attach screenshots or proof if needed</span>
        </div>

        <div class="p-6">
            @if($ticket->status !== 'open')
                <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl text-center text-sm font-bold text-gray-500">
                    This ticket is closed. Reopen or create a new ticket if you need further help.
                </div>
            @else
                <form action="/tickets/{{ $ticket->id }}/reply" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <textarea name="message" rows="4" class="w-full rounded-xl border-2 border-gray-200 px-4 py-3 focus:ring-0 focus:border-[#0d9488] font-medium text-sm text-gray-900 transition" placeholder="Type your response here..."></textarea>
                    </div>

                    <!-- File Attachment Input -->
                    <div class="flex items-center justify-between gap-4 flex-wrap">
                        <div class="relative flex-1 min-w-[240px]">
                            <input type="file" name="attachment" x-ref="replyFileInput" @change="handleFileSelect($event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*,.pdf,.doc,.docx,.zip,.txt">
                            
                            <div class="flex items-center gap-3 px-4 py-2.5 rounded-xl border-2 border-dashed border-gray-300 hover:border-[#0d9488] transition bg-gray-50 cursor-pointer">
                                <svg class="w-5 h-5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                
                                <template x-if="!fileName">
                                    <span class="text-xs font-bold text-gray-600">Attach screenshot or document (Optional)</span>
                                </template>

                                <template x-if="fileName">
                                    <span class="text-xs font-bold text-teal-700 truncate" x-text="fileName"></span>
                                </template>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <template x-if="fileName">
                                <button type="button" @click="removeFile($refs.replyFileInput)" class="px-3 py-2 text-xs font-bold text-rose-600 bg-rose-50 rounded-xl hover:bg-rose-100 transition">
                                    Remove Attachment
                                </button>
                            </template>

                            <button type="submit" class="bg-[#0d9488] text-white px-7 py-2.5 rounded-xl font-bold shadow-md hover:bg-teal-700 transition flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                <span>Send Reply</span>
                            </button>
                        </div>
                    </div>

                    <!-- File Preview Box -->
                    <template x-if="fileIsImage && filePreview">
                        <div class="mt-2 relative inline-block">
                            <img :src="filePreview" class="h-24 w-auto object-cover rounded-xl border-2 border-teal-500 shadow-sm">
                            <span class="absolute top-1 right-1 bg-black/60 text-white text-[10px] px-1.5 py-0.5 rounded font-bold">Preview</span>
                        </div>
                    </template>
                </form>
            @endif
        </div>
    </div>

    <!-- Image Lightbox Modal -->
    <template x-if="activeModalImg">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" @click="activeModalImg = null">
            <div class="relative max-w-4xl max-h-[90vh] overflow-hidden rounded-2xl bg-black p-2 shadow-2xl" @click.stop>
                <button type="button" @click="activeModalImg = null" class="absolute top-4 right-4 z-10 rounded-full bg-black/70 p-2 text-white hover:bg-black transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                <img :src="activeModalImg" class="max-h-[85vh] w-auto object-contain rounded-xl mx-auto">
            </div>
        </div>
    </template>
</div>
@endsection
