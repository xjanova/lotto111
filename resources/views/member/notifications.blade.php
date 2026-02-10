@extends('member.layouts.app')
@section('title', 'แจ้งเตือน')

@section('content')
<div x-data="notiPage()" class="space-y-6 pb-4">

    {{-- Page Header --}}
    <div class="flex items-center justify-between animate-fade-up">
        <div>
            <h1 class="text-xl font-bold text-white">แจ้งเตือน</h1>
            <p class="text-xs text-white/30 mt-1">การแจ้งเตือนและข่าวสารจากระบบ</p>
        </div>
        <button @click="markAllRead()" class="text-xs text-gold-400/60 hover:text-gold-400 transition-colors">อ่านทั้งหมด</button>
    </div>

    {{-- Unread Count --}}
    @if($unreadCount > 0)
    <div class="card-dark px-4 py-3 animate-fade-up delay-100 flex items-center gap-3" style="border-color:rgba(251,191,36,0.15)">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(251,191,36,0.1)">
            <span class="text-sm font-bold text-gold-400">{{ $unreadCount }}</span>
        </div>
        <span class="text-xs text-white/50">การแจ้งเตือนที่ยังไม่ได้อ่าน</span>
    </div>
    @endif

    {{-- Filter Tabs --}}
    <div class="flex gap-2 overflow-x-auto scrollbar-hide animate-fade-up delay-200">
        <template x-for="f in filters" :key="f.id">
            <button @click="activeFilter = f.id"
                    :class="activeFilter === f.id ? 'bg-gold-400/20 text-gold-400 border-gold-400/30' : 'text-white/30 border-white/5'"
                    class="whitespace-nowrap px-4 py-2 rounded-xl text-xs font-semibold border transition-all"
                    x-text="f.name">
            </button>
        </template>
    </div>

    {{-- Notifications List --}}
    <div class="space-y-2 animate-fade-up delay-300">
        @forelse($notifications as $noti)
        @php
            $notiConfig = match($noti['type']) {
                'result' => ['text-gold-400', 'rgba(251,191,36,0.1)', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                'deposit' => ['text-emerald-400', 'rgba(16,185,129,0.1)', 'M12 4v16m8-8H4'],
                'withdrawal' => ['text-orange-400', 'rgba(249,115,22,0.1)', 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                'promotion' => ['text-pink-400', 'rgba(236,72,153,0.1)', 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7'],
                'system' => ['text-blue-400', 'rgba(59,130,246,0.1)', 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                'win' => ['text-gold-400', 'rgba(251,191,36,0.1)', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                default => ['text-white/30', 'rgba(255,255,255,0.04)', 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
            };
        @endphp
        <div class="card-dark px-4 py-4 {{ !$noti['read'] ? 'border-l-2' : '' }}" style="{{ !$noti['read'] ? 'border-left-color:' . ($notiConfig[0] === 'text-gold-400' ? '#fbbf24' : ($notiConfig[0] === 'text-emerald-400' ? '#34d399' : ($notiConfig[0] === 'text-orange-400' ? '#fb923c' : ($notiConfig[0] === 'text-pink-400' ? '#f472b6' : '#60a5fa')))) : '' }}">
            <div class="flex gap-3">
                <div class="w-9 h-9 rounded-lg flex-shrink-0 flex items-center justify-center" style="background:{{ $notiConfig[1] }}">
                    <svg class="w-4 h-4 {{ $notiConfig[0] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $notiConfig[2] }}"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <div class="text-sm font-semibold {{ $noti['read'] ? 'text-white/50' : 'text-white' }}">{{ $noti['title'] }}</div>
                        @if(!$noti['read'])
                        <span class="w-2 h-2 rounded-full bg-gold-400 flex-shrink-0"></span>
                        @endif
                    </div>
                    <p class="text-xs {{ $noti['read'] ? 'text-white/20' : 'text-white/40' }} leading-relaxed">{{ $noti['message'] }}</p>
                    <div class="text-[10px] text-white/15 mt-2">{{ $noti['time'] }}</div>
                </div>
            </div>
        </div>
        @empty
        <div class="card-dark py-16 text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-2xl flex items-center justify-center" style="background:rgba(255,255,255,0.03)">
                <svg class="w-7 h-7 text-white/10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <p class="text-sm text-white/25">ไม่มีการแจ้งเตือน</p>
        </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
function notiPage() {
    return {
        activeFilter: 'all',
        filters: [
            { id: 'all', name: 'ทั้งหมด' },
            { id: 'result', name: 'ผลหวย' },
            { id: 'deposit', name: 'เติมเงิน' },
            { id: 'promotion', name: 'โปรโมชั่น' },
            { id: 'system', name: 'ระบบ' },
        ],
        markAllRead() {
            alert('อ่านทั้งหมดแล้ว (Mockup)');
        }
    };
}
</script>
@endpush
