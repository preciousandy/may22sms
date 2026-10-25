@extends('layouts.admin')

@section('admin_content')
@push('header_title', 'Service Controls & Availability')

<div class="space-y-6" x-data="serviceManagement()">

    <!-- Top Header & Summary Stats -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 flex items-center gap-2.5">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-teal-500/10 text-teal-600 border border-teal-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                </span>
                <span>Service Controls &amp; Availability</span>
            </h1>
            <p class="text-xs text-gray-500 mt-1">
                Enable or disable system services in real time. Changes take effect immediately across all user dashboards and backend purchase APIs.
            </p>
        </div>

        <!-- Quick Stats Badges -->
        <div class="flex items-center gap-2.5">
            <div class="px-3.5 py-2 rounded-xl bg-white border border-gray-200 shadow-sm flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span class="text-xs font-bold text-gray-600">Active:</span>
                <span class="text-xs font-black text-emerald-600">{{ $enabledCount }}</span>
            </div>
            <div class="px-3.5 py-2 rounded-xl bg-white border border-gray-200 shadow-sm flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                <span class="text-xs font-bold text-gray-600">Disabled:</span>
                <span class="text-xs font-black text-rose-600">{{ $disabledCount }}</span>
            </div>
            <div class="px-3.5 py-2 rounded-xl bg-gray-900 text-white shadow-sm flex items-center gap-2">
                <span class="text-xs font-bold text-gray-300">Total:</span>
                <span class="text-xs font-black text-white">{{ $totalCount }}</span>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center justify-between shadow-sm">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </span>
            <button onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 font-black">✕</button>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center justify-between shadow-sm">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ session('error') }}</span>
            </span>
            <button onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-900 font-black">✕</button>
        </div>
    @endif

    <!-- Information Card -->
    <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-900 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div class="text-xs leading-relaxed">
            <span class="font-black text-amber-950">Safety Policy &amp; Transactions Protection:</span>
            Disabling a service instantly rejects all new customer purchase requests and HTTP submissions with a maintenance message. <strong>Transactions currently pending or in progress will NOT be cancelled or corrupted</strong>; providers will continue to be queried and legitimate reconciliation or refunds proceed safely.
        </div>
    </div>

    <!-- Services Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($services as $service)
            @php
                $isEnabled = (bool) $service->is_enabled;
                $cardBorder = $isEnabled ? 'border-gray-200 hover:border-teal-500/40' : 'border-rose-200 bg-rose-50/20';
            @endphp
            <div class="bg-white rounded-2xl border {{ $cardBorder }} p-5 shadow-sm transition-all hover:shadow-md flex flex-col justify-between">
                <div>
                    <!-- Header: Icon, Name & Status Badge -->
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center {{ $isEnabled ? 'bg-teal-50 text-teal-600 border border-teal-100' : 'bg-rose-50 text-rose-600 border border-rose-100' }}">
                                @switch($service->key)
                                    @case('airtime')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        @break
                                    @case('data')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                                        @break
                                    @case('electricity')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        @break
                                    @case('betting')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @break
                                    @case('sms_verify')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                        @break
                                    @case('boosting')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                        @break
                                    @case('crypto_to_naira')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @break
                                    @case('digital_logs')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        @break
                                    @default
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                @endswitch
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-gray-900 leading-snug">{{ $service->name }}</h3>
                                <div class="text-[10px] font-mono text-gray-400">key: {{ $service->key }}</div>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        @if($isEnabled)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                Enabled
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                Disabled
                            </span>
                        @endif
                    </div>

                    <!-- Description -->
                    <p class="text-xs text-gray-500 mb-4 line-clamp-2">
                        {{ $service->description ?? 'Service availability controls.' }}
                    </p>

                    <!-- Maintenance Notice Preview if disabled -->
                    @if(!$isEnabled && $service->maintenance_message)
                        <div class="mb-4 p-2.5 rounded-xl bg-rose-100/60 border border-rose-200/80 text-[11px] text-rose-900 font-medium">
                            <span class="font-bold">Notice to users:</span> {{ $service->maintenance_message }}
                        </div>
                    @endif

                    <!-- Metadata details -->
                    <div class="space-y-1.5 py-2.5 border-t border-gray-100 text-[11px] text-gray-500">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Last Changed:</span>
                            <span class="font-semibold text-gray-700">
                                {{ $service->last_status_changed_at ? $service->last_status_changed_at->format('M d, Y h:i A') : 'Default (Never)' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-400">Changed By:</span>
                            <span class="font-semibold text-gray-700">
                                {{ $service->updatedByAdmin?->name ?? 'System Seeder' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action Button / Toggle Form -->
                <div class="pt-3 mt-2 border-t border-gray-100">
                    @if($isEnabled)
                        <button type="button" 
                                @click="openDisableModal('{{ $service->key }}', '{{ addslashes($service->name) }}', '{{ addslashes($service->maintenance_message ?? 'This service is temporarily unavailable. Please try again later.') }}')"
                                class="w-full py-2.5 px-4 rounded-xl font-bold text-xs uppercase tracking-wider bg-rose-600 hover:bg-rose-700 text-white transition-all shadow-sm shadow-rose-900/20 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            <span>Disable Service</span>
                        </button>
                    @else
                        <form method="POST" action="{{ route('admin.services.toggle', $service->key) }}" class="inline-block w-full">
                            @csrf
                            <input type="hidden" name="is_enabled" value="1">
                            <input type="hidden" name="reason" value="Re-enabled by administrator">
                            <button type="submit" 
                                    class="w-full py-2.5 px-4 rounded-xl font-bold text-xs uppercase tracking-wider bg-emerald-600 hover:bg-emerald-700 text-white transition-all shadow-sm shadow-emerald-900/20 flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Enable Service</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Audit Logs Section -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mt-8">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    <span>Audit Trail — Service Availability History</span>
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Immutable record of all service enable/disable actions.</p>
            </div>
            <span class="text-xs font-bold text-gray-400">Recent 25 events</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200/80 text-[11px] font-black uppercase text-gray-500 tracking-wider">
                        <th class="py-3 px-4">Date &amp; Time</th>
                        <th class="py-3 px-4">Service</th>
                        <th class="py-3 px-4">Transition</th>
                        <th class="py-3 px-4">Administrator</th>
                        <th class="py-3 px-4">Reason / Notes</th>
                        <th class="py-3 px-4">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentLogs as $log)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="py-3 px-4 whitespace-nowrap text-gray-600 font-mono text-[11px]">
                                {{ $log->created_at->format('M d, Y h:i:s A') }}
                            </td>
                            <td class="py-3 px-4 font-bold text-gray-900 whitespace-nowrap">
                                {{ $log->serviceConfiguration?->name ?? $log->service_key }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $log->previous_status ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $log->previous_status ? 'Enabled' : 'Disabled' }}
                                    </span>
                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black {{ $log->new_status ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $log->new_status ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap font-medium text-gray-800">
                                {{ $log->admin?->name ?? 'Admin #' . $log->admin_id }}
                            </td>
                            <td class="py-3 px-4 text-gray-600 max-w-xs truncate" title="{{ $log->reason }}">
                                {{ $log->reason ?: '—' }}
                            </td>
                            <td class="py-3 px-4 text-gray-400 font-mono text-[11px] whitespace-nowrap">
                                {{ $log->ip_address ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-400 font-medium">
                                No service status changes have been recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Confirmation Modal Before Disabling Service -->
    <div x-show="isModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-gray-950/70 backdrop-blur-sm flex items-center justify-center p-4"
         style="display: none;">
        
        <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-200"
             @click.away="closeModal()">
            
            <!-- Warning Header -->
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 leading-snug">
                        Disable <span x-text="targetServiceName" class="text-rose-600"></span>?
                    </h3>
                    <p class="text-xs text-gray-500">Confirm service shutdown</p>
                </div>
            </div>

            <!-- Caution Notice -->
            <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200/80 text-rose-900 text-xs leading-relaxed mb-4">
                <strong>Attention:</strong> Disabling this service means users will <strong>no longer be able to make new purchases or place new orders</strong> through this service. All API purchase requests will be rejected immediately.
            </div>

            <!-- Form -->
            <form :action="'/admin/services/' + targetServiceKey + '/toggle'" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="is_enabled" value="0">

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Reason for Disabling (Internal Audit Log)
                    </label>
                    <input type="text" 
                           name="reason" 
                           x-model="reason"
                           placeholder="e.g. Upstream provider maintenance, rate limit issues, testing"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                           required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        User Notice Message
                    </label>
                    <textarea name="maintenance_message" 
                              x-model="maintenanceMessage"
                              rows="3"
                              class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs text-gray-900 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                              placeholder="Message shown to customers attempting to access or purchase"></textarea>
                    <p class="text-[10px] text-gray-400 mt-1">Default: "This service is temporarily unavailable. Please try again later."</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                    <button type="button" 
                            @click="closeModal()" 
                            class="px-4 py-2.5 rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-100 transition">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider bg-rose-600 hover:bg-rose-700 text-white transition shadow-sm shadow-rose-900/30">
                        Confirm &amp; Disable Service
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function serviceManagement() {
    return {
        isModalOpen: false,
        targetServiceKey: '',
        targetServiceName: '',
        reason: '',
        maintenanceMessage: '',
        openDisableModal(key, name, defaultMessage) {
            this.targetServiceKey = key;
            this.targetServiceName = name;
            this.reason = 'Provider maintenance or temporary pause';
            this.maintenanceMessage = defaultMessage || 'This service is temporarily unavailable. Please try again later.';
            this.isModalOpen = true;
        },
        closeModal() {
            this.isModalOpen = false;
        }
    }
}
</script>
@endsection
