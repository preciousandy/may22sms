@extends('layouts.dashboard')

@section('page_title', 'SportyBet Codes')

@section('dashboard_content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    @if(session('success'))
        <div class="px-6 py-4 bg-green-50 border-b border-green-100">
            <div class="text-green-700 font-bold">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="px-6 py-4 bg-red-50 border-b border-red-100">
            <div class="text-red-700 font-bold">{{ session('error') }}</div>
        </div>
    @endif

    <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50">
        <h3 class="text-lg font-bold text-gray-800">SportyBet Codes</h3>
        <span class="bg-[#0d9488] text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">Daily Updates</span>
    </div>
    
    <div class="p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($codes as $code)
                @php
                    $isPurchased = in_array($code->id, $purchasedCodeIds);
                    $isFree = $code->amount == 0;
                @endphp
                <div class="bg-gray-50 rounded-2xl border-2 border-gray-100 p-6 hover:border-[#0d9488] transition group relative overflow-hidden">
                    <div class="absolute top-0 right-0 mt-4 mr-4 flex gap-2">
                        <span class="{{ $isFree ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700' }} text-[10px] font-extrabold px-2 py-1 rounded-full uppercase tracking-wider">
                            {{ $isFree ? 'Free' : 'Paid' }}
                        </span>
                        @if($isPurchased)
                            <span class="bg-green-100 text-green-700 text-[10px] font-extrabold px-2 py-1 rounded-full uppercase tracking-wider">
                                Unlocked
                            </span>
                        @endif
                    </div>
                    
                    <div class="mb-4">
                        <div class="text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-1">Bet Code</div>
                        @if($isPurchased)
                            <div class="flex items-center gap-2">
                                <span class="text-xl font-black text-gray-900 tracking-tight" id="code-{{ $code->id }}">{{ $code->code }}</span>
                                <button onclick="copyCode('{{ $code->code }}', '{{ $code->id }}')" class="p-2 text-gray-400 hover:text-[#0d9488] transition" title="Copy Code">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                </button>
                            </div>
                        @else
                            <div class="flex items-center gap-2">
                            <span class="text-xl font-black text-gray-400 tracking-tight">••••••••••</span>
                        </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <div class="text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-1">
                                {{ $isFree ? 'Free' : 'Price' }}
                            </div>
                            <div class="text-lg font-bold text-[#0d9488]">
                                {{ $isFree ? '₦0.00' : '₦' . number_format($code->amount, 2) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-1">Added</div>
                            <div class="text-sm font-bold text-gray-600">{{ $code->created_at->diffForHumans() }}</div>
                        </div>
                    </div>

                    @if($code->description)
                        <div class="border-t border-gray-200 pt-4 mt-4">
                            <div class="text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-1">Description</div>
                            <p class="text-sm font-bold text-gray-700 leading-relaxed">
                                {{ $code->description }}
                            </p>
                        </div>
                    @endif

                    @if(!$isPurchased)
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <form action="/sporty-bet-codes/{{ $code->id }}/purchase" method="POST" data-loader="true" data-loader-title="Purchasing Code..." data-loader-subtitle="Unlocking booking code">
                                @csrf
                                <button type="submit" class="w-full py-3 rounded-xl font-bold text-white transition shadow-lg {{ $isFree ? 'bg-blue-600 hover:bg-blue-700' : 'bg-[#0d9488] hover:bg-[#0a7d73]' }}">
                                    {{ $isFree ? 'Get Free Code' : 'Purchase Code' }}
                                </button>
                            </form>
                        </div>
                    @endif

                    @if($isPurchased)
                        <div id="copy-status-{{ $code->id }}" class="hidden absolute inset-0 bg-white/90 flex items-center justify-center backdrop-blur-sm transition">
                            <div class="flex flex-col items-center">
                                <div class="bg-green-500 text-white p-2 rounded-full mb-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <span class="text-sm font-black text-gray-900 uppercase">Code Copied!</span>
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="col-span-full py-20 flex flex-col items-center justify-center bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                    <div class="bg-gray-100 p-4 rounded-full mb-4">
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m-3 8h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                    <h4 class="text-xl font-black text-gray-900 mb-1">No SportyBet codes available</h4>
                    <p class="text-gray-500 font-bold">Check back later for new updates.</p>
                </div>
            @endforelse
        </div>

        @if($codes->hasPages())
            <div class="mt-8">
                {{ $codes->links() }}
            </div>
        @endif
    </div>
</div>

<script>
function copyCode(code, id) {
    navigator.clipboard.writeText(code).then(() => {
        const status = document.getElementById(`copy-status-${id}`);
        status.classList.remove('hidden');
        setTimeout(() => {
            status.classList.add('hidden');
        }, 2000);
    });
}
</script>
@endsection
