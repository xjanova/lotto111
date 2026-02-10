@extends('member.layouts.app')
@section('title', 'โพยของฉัน')

@section('content')
<div x-data="ticketsPage()" class="space-y-6 pb-4">

    {{-- Page Header --}}
    <div class="flex items-center justify-between animate-fade-up">
        <div>
            <h1 class="text-xl font-bold text-white">โพยของฉัน</h1>
            <p class="text-xs text-white/30 mt-1">ตรวจสอบโพยและประวัติการแทง</p>
        </div>
        <a href="{{ route('member.lottery') }}" class="btn-gold text-xs px-4 py-2 rounded-lg font-bold">+ แทงหวย</a>
    </div>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-3 gap-3 animate-fade-up delay-100">
        <div class="card-dark p-4 text-center">
            <div class="text-xs text-white/30 mb-1">แทงทั้งหมด</div>
            <div class="text-lg font-bold text-indigo-400">{{ $stats['total_bets'] }}</div>
        </div>
        <div class="card-dark p-4 text-center">
            <div class="text-xs text-white/30 mb-1">ถูกรางวัล</div>
            <div class="text-lg font-bold text-emerald-400">{{ $stats['total_wins'] }}</div>
        </div>
        <div class="card-dark p-4 text-center">
            <div class="text-xs text-white/30 mb-1">ยอดชนะ</div>
            <div class="text-lg font-bold text-gold-400">฿{{ number_format($stats['total_win_amount']) }}</div>
        </div>
    </div>

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

    {{-- Tickets List --}}
    <div class="space-y-2 animate-fade-up delay-300">
        @forelse($tickets as $ticket)
        <div class="card-dark p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold" style="background:{{ $ticket['color'] }}20">
                        <span style="color:{{ $ticket['color'] }}">{{ mb_substr($ticket['type_name'], 0, 2) }}</span>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-white">{{ $ticket['type_name'] }}</div>
                        <div class="text-[10px] text-white/25">{{ $ticket['round_name'] }}</div>
                    </div>
                </div>
                @php
                    $statusConfig = match($ticket['status']) {
                        'won' => ['bg-emerald-400/10 text-emerald-400', 'ถูกรางวัล'],
                        'lost' => ['bg-red-400/10 text-red-400', 'ไม่ถูก'],
                        'pending' => ['bg-amber-400/10 text-amber-400', 'รอผล'],
                        'cancelled' => ['bg-gray-400/10 text-gray-400', 'ยกเลิก'],
                        default => ['bg-white/5 text-white/30', $ticket['status']],
                    };
                @endphp
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $statusConfig[0] }}">
                    @if($ticket['status'] === 'pending')
                    <span class="w-1 h-1 rounded-full bg-amber-400 animate-pulse"></span>
                    @endif
                    {{ $statusConfig[1] }}
                </span>
            </div>

            {{-- Numbers --}}
            <div class="flex flex-wrap gap-2 mb-3">
                @foreach($ticket['numbers'] as $num)
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-white/5" style="background:rgba(255,255,255,0.02)">
                    <span class="text-[10px] text-white/25">{{ $num['bet_type'] }}</span>
                    <span class="font-mono font-bold text-sm {{ $ticket['status'] === 'won' && $num['won'] ? 'text-gold-400' : 'text-white/70' }}">{{ $num['number'] }}</span>
                    <span class="text-[10px] text-white/30">฿{{ $num['amount'] }}</span>
                    @if($ticket['status'] === 'won' && $num['won'])
                    <span class="text-[10px] text-emerald-400 font-bold">+฿{{ number_format($num['win_amount']) }}</span>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between pt-2 border-t border-white/5">
                <span class="text-[10px] text-white/20">{{ $ticket['date'] }}</span>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-white/30">แทง <strong class="text-white/60">฿{{ number_format($ticket['total_amount']) }}</strong></span>
                    @if($ticket['status'] === 'won')
                    <span class="text-xs text-emerald-400 font-bold">ชนะ +฿{{ number_format($ticket['win_amount']) }}</span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="card-dark py-16 text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-2xl flex items-center justify-center" style="background:rgba(255,255,255,0.03)">
                <svg class="w-7 h-7 text-white/10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
            </div>
            <p class="text-sm text-white/25">ยังไม่มีโพย</p>
            <a href="{{ route('member.lottery') }}" class="inline-block mt-4 btn-gold text-xs px-5 py-2 rounded-lg font-bold">แทงหวยเลย</a>
        </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
function ticketsPage() {
    return {
        activeFilter: 'all',
        filters: [
            { id: 'all', name: 'ทั้งหมด' },
            { id: 'today', name: 'วันนี้' },
            { id: 'pending', name: 'รอผล' },
            { id: 'won', name: 'ถูกรางวัล' },
            { id: 'lost', name: 'ไม่ถูก' },
        ],
    };
}
</script>
@endpush
