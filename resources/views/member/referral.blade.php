@extends('member.layouts.app')
@section('title', 'แนะนำเพื่อน')

@section('content')
<div class="space-y-6 pb-4" x-data="referralPage()">

    {{-- Header --}}
    <div class="relative overflow-hidden rounded-2xl p-6 animate-fade-up" style="background:linear-gradient(135deg,rgba(168,85,247,0.3),rgba(124,58,237,0.2),rgba(79,70,229,0.15));border:1px solid rgba(255,255,255,0.08)">
        <div class="absolute -top-20 -right-20 w-60 h-60 bg-purple-500/20 rounded-full filter blur-[80px] animate-blob"></div>
        <div class="relative z-10">
            <h1 class="text-xl font-bold text-white mb-1">
                <svg class="w-6 h-6 inline mr-1 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                แนะนำเพื่อน
            </h1>
            <p class="text-white/40 text-sm">แนะนำเพื่อนมาเล่น รับค่าคอมมิชชั่น {{ $affiliate['commission_rate'] }}% จากยอดเดิมพัน</p>
        </div>
    </div>

    {{-- Referral Link --}}
    <div class="card-dark p-5 animate-fade-up delay-100" x-data="{ copied: false }">
        <div class="text-xs text-white/30 uppercase tracking-wider font-semibold mb-3">ลิงก์แนะนำของคุณ</div>
        <div class="flex items-center gap-2">
            <div class="flex-1 px-4 py-3 rounded-xl text-sm font-mono text-purple-300 truncate" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06)">
                {{ $affiliate['referral_link'] }}
            </div>
            <button @click="navigator.clipboard.writeText('{{ $affiliate['referral_link'] }}'); copied=true; setTimeout(()=>copied=false, 2000)"
                    class="flex-shrink-0 px-4 py-3 rounded-xl text-sm font-bold transition-all"
                    :class="copied ? 'bg-emerald-500/20 text-emerald-400' : 'btn-gold'">
                <span x-show="!copied">คัดลอก</span>
                <span x-show="copied">สำเร็จ!</span>
            </button>
        </div>
        <div class="flex items-center gap-2 mt-3">
            <span class="text-xs text-white/20">รหัสแนะนำ:</span>
            <span class="text-xs font-mono font-bold text-gold-400">{{ $affiliate['referral_code'] }}</span>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 animate-fade-up delay-200">
        <div class="card-dark p-4 text-center">
            <div class="text-2xl font-black text-white">{{ $affiliate['total_referrals'] }}</div>
            <div class="text-[11px] text-white/30 mt-1">เพื่อนทั้งหมด</div>
        </div>
        <div class="card-dark p-4 text-center">
            <div class="text-2xl font-black text-gold-400">฿{{ number_format($affiliate['total_commission'], 2) }}</div>
            <div class="text-[11px] text-white/30 mt-1">คอมมิชชั่นรวม</div>
        </div>
        <div class="card-dark p-4 text-center">
            <div class="text-2xl font-black text-purple-400">฿{{ number_format($affiliate['pending_commission'], 2) }}</div>
            <div class="text-[11px] text-white/30 mt-1">รอถอน</div>
        </div>
        <div class="card-dark p-4 text-center">
            <div class="text-2xl font-black text-emerald-400">฿{{ number_format($affiliate['today_commission'], 2) }}</div>
            <div class="text-[11px] text-white/30 mt-1">วันนี้</div>
        </div>
    </div>

    {{-- Withdraw Commission --}}
    @if($affiliate['pending_commission'] >= 1)
    <div class="card-dark p-4 flex items-center justify-between animate-fade-up delay-200">
        <div>
            <div class="text-sm font-semibold text-white">ถอนคอมมิชชั่น</div>
            <div class="text-xs text-white/30">ยอดรอถอน ฿{{ number_format($affiliate['pending_commission'], 2) }}</div>
        </div>
        <button @click="withdrawCommission()" :disabled="withdrawing"
                class="btn-gold px-5 py-2.5 rounded-xl text-xs font-bold disabled:opacity-50">
            <span x-show="!withdrawing">ถอนเข้ากระเป๋า</span>
            <span x-show="withdrawing">กำลังถอน...</span>
        </button>
    </div>
    @endif

    {{-- Referral List --}}
    <div class="animate-fade-up delay-300">
        <h2 class="text-sm font-bold text-white mb-3">รายชื่อเพื่อนที่แนะนำ ({{ $referrals->total() }})</h2>
        @if($referrals->count())
        <div class="space-y-2">
            @foreach($referrals as $ref)
            <div class="card-dark px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold" style="background:linear-gradient(135deg,rgba(99,102,241,0.2),rgba(124,58,237,0.15))">
                        <span class="text-indigo-300">{{ mb_substr($ref->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-white/80">{{ Str::mask($ref->name, '*', 3) }}</div>
                        <div class="text-[10px] text-white/20">{{ $ref->phone ? Str::mask($ref->phone, '*', 3, 4) : '-' }}</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-[11px] text-white/20">สมัครเมื่อ</div>
                    <div class="text-xs text-white/40">{{ $ref->created_at?->diffForHumans() }}</div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $referrals->links() }}</div>
        @else
        <div class="card-dark py-10 text-center">
            <div class="w-12 h-12 mx-auto mb-3 rounded-xl flex items-center justify-center" style="background:rgba(255,255,255,0.04)">
                <svg class="w-6 h-6 text-white/10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <p class="text-xs text-white/20">ยังไม่มีเพื่อนที่แนะนำ</p>
            <p class="text-[11px] text-white/10 mt-1">แชร์ลิงก์ด้านบนให้เพื่อนของคุณ</p>
        </div>
        @endif
    </div>

    {{-- Recent Commissions --}}
    @if($commissions->count())
    <div class="animate-fade-up delay-400">
        <h2 class="text-sm font-bold text-white mb-3">คอมมิชชั่นล่าสุด</h2>
        <div class="space-y-2">
            @foreach($commissions as $c)
            <div class="card-dark px-4 py-3 flex items-center justify-between">
                <div>
                    <div class="text-xs text-white/50">{{ $c->fromUser?->name ? Str::mask($c->fromUser->name, '*', 3) : '-' }}</div>
                    <div class="text-[10px] text-white/20">ยอดเดิมพัน ฿{{ number_format($c->bet_amount, 0) }} @ {{ $c->commission_rate }}%</div>
                </div>
                <div class="text-right">
                    <div class="text-sm font-bold text-gold-400">+฿{{ number_format($c->commission, 2) }}</div>
                    <div class="text-[10px] {{ $c->status === 'paid' ? 'text-emerald-400/60' : 'text-white/20' }}">
                        {{ $c->status === 'paid' ? 'จ่ายแล้ว' : 'รอถอน' }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function referralPage() {
    return {
        withdrawing: false,
        async withdrawCommission() {
            this.withdrawing = true;
            try {
                const res = await fetchApi('{{ route("member.referral.withdraw") }}', { method: 'POST' });
                if (res.success) {
                    alert('ถอนคอมมิชชั่นสำเร็จ ฿' + (res.amount || ''));
                    location.reload();
                } else {
                    alert(res.message || 'เกิดข้อผิดพลาด');
                }
            } catch (e) {
                alert('ไม่สามารถเชื่อมต่อได้');
            } finally {
                this.withdrawing = false;
            }
        }
    }
}
</script>
@endpush
