@extends('member.layouts.app')
@section('title', 'โปรไฟล์')

@section('content')
<div class="space-y-6 pb-4" x-data="profilePage()">

    {{-- Header --}}
    <div class="relative overflow-hidden rounded-2xl p-6 animate-fade-up" style="background:linear-gradient(135deg,rgba(79,70,229,0.3),rgba(124,58,237,0.2));border:1px solid rgba(255,255,255,0.08)">
        <div class="relative z-10 flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-xl font-black" style="background:linear-gradient(135deg,#4f46e5,#7c3aed)">
                {{ mb_substr($user->name, 0, 1) }}
            </div>
            <div>
                <h1 class="text-xl font-bold text-white">{{ $user->name }}</h1>
                <div class="text-sm text-white/40">{{ $user->phone }}</div>
                <div class="flex items-center gap-2 mt-1">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold" style="background:{{ $user->vip_level?->color() ?? '#cd7f32' }}20;color:{{ $user->vip_level?->color() ?? '#cd7f32' }}">
                        {{ $user->vip_level?->label() ?? 'Bronze' }}
                    </span>
                    <span class="text-[11px] text-white/20">สมาชิกตั้งแต่ {{ $user->created_at?->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Profile --}}
    <div class="card-dark p-5 animate-fade-up delay-100">
        <h2 class="text-sm font-bold text-white mb-4">ข้อมูลส่วนตัว</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-xs text-white/30 uppercase tracking-wider font-semibold mb-1.5">ชื่อ-นามสกุล</label>
                <input type="text" x-model="name" class="w-full px-4 py-3 rounded-xl text-white text-sm outline-none transition-all focus:ring-2 focus:ring-gold-400/20" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06)">
            </div>
            <div>
                <label class="block text-xs text-white/30 uppercase tracking-wider font-semibold mb-1.5">เบอร์โทรศัพท์</label>
                <input type="text" value="{{ $user->phone }}" disabled class="w-full px-4 py-3 rounded-xl text-white/30 text-sm cursor-not-allowed" style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.04)">
                <p class="text-[10px] text-white/15 mt-1">ไม่สามารถแก้ไขเบอร์โทรศัพท์ได้</p>
            </div>
            <div>
                <label class="block text-xs text-white/30 uppercase tracking-wider font-semibold mb-1.5">LINE ID</label>
                <input type="text" x-model="lineId" placeholder="กรอก LINE ID" class="w-full px-4 py-3 rounded-xl text-white text-sm placeholder-white/15 outline-none transition-all focus:ring-2 focus:ring-gold-400/20" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06)">
            </div>

            <div x-show="message" x-transition class="px-4 py-2.5 rounded-xl text-xs" :class="messageType === 'success' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20'" x-text="message"></div>

            <button @click="saveProfile()" :disabled="saving" class="btn-gold w-full py-3 rounded-xl text-sm font-bold disabled:opacity-50">
                <span x-show="!saving">บันทึก</span>
                <span x-show="saving">กำลังบันทึก...</span>
            </button>
        </div>
    </div>

    {{-- Bank Account --}}
    <div class="card-dark p-5 animate-fade-up delay-200">
        <h2 class="text-sm font-bold text-white mb-4">บัญชีธนาคาร</h2>
        @if($user->primaryBankAccount)
        <div class="flex items-center gap-4 p-4 rounded-xl" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.05)">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-lg font-bold" style="background:linear-gradient(135deg,rgba(16,185,129,0.15),rgba(16,185,129,0.08))">
                <span class="text-emerald-400">{{ mb_substr($user->primaryBankAccount->bank_name ?? '?', 0, 2) }}</span>
            </div>
            <div>
                <div class="text-sm font-semibold text-white">{{ $user->primaryBankAccount->bank_name }}</div>
                <div class="text-xs text-white/40 font-mono">{{ Str::mask($user->primaryBankAccount->account_number ?? '', '*', 3, -3) }}</div>
                <div class="text-[11px] text-white/20">{{ $user->primaryBankAccount->account_name }}</div>
            </div>
            <div class="ml-auto">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">หลัก</span>
            </div>
        </div>
        @else
        <div class="text-center py-6">
            <p class="text-xs text-white/20">ยังไม่ได้ผูกบัญชีธนาคาร</p>
        </div>
        @endif
    </div>

    {{-- Account Info --}}
    <div class="card-dark p-5 animate-fade-up delay-300">
        <h2 class="text-sm font-bold text-white mb-4">ข้อมูลบัญชี</h2>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between py-2 border-b border-white/5">
                <span class="text-white/30">รหัสแนะนำ</span>
                <span class="font-mono font-bold text-gold-400">{{ $user->referral_code ?? '-' }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-white/5">
                <span class="text-white/30">VIP Level</span>
                <span style="color:{{ $user->vip_level?->color() ?? '#cd7f32' }}">{{ $user->vip_level?->label() ?? 'Bronze' }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-white/5">
                <span class="text-white/30">XP</span>
                <span class="text-white/60">{{ number_format($user->xp) }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-white/5">
                <span class="text-white/30">เข้าสู่ระบบล่าสุด</span>
                <span class="text-white/40">{{ $user->last_login_at?->diffForHumans() ?? '-' }}</span>
            </div>
            @if($user->referrer)
            <div class="flex justify-between py-2">
                <span class="text-white/30">แนะนำโดย</span>
                <span class="text-purple-400">{{ Str::mask($user->referrer->name, '*', 3) }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Logout --}}
    <div class="animate-fade-up delay-400">
        <form method="POST" action="/logout">
            @csrf
            <button type="submit" class="w-full py-3 rounded-xl text-sm font-medium text-red-400 hover:text-red-300 transition-colors" style="background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.1)">
                ออกจากระบบ
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function profilePage() {
    return {
        name: @json($user->name),
        lineId: @json($user->line_id ?? ''),
        saving: false,
        message: '',
        messageType: '',
        async saveProfile() {
            this.saving = true;
            this.message = '';
            try {
                const res = await fetchApi('{{ route("member.profile.update") }}', {
                    method: 'PUT',
                    body: JSON.stringify({ name: this.name, line_id: this.lineId }),
                });
                this.messageType = res.success ? 'success' : 'error';
                this.message = res.message || (res.success ? 'บันทึกสำเร็จ' : 'เกิดข้อผิดพลาด');
            } catch (e) {
                this.messageType = 'error';
                this.message = 'ไม่สามารถเชื่อมต่อได้';
            } finally {
                this.saving = false;
            }
        }
    }
}
</script>
@endpush
