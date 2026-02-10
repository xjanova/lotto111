@extends('member.layouts.app')
@section('title', 'แทงหวย')

@section('content')
<div x-data="lotteryPage()" class="space-y-6 pb-4">

    {{-- Page Header --}}
    <div class="animate-fade-up">
        <h1 class="text-xl font-bold text-white">แทงหวย</h1>
        <p class="text-xs text-white/30 mt-1">เลือกประเภทหวยและงวดที่ต้องการ</p>
    </div>

    {{-- Lottery Types Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 animate-fade-up delay-100">
        @foreach($lotteryTypes as $type)
        <button @click="selectType({{ $type['id'] }})"
                :class="selectedType === {{ $type['id'] }} ? 'ring-2 ring-gold-400 border-gold-400/30' : ''"
                class="card-dark p-4 text-left transition-all group relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-20 h-20 rounded-full filter blur-[40px] opacity-20" style="background:{{ $type['color'] }}"></div>
            <div class="relative z-10">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center mb-3" style="background:{{ $type['color'] }}20">
                    <span class="text-lg">{{ $type['icon'] }}</span>
                </div>
                <div class="text-sm font-bold text-white group-hover:text-gold-400 transition-colors">{{ $type['name'] }}</div>
                <div class="text-[10px] text-white/25 mt-1">{{ $type['rounds_count'] }} งวดเปิด</div>
            </div>
        </button>
        @endforeach
    </div>

    {{-- Open Rounds for Selected Type --}}
    <div x-show="selectedType" x-cloak x-transition class="animate-fade-up delay-200">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-bold text-white">งวดที่เปิดรับ</h2>
            <button @click="selectedType=null; selectedRound=null" class="text-xs text-white/30 hover:text-white transition-colors">เปลี่ยนประเภท</button>
        </div>
        <div class="space-y-2">
            @foreach($openRounds as $round)
            <button @click="selectRound({{ $round['id'] }})"
                    x-show="selectedType === {{ $round['type_id'] }}"
                    :class="selectedRound === {{ $round['id'] }} ? 'ring-2 ring-gold-400 border-gold-400/30' : ''"
                    class="card-dark w-full p-4 text-left transition-all flex items-center justify-between">
                <div>
                    <div class="text-sm font-semibold text-white">{{ $round['name'] }}</div>
                    <div class="flex items-center gap-2 mt-1">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></div>
                        <span class="text-[11px] text-emerald-400">เปิดรับ</span>
                        <span class="text-[11px] text-white/20">ปิด {{ $round['close_at'] }}</span>
                    </div>
                </div>
                <svg class="w-5 h-5 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
            @endforeach
        </div>
    </div>

    {{-- Bet Form --}}
    <div x-show="selectedRound" x-cloak x-transition class="space-y-4">

        {{-- Bet Type Tabs --}}
        <div class="animate-fade-up delay-300">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-bold text-white">ประเภทการแทง</h2>
            </div>
            <div class="flex gap-2 overflow-x-auto scrollbar-hide pb-1">
                <template x-for="bt in betTypes" :key="bt.id">
                    <button @click="activeBetType = bt.id"
                            :class="activeBetType === bt.id ? 'bg-gold-400/20 text-gold-400 border-gold-400/30' : 'text-white/40 border-white/5 hover:text-white/60'"
                            class="whitespace-nowrap px-4 py-2 rounded-xl text-xs font-semibold border transition-all"
                            x-text="bt.name">
                    </button>
                </template>
            </div>
        </div>

        {{-- Number Input --}}
        <div class="card-dark p-5 animate-fade-up delay-400">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-white">กรอกเลข</h3>
                <div class="text-[10px] text-white/25" x-text="'(' + getDigitLength() + ' หลัก)'"></div>
            </div>

            {{-- Quick Number Input --}}
            <div class="space-y-3">
                <div class="flex gap-2">
                    <input type="text" x-model="numberInput"
                           :maxlength="getDigitLength()"
                           @keyup.enter="addNumber()"
                           class="flex-1 px-4 py-3 rounded-xl text-white text-center text-lg font-mono font-bold tracking-[0.5em] border border-white/10 focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/20 outline-none transition-all"
                           style="background:rgba(255,255,255,0.03)"
                           :placeholder="'กรอกเลข ' + getDigitLength() + ' หลัก'">
                    <input type="number" x-model="amountInput"
                           class="w-28 px-4 py-3 rounded-xl text-white text-center font-bold border border-white/10 focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/20 outline-none transition-all"
                           style="background:rgba(255,255,255,0.03)"
                           placeholder="จำนวนเงิน">
                </div>
                <div class="flex gap-2">
                    <button @click="addNumber()"
                            class="flex-1 btn-gold py-2.5 rounded-xl text-sm font-bold">
                        เพิ่มเลข
                    </button>
                    <button @click="showQuickPick = !showQuickPick"
                            class="px-4 py-2.5 rounded-xl text-xs font-semibold border border-white/10 text-white/50 hover:text-white hover:border-white/20 transition-all">
                        สุ่มเลข
                    </button>
                </div>
            </div>

            {{-- Quick Amount Buttons --}}
            <div class="flex gap-2 mt-3">
                <template x-for="amt in [10, 20, 50, 100, 500]" :key="amt">
                    <button @click="amountInput = amt"
                            :class="amountInput == amt ? 'bg-gold-400/20 text-gold-400 border-gold-400/30' : 'text-white/30 border-white/5'"
                            class="flex-1 py-1.5 rounded-lg text-xs font-bold border transition-all"
                            x-text="'฿' + amt">
                    </button>
                </template>
            </div>
        </div>

        {{-- Cart / Bet List --}}
        <div class="card-dark overflow-hidden animate-fade-up delay-500" x-show="cart.length > 0" x-cloak>
            <div class="px-5 py-3 flex items-center justify-between border-b border-white/5">
                <h3 class="text-sm font-bold text-white">โพยของคุณ</h3>
                <span class="text-xs text-white/30" x-text="cart.length + ' รายการ'"></span>
            </div>
            <div class="max-h-60 overflow-y-auto scrollbar-hide">
                <template x-for="(item, idx) in cart" :key="idx">
                    <div class="flex items-center justify-between px-5 py-3 border-b border-white/5 last:border-0">
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-white/20" x-text="getBetTypeName(item.type)"></span>
                            <span class="text-sm font-mono font-bold text-gold-400" x-text="item.number"></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-bold text-white" x-text="'฿' + item.amount"></span>
                            <button @click="removeFromCart(idx)" class="text-red-400/50 hover:text-red-400 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
            <div class="px-5 py-4 border-t border-white/5" style="background:rgba(251,191,36,0.03)">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs text-white/40">รวมทั้งหมด</span>
                    <span class="text-lg font-black gradient-text" x-text="'฿' + getCartTotal()"></span>
                </div>
                <button @click="submitBet()"
                        class="w-full btn-gold py-3 rounded-xl text-sm font-black flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    ยืนยันแทง
                </button>
            </div>
        </div>
    </div>

    {{-- Recent Winning Numbers --}}
    <div class="animate-fade-up delay-300">
        <h2 class="text-sm font-bold text-white mb-3">ผลรางวัลล่าสุด</h2>
        <div class="space-y-2">
            @foreach($recentResults as $result)
            <div class="card-dark px-4 py-3 flex items-center justify-between">
                <div>
                    <div class="text-xs text-white/30">{{ $result['type'] }}</div>
                    <div class="text-[11px] text-white/20">{{ $result['date'] }}</div>
                </div>
                <div class="flex items-center gap-2">
                    @foreach(str_split($result['number']) as $digit)
                    <span class="w-7 h-7 rounded-lg flex items-center justify-center text-sm font-bold text-gold-400" style="background:rgba(251,191,36,0.1)">{{ $digit }}</span>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function lotteryPage() {
    return {
        selectedType: null,
        selectedRound: null,
        activeBetType: 'top3',
        numberInput: '',
        amountInput: 10,
        showQuickPick: false,
        cart: [],
        betTypes: [
            { id: 'top3', name: '3 ตัวบน', digits: 3 },
            { id: 'tod3', name: '3 ตัวโต๊ด', digits: 3 },
            { id: 'top2', name: '2 ตัวบน', digits: 2 },
            { id: 'bot2', name: '2 ตัวล่าง', digits: 2 },
            { id: 'run_top', name: 'วิ่งบน', digits: 1 },
            { id: 'run_bot', name: 'วิ่งล่าง', digits: 1 },
        ],

        selectType(id) {
            this.selectedType = id;
            this.selectedRound = null;
        },

        selectRound(id) {
            this.selectedRound = id;
        },

        getDigitLength() {
            const bt = this.betTypes.find(b => b.id === this.activeBetType);
            return bt ? bt.digits : 3;
        },

        getBetTypeName(id) {
            const bt = this.betTypes.find(b => b.id === id);
            return bt ? bt.name : id;
        },

        addNumber() {
            if (!this.numberInput || this.numberInput.length !== this.getDigitLength()) {
                alert('กรุณากรอกเลข ' + this.getDigitLength() + ' หลัก');
                return;
            }
            if (!this.amountInput || this.amountInput < 1) {
                alert('กรุณากรอกจำนวนเงิน');
                return;
            }
            this.cart.push({
                type: this.activeBetType,
                number: this.numberInput,
                amount: parseInt(this.amountInput),
            });
            this.numberInput = '';
        },

        removeFromCart(idx) {
            this.cart.splice(idx, 1);
        },

        getCartTotal() {
            return this.cart.reduce((sum, item) => sum + item.amount, 0);
        },

        submitBet() {
            if (this.cart.length === 0) return alert('กรุณาเพิ่มเลขก่อน');
            alert('ส่งโพยสำเร็จ! (Mockup - ยังไม่เชื่อมต่อ API)');
            this.cart = [];
        }
    };
}
</script>
@endpush
