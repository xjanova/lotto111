@extends('member.layouts.app')
@section('title', 'ถอนเงิน')

@section('content')
<div x-data="withdrawPage()" class="space-y-6 pb-4">

    {{-- Page Header --}}
    <div class="animate-fade-up">
        <h1 class="text-xl font-bold text-white">ถอนเงิน</h1>
        <p class="text-xs text-white/30 mt-1">ถอนเงินเข้าบัญชีธนาคารของคุณ</p>
    </div>

    {{-- Balance Card --}}
    <div class="card-dark p-5 animate-fade-up delay-100 relative overflow-hidden">
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-orange-500/10 rounded-full filter blur-[60px]"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <div class="text-xs text-white/30 mb-1">ยอดเงินคงเหลือ</div>
                    <div class="text-2xl font-black gradient-text">฿{{ number_format($balance, 2) }}</div>
                </div>
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:linear-gradient(135deg,rgba(249,115,22,0.15),rgba(249,115,22,0.08))">
                    <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            @if($pendingAmount > 0)
            <div class="flex items-center gap-2 text-xs text-amber-400/80 bg-amber-400/5 rounded-lg px-3 py-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>รอถอน ฿{{ number_format($pendingAmount, 2) }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Bank Account Selection --}}
    <div class="animate-fade-up delay-200">
        <h2 class="text-sm font-bold text-white mb-3">เลือกบัญชีรับเงิน</h2>
        @if(count($userBankAccounts) > 0)
        <div class="space-y-2">
            @foreach($userBankAccounts as $bank)
            <button @click="selectedBank = {{ $bank['id'] }}"
                    :class="selectedBank === {{ $bank['id'] }} ? 'ring-2 ring-gold-400 border-gold-400/30' : ''"
                    class="card-dark w-full p-4 text-left transition-all flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold" style="background:{{ $bank['color'] }}20;color:{{ $bank['color'] }}">
                    {{ mb_substr($bank['bank_name'], 0, 2) }}
                </div>
                <div class="flex-1">
                    <div class="text-sm font-semibold text-white">{{ $bank['bank_name'] }}</div>
                    <div class="text-xs text-white/30">{{ $bank['account_number'] }} - {{ $bank['account_name'] }}</div>
                </div>
                <div x-show="selectedBank === {{ $bank['id'] }}" class="w-6 h-6 rounded-full bg-gold-400 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-brand-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                </div>
            </button>
            @endforeach
        </div>
        @else
        <div class="card-dark p-5 text-center">
            <div class="w-12 h-12 mx-auto mb-3 rounded-xl flex items-center justify-center" style="background:rgba(255,255,255,0.04)">
                <svg class="w-6 h-6 text-white/15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <p class="text-xs text-white/25 mb-3">ยังไม่มีบัญชีธนาคาร</p>
            <a href="{{ route('member.profile') }}" class="inline-block btn-gold text-xs px-4 py-2 rounded-lg font-bold">เพิ่มบัญชีธนาคาร</a>
        </div>
        @endif
    </div>

    {{-- Amount Input --}}
    <div class="card-dark p-5 animate-fade-up delay-300">
        <h3 class="text-sm font-bold text-white mb-4">จำนวนเงินที่ต้องการถอน</h3>
        <input type="number" x-model="amount"
               class="w-full px-4 py-3.5 rounded-xl text-white text-center text-xl font-bold border border-white/10 focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/20 outline-none transition-all mb-3"
               style="background:rgba(255,255,255,0.03)"
               placeholder="0.00"
               min="100" step="100">
        <div class="grid grid-cols-4 gap-2 mb-4">
            <template x-for="amt in [300, 500, 1000, 5000]" :key="amt">
                <button @click="amount = amt"
                        :class="amount == amt ? 'bg-gold-400/20 text-gold-400 border-gold-400/30' : 'text-white/30 border-white/5'"
                        class="py-2 rounded-lg text-xs font-bold border transition-all"
                        x-text="'฿' + amt.toLocaleString()">
                </button>
            </template>
        </div>
        <button @click="amount = {{ $balance }}" class="w-full py-2 rounded-lg text-xs font-semibold border border-white/5 text-white/30 hover:text-white/50 hover:border-white/10 transition-all mb-4">
            ถอนทั้งหมด ฿{{ number_format($balance, 2) }}
        </button>
        <div class="flex items-center gap-2 text-xs text-white/25 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>ถอนขั้นต่ำ ฿100 / สูงสุด ฿50,000 ต่อครั้ง / ดำเนินการ 5-15 นาที</span>
        </div>
        <button @click="submitWithdraw()"
                :disabled="!selectedBank || !amount || amount < 100"
                class="w-full py-3.5 rounded-xl text-sm font-black flex items-center justify-center gap-2 transition-all"
                :class="selectedBank && amount >= 100 ? 'btn-gold' : 'bg-white/5 text-white/20 cursor-not-allowed'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            ยืนยันถอนเงิน
        </button>
    </div>

    {{-- Recent Withdrawals --}}
    <div class="animate-fade-up delay-400">
        <h2 class="text-sm font-bold text-white mb-3">ประวัติถอนเงิน</h2>
        <div class="space-y-2">
            @forelse($recentWithdrawals as $w)
            <div class="card-dark px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(249,115,22,0.1)">
                        <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-white">฿{{ number_format($w['amount'], 2) }}</div>
                        <div class="text-[10px] text-white/20">{{ $w['bank'] }} {{ $w['date'] }}</div>
                    </div>
                </div>
                @php
                    $wStatus = match($w['status']) {
                        'completed','approved' => ['bg-emerald-400/10 text-emerald-400', 'สำเร็จ'],
                        'pending' => ['bg-amber-400/10 text-amber-400', 'รอดำเนินการ'],
                        'rejected' => ['bg-red-400/10 text-red-400', 'ปฏิเสธ'],
                        default => ['bg-white/5 text-white/30', $w['status']],
                    };
                @endphp
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $wStatus[0] }}">
                    {{ $wStatus[1] }}
                </span>
            </div>
            @empty
            <div class="card-dark py-8 text-center">
                <p class="text-xs text-white/20">ยังไม่มีประวัติถอนเงิน</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function withdrawPage() {
    return {
        selectedBank: {{ $userBankAccounts[0]['id'] ?? 'null' }},
        amount: 0,
        submitWithdraw() {
            if (!this.selectedBank) return alert('กรุณาเลือกบัญชีธนาคาร');
            if (!this.amount || this.amount < 100) return alert('จำนวนเงินขั้นต่ำ ฿100');
            alert('ยื่นคำขอถอนเงิน ฿' + this.amount.toLocaleString() + ' สำเร็จ (Mockup - ยังไม่เชื่อมต่อ API)');
        }
    };
}
</script>
@endpush
