@extends('member.layouts.app')
@section('title', 'เติมเงิน')

@section('content')
<div x-data="depositPage()" class="space-y-6 pb-4">

    {{-- Page Header --}}
    <div class="animate-fade-up">
        <h1 class="text-xl font-bold text-white">เติมเงิน</h1>
        <p class="text-xs text-white/30 mt-1">เลือกช่องทางและจำนวนเงินที่ต้องการเติม</p>
    </div>

    {{-- Balance Card --}}
    <div class="card-dark p-5 animate-fade-up delay-100 relative overflow-hidden">
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-emerald-500/10 rounded-full filter blur-[60px]"></div>
        <div class="relative z-10 flex items-center justify-between">
            <div>
                <div class="text-xs text-white/30 mb-1">ยอดเงินคงเหลือ</div>
                <div class="text-2xl font-black gradient-text">฿{{ number_format($balance, 2) }}</div>
            </div>
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center" style="background:linear-gradient(135deg,rgba(16,185,129,0.15),rgba(16,185,129,0.08))">
                <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
        </div>
    </div>

    {{-- Deposit Methods --}}
    <div class="animate-fade-up delay-200">
        <h2 class="text-sm font-bold text-white mb-3">ช่องทางเติมเงิน</h2>
        <div class="grid grid-cols-2 gap-3">
            <button @click="method = 'bank'"
                    :class="method === 'bank' ? 'ring-2 ring-gold-400 border-gold-400/30' : ''"
                    class="card-dark p-4 text-left transition-all">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-2" style="background:linear-gradient(135deg,rgba(59,130,246,0.15),rgba(59,130,246,0.08))">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div class="text-sm font-semibold text-white">โอนผ่านธนาคาร</div>
                <div class="text-[10px] text-white/25 mt-1">ฝากอัตโนมัติ 1-3 นาที</div>
            </button>
            <button @click="method = 'truewallet'"
                    :class="method === 'truewallet' ? 'ring-2 ring-gold-400 border-gold-400/30' : ''"
                    class="card-dark p-4 text-left transition-all">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-2" style="background:linear-gradient(135deg,rgba(249,115,22,0.15),rgba(249,115,22,0.08))">
                    <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <div class="text-sm font-semibold text-white">TrueWallet</div>
                <div class="text-[10px] text-white/25 mt-1">เติมผ่าน TrueMoney</div>
            </button>
        </div>
    </div>

    {{-- Bank Transfer Info --}}
    <div x-show="method === 'bank'" x-cloak x-transition class="space-y-4">
        {{-- Bank Accounts --}}
        <div class="animate-fade-up delay-300">
            <h2 class="text-sm font-bold text-white mb-3">บัญชีสำหรับโอนเงิน</h2>
            @foreach($bankAccounts as $bank)
            <div class="card-dark p-4 mb-2" x-data="{ copied: false }">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg font-bold" style="background:{{ $bank['color'] }}20;color:{{ $bank['color'] }}">
                        {{ mb_substr($bank['bank'], 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-white">{{ $bank['bank'] }}</div>
                        <div class="text-xs text-white/30">{{ $bank['name'] }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-mono font-bold text-gold-400">{{ $bank['number'] }}</div>
                        <button @click="navigator.clipboard.writeText('{{ $bank['number'] }}'); copied=true; setTimeout(()=>copied=false,2000)"
                                class="text-[10px] transition-colors"
                                :class="copied ? 'text-emerald-400' : 'text-white/25 hover:text-white/50'"
                                x-text="copied ? 'คัดลอกแล้ว!' : 'คัดลอก'">
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Amount Input --}}
        <div class="card-dark p-5">
            <h3 class="text-sm font-bold text-white mb-4">จำนวนเงิน</h3>
            <input type="number" x-model="amount"
                   class="w-full px-4 py-3.5 rounded-xl text-white text-center text-xl font-bold border border-white/10 focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/20 outline-none transition-all mb-3"
                   style="background:rgba(255,255,255,0.03)"
                   placeholder="0.00"
                   min="100" step="100">
            <div class="grid grid-cols-4 gap-2 mb-4">
                <template x-for="amt in [100, 300, 500, 1000]" :key="amt">
                    <button @click="amount = amt"
                            :class="amount == amt ? 'bg-gold-400/20 text-gold-400 border-gold-400/30' : 'text-white/30 border-white/5'"
                            class="py-2 rounded-lg text-xs font-bold border transition-all"
                            x-text="'฿' + amt.toLocaleString()">
                    </button>
                </template>
            </div>
            <div class="flex items-center gap-2 text-xs text-white/25 mb-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>เติมขั้นต่ำ ฿100 / สูงสุด ฿50,000</span>
            </div>
            <button @click="submitDeposit()"
                    class="w-full btn-gold py-3.5 rounded-xl text-sm font-black flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                แจ้งเติมเงิน
            </button>
        </div>
    </div>

    {{-- TrueWallet --}}
    <div x-show="method === 'truewallet'" x-cloak x-transition>
        <div class="card-dark p-5 text-center">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center" style="background:linear-gradient(135deg,rgba(249,115,22,0.15),rgba(249,115,22,0.08))">
                <svg class="w-8 h-8 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="text-sm font-bold text-white mb-2">TrueWallet</h3>
            <p class="text-xs text-white/30 mb-4">โอนเงินไปที่เบอร์ TrueWallet ด้านล่าง</p>
            <div class="text-xl font-mono font-bold text-gold-400 mb-2">088-888-8888</div>
            <p class="text-[10px] text-white/20">ชื่อบัญชี: บริษัท ลอตโต จำกัด</p>
        </div>
    </div>

    {{-- Recent Deposits --}}
    <div class="animate-fade-up delay-400">
        <h2 class="text-sm font-bold text-white mb-3">ประวัติเติมเงิน</h2>
        <div class="space-y-2">
            @forelse($recentDeposits as $dep)
            <div class="card-dark px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(16,185,129,0.1)">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-white">฿{{ number_format($dep['amount'], 2) }}</div>
                        <div class="text-[10px] text-white/20">{{ $dep['date'] }}</div>
                    </div>
                </div>
                @php
                    $depStatus = match($dep['status']) {
                        'credited' => ['bg-emerald-400/10 text-emerald-400', 'สำเร็จ'],
                        'pending' => ['bg-amber-400/10 text-amber-400', 'รอตรวจสอบ'],
                        'rejected' => ['bg-red-400/10 text-red-400', 'ปฏิเสธ'],
                        default => ['bg-white/5 text-white/30', $dep['status']],
                    };
                @endphp
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $depStatus[0] }}">
                    {{ $depStatus[1] }}
                </span>
            </div>
            @empty
            <div class="card-dark py-8 text-center">
                <p class="text-xs text-white/20">ยังไม่มีประวัติเติมเงิน</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function depositPage() {
    return {
        method: 'bank',
        amount: 100,
        submitDeposit() {
            if (!this.amount || this.amount < 100) return alert('กรุณากรอกจำนวนเงินขั้นต่ำ ฿100');
            alert('แจ้งเติมเงินสำเร็จ ฿' + this.amount.toLocaleString() + ' (Mockup - ยังไม่เชื่อมต่อ API)');
        }
    };
}
</script>
@endpush
