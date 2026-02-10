@extends('member.layouts.app')
@section('title', 'ประวัติรายการ')

@section('content')
<div x-data="transactionsPage()" class="space-y-6 pb-4">

    {{-- Page Header --}}
    <div class="animate-fade-up">
        <h1 class="text-xl font-bold text-white">ประวัติรายการ</h1>
        <p class="text-xs text-white/30 mt-1">รายการเคลื่อนไหวทางการเงินทั้งหมด</p>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 gap-3 animate-fade-up delay-100">
        <div class="card-dark p-4">
            <div class="text-xs text-white/30 mb-1">รายรับ</div>
            <div class="text-lg font-bold text-emerald-400">+฿{{ number_format($totalIncome, 2) }}</div>
            <div class="text-[10px] text-white/15 mt-1">30 วันล่าสุด</div>
        </div>
        <div class="card-dark p-4">
            <div class="text-xs text-white/30 mb-1">รายจ่าย</div>
            <div class="text-lg font-bold text-red-400">-฿{{ number_format($totalExpense, 2) }}</div>
            <div class="text-[10px] text-white/15 mt-1">30 วันล่าสุด</div>
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

    {{-- Transactions List --}}
    <div class="space-y-2 animate-fade-up delay-300">
        @forelse($transactions as $tx)
        @php
            $isCredit = in_array($tx['type'], ['deposit', 'win', 'refund', 'commission', 'bonus', 'adjustment_credit']);
            $typeConfig = match($tx['type']) {
                'deposit' => ['text-emerald-400', 'rgba(16,185,129,0.1)', 'M12 4v16m8-8H4', 'เติมเงิน'],
                'withdraw' => ['text-orange-400', 'rgba(249,115,22,0.1)', 'M20 12H4', 'ถอนเงิน'],
                'bet' => ['text-indigo-400', 'rgba(99,102,241,0.1)', 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z', 'แทงหวย'],
                'win' => ['text-gold-400', 'rgba(251,191,36,0.1)', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'ถูกรางวัล'],
                'commission' => ['text-purple-400', 'rgba(168,85,247,0.1)', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'ค่าแนะนำ'],
                'bonus' => ['text-pink-400', 'rgba(236,72,153,0.1)', 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7', 'โบนัส'],
                default => ['text-white/30', 'rgba(255,255,255,0.04)', 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', $tx['type']],
            };
        @endphp
        <div class="card-dark px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:{{ $typeConfig[1] }}">
                    <svg class="w-4 h-4 {{ $typeConfig[0] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $typeConfig[2] }}"/></svg>
                </div>
                <div>
                    <div class="text-sm font-medium text-white/70">{{ $typeConfig[3] }}</div>
                    <div class="text-[10px] text-white/20">{{ $tx['description'] ?? '' }}</div>
                    <div class="text-[10px] text-white/15 mt-0.5">{{ $tx['date'] }}</div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-sm font-bold {{ $isCredit ? 'text-emerald-400' : 'text-red-400' }}">
                    {{ $isCredit ? '+' : '-' }}฿{{ number_format(abs($tx['amount']), 2) }}
                </div>
                <div class="text-[10px] text-white/15">฿{{ number_format($tx['balance_after'], 2) }}</div>
            </div>
        </div>
        @empty
        <div class="card-dark py-16 text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-2xl flex items-center justify-center" style="background:rgba(255,255,255,0.03)">
                <svg class="w-7 h-7 text-white/10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <p class="text-sm text-white/25">ยังไม่มีรายการ</p>
        </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
function transactionsPage() {
    return {
        activeFilter: 'all',
        filters: [
            { id: 'all', name: 'ทั้งหมด' },
            { id: 'deposit', name: 'เติมเงิน' },
            { id: 'withdraw', name: 'ถอนเงิน' },
            { id: 'bet', name: 'แทงหวย' },
            { id: 'win', name: 'ถูกรางวัล' },
            { id: 'commission', name: 'ค่าแนะนำ' },
        ],
    };
}
</script>
@endpush
