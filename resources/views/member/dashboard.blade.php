@extends('member.layouts.app')
@section('title', 'หน้าหลัก')

@section('content')
<div class="space-y-6 pb-4">

    {{-- Welcome + Balance Hero --}}
    <div class="relative overflow-hidden rounded-2xl p-6 animate-fade-up" style="background:linear-gradient(135deg,rgba(79,70,229,0.3),rgba(124,58,237,0.2),rgba(219,39,119,0.15));border:1px solid rgba(255,255,255,0.08)">
        <div class="absolute -top-20 -right-20 w-60 h-60 bg-purple-500/20 rounded-full filter blur-[80px] animate-blob"></div>
        <div class="relative z-10">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-white/40 text-sm mb-1">สวัสดี</p>
                    <h1 class="text-xl font-bold text-white">{{ $user->name }}</h1>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase" style="background:{{ $user->vip_level?->color() ?? '#cd7f32' }}20;color:{{ $user->vip_level?->color() ?? '#cd7f32' }}">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            {{ $user->vip_level?->label() ?? 'Bronze' }}
                        </span>
                        <span class="text-xs text-white/20">XP {{ number_format($user->xp) }}</span>
                    </div>
                </div>
                <div class="text-left sm:text-right">
                    <p class="text-white/40 text-xs mb-1">ยอดเงินคงเหลือ</p>
                    <div class="text-3xl font-black gradient-text">฿{{ number_format($user->balance, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-4 gap-3 animate-fade-up delay-100">
        <a href="/" class="card-dark flex flex-col items-center gap-2 py-4 px-2 text-center group">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,rgba(251,191,36,0.15),rgba(245,158,11,0.1))">
                <svg class="w-5 h-5 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <span class="text-[11px] text-white/50 group-hover:text-gold-400 transition-colors font-medium">แทงหวย</span>
        </a>
        <a href="#" class="card-dark flex flex-col items-center gap-2 py-4 px-2 text-center group">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,rgba(16,185,129,0.15),rgba(16,185,129,0.1))">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </div>
            <span class="text-[11px] text-white/50 group-hover:text-emerald-400 transition-colors font-medium">เติมเงิน</span>
        </a>
        <a href="#" class="card-dark flex flex-col items-center gap-2 py-4 px-2 text-center group">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,rgba(249,115,22,0.15),rgba(249,115,22,0.1))">
                <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <span class="text-[11px] text-white/50 group-hover:text-orange-400 transition-colors font-medium">ถอนเงิน</span>
        </a>
        <a href="{{ route('member.referral') }}" class="card-dark flex flex-col items-center gap-2 py-4 px-2 text-center group">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,rgba(168,85,247,0.15),rgba(168,85,247,0.1))">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <span class="text-[11px] text-white/50 group-hover:text-purple-400 transition-colors font-medium">แนะนำ</span>
        </a>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 animate-fade-up delay-200">
        <div class="card-dark p-4">
            <div class="text-xs text-white/30 mb-1">ฝากทั้งหมด</div>
            <div class="text-lg font-bold text-emerald-400">฿{{ number_format($totalDeposits, 0) }}</div>
        </div>
        <div class="card-dark p-4">
            <div class="text-xs text-white/30 mb-1">ถอนทั้งหมด</div>
            <div class="text-lg font-bold text-orange-400">฿{{ number_format($totalWithdrawals, 0) }}</div>
        </div>
        <div class="card-dark p-4">
            <div class="text-xs text-white/30 mb-1">แทงทั้งหมด</div>
            <div class="text-lg font-bold text-indigo-400">฿{{ number_format($totalBets, 0) }}</div>
        </div>
        <div class="card-dark p-4">
            <div class="text-xs text-white/30 mb-1">ถูกรางวัล</div>
            <div class="text-lg font-bold text-gold-400">฿{{ number_format($totalWins, 0) }}</div>
        </div>
    </div>

    {{-- Referral Banner --}}
    @if($user->referral_code)
    <div class="card-dark p-4 animate-fade-up delay-300" x-data="{ copied: false }">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center" style="background:linear-gradient(135deg,rgba(168,85,247,0.15),rgba(168,85,247,0.1))">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                </div>
                <div class="min-w-0">
                    <div class="text-xs text-white/30">ลิงก์แนะนำเพื่อน</div>
                    <div class="text-sm font-mono text-purple-300 truncate">{{ url('/register?ref=' . $user->referral_code) }}</div>
                </div>
            </div>
            <button @click="navigator.clipboard.writeText('{{ url('/register?ref=' . $user->referral_code) }}'); copied=true; setTimeout(()=>copied=false, 2000)"
                    class="flex-shrink-0 px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
                    :class="copied ? 'bg-emerald-500/20 text-emerald-400' : 'bg-white/5 text-white/40 hover:text-white hover:bg-white/10'">
                <span x-show="!copied">คัดลอก</span>
                <span x-show="copied">สำเร็จ!</span>
            </button>
        </div>
        <div class="flex items-center gap-4 mt-3 pt-3 border-t border-white/5 text-xs text-white/30">
            <span>เพื่อน <strong class="text-white/70">{{ $referralCount }}</strong> คน</span>
            <span>คอมมิชชั่นรอ <strong class="text-purple-400">฿{{ number_format($pendingCommission, 2) }}</strong></span>
        </div>
    </div>
    @endif

    {{-- Open Rounds --}}
    @if($openRounds->count())
    <div class="animate-fade-up delay-300">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-bold text-white">หวยที่เปิดอยู่</h2>
            <a href="/" class="text-xs text-gold-400 hover:text-gold-300 transition-colors">ดูทั้งหมด</a>
        </div>
        <div class="flex gap-3 overflow-x-auto scrollbar-hide pb-1">
            @foreach($openRounds as $round)
            <a href="/" class="card-dark p-4 min-w-[200px] flex-shrink-0 group">
                <div class="text-xs text-white/30 mb-1">{{ $round->lotteryType?->name ?? 'หวย' }}</div>
                <div class="text-sm font-semibold text-white group-hover:text-gold-400 transition-colors">{{ $round->name ?? 'งวด ' . ($round->draw_date ?? '') }}</div>
                <div class="flex items-center gap-2 mt-2">
                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></div>
                    <span class="text-[11px] text-emerald-400">เปิดรับ</span>
                    @if($round->close_at)
                    <span class="text-[11px] text-white/20 ml-auto">ปิด {{ $round->close_at->format('H:i') }}</span>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Today's Tickets --}}
    <div class="animate-fade-up delay-400">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-bold text-white">โพยวันนี้</h2>
            <span class="text-xs text-white/20">{{ $todayTickets->count() }} รายการ</span>
        </div>
        @if($todayTickets->count())
        <div class="space-y-2">
            @foreach($todayTickets as $ticket)
            <div class="card-dark px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center text-xs font-bold" style="background:linear-gradient(135deg,rgba(99,102,241,0.15),rgba(99,102,241,0.08))">
                        <span class="text-indigo-400">{{ mb_substr($ticket->lotteryRound?->lotteryType?->name ?? '?', 0, 2) }}</span>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-white">{{ $ticket->number }}</div>
                        <div class="text-[11px] text-white/30">{{ $ticket->lotteryRound?->lotteryType?->name ?? '-' }}</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm font-bold {{ ($ticket->status?->value ?? '') === 'won' ? 'text-emerald-400' : (($ticket->status?->value ?? '') === 'lost' ? 'text-red-400' : 'text-white/60') }}">
                        @if(($ticket->status?->value ?? '') === 'won')
                            +฿{{ number_format($ticket->win_amount, 0) }}
                        @else
                            ฿{{ number_format($ticket->amount, 0) }}
                        @endif
                    </div>
                    <div class="text-[10px] {{ ($ticket->status?->value ?? '') === 'won' ? 'text-emerald-400/60' : (($ticket->status?->value ?? '') === 'lost' ? 'text-red-400/60' : 'text-white/20') }}">
                        {{ match($ticket->status?->value ?? '') {
                            'won' => 'ถูกรางวัล',
                            'lost' => 'ไม่ถูก',
                            'pending' => 'รอผล',
                            default => 'รอผล'
                        } }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="card-dark py-10 text-center">
            <div class="w-12 h-12 mx-auto mb-3 rounded-xl flex items-center justify-center" style="background:rgba(255,255,255,0.04)">
                <svg class="w-6 h-6 text-white/10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <p class="text-xs text-white/20">ยังไม่มีโพยวันนี้</p>
            <a href="/" class="inline-block mt-3 btn-gold text-xs px-4 py-2 rounded-lg">แทงหวยเลย</a>
        </div>
        @endif
    </div>

    {{-- Recent Transactions --}}
    <div class="animate-fade-up delay-500">
        <h2 class="text-sm font-bold text-white mb-3">รายการล่าสุด</h2>
        @if($recentTransactions->count())
        <div class="space-y-2">
            @foreach($recentTransactions as $tx)
            <div class="card-dark px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @php
                        $txType = $tx->type?->value ?? $tx->type ?? '';
                        $icon = match($txType) {
                            'deposit' => ['text-emerald-400', 'bg-emerald-400', 'M12 4v16m8-8H4'],
                            'withdraw' => ['text-orange-400', 'bg-orange-400', 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                            'bet' => ['text-indigo-400', 'bg-indigo-400', 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
                            'win' => ['text-gold-400', 'bg-gold-400', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                            'commission' => ['text-purple-400', 'bg-purple-400', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                            default => ['text-white/30', 'bg-white/30', 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        };
                        $isCredit = in_array($txType, ['deposit', 'win', 'refund', 'commission', 'bonus']);
                    @endphp
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:{{ $icon[1] }}15">
                        <svg class="w-4 h-4 {{ $icon[0] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon[2] }}"/></svg>
                    </div>
                    <div>
                        <div class="text-xs font-medium text-white/70">{{ $tx->type?->label() ?? $txType }}</div>
                        <div class="text-[10px] text-white/20">{{ $tx->created_at?->diffForHumans() ?? '' }}</div>
                    </div>
                </div>
                <div class="text-sm font-bold {{ $isCredit ? 'text-emerald-400' : 'text-red-400' }}">
                    {{ $isCredit ? '+' : '' }}฿{{ number_format(abs($tx->amount), 2) }}
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="card-dark py-8 text-center">
            <p class="text-xs text-white/20">ยังไม่มีรายการ</p>
        </div>
        @endif
    </div>
</div>
@endsection
