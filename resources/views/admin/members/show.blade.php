@extends('admin.layouts.app')
@section('title', 'รายละเอียดสมาชิก - ' . $user->name)
@section('page-title', 'รายละเอียดสมาชิก')
@section('breadcrumb')
    <a href="{{ route('admin.members.index') }}" class="text-brand-600 hover:underline">จัดการสมาชิก</a>
    <span class="mx-2 text-gray-400">/</span>
    <span class="text-gray-700">{{ $user->name }}</span>
@endsection

@section('content')
<div class="space-y-6 animate-fade-in">
    {{-- User Info Card --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">{{ $user->name }}</h2>
                <p class="text-gray-500 text-sm mt-1">{{ $user->phone ?? '-' }} | {{ $user->email ?? '-' }}</p>
                <p class="text-gray-400 text-xs mt-1">สมัครเมื่อ {{ $user->created_at?->format('d/m/Y H:i') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 text-xs rounded-full {{ ($user->status?->value ?? $user->status) === 'active' ? 'bg-green-50 text-green-600' : (($user->status?->value ?? $user->status) === 'suspended' ? 'bg-yellow-50 text-yellow-600' : 'bg-red-50 text-red-600') }}">
                    {{ ($user->status?->value ?? $user->status) === 'active' ? 'ใช้งาน' : (($user->status?->value ?? $user->status) === 'suspended' ? 'ระงับ' : 'แบน') }}
                </span>
                <span class="px-3 py-1 text-xs rounded-full bg-purple-50 text-purple-600">VIP Lv.{{ $user->vip_level ?? 0 }}</span>
            </div>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <div class="text-xs text-gray-400 mb-1">ยอดเงินคงเหลือ</div>
            <div class="text-lg font-bold text-gray-800">{{ number_format($user->balance, 2) }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <div class="text-xs text-gray-400 mb-1">ฝากรวม</div>
            <div class="text-lg font-bold text-green-600">{{ number_format($stats['total_deposits'], 2) }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <div class="text-xs text-gray-400 mb-1">ถอนรวม</div>
            <div class="text-lg font-bold text-red-600">{{ number_format($stats['total_withdrawals'], 2) }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <div class="text-xs text-gray-400 mb-1">แทงรวม</div>
            <div class="text-lg font-bold text-gray-700">{{ number_format($stats['total_bets'], 2) }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <div class="text-xs text-gray-400 mb-1">ถูกรางวัลรวม</div>
            <div class="text-lg font-bold text-brand-600">{{ number_format($stats['total_wins'], 2) }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <div class="text-xs text-gray-400 mb-1">โพยทั้งหมด</div>
            <div class="text-lg font-bold text-gray-700">{{ number_format($stats['total_tickets']) }}</div>
        </div>
    </div>

    {{-- Bank Accounts & Risk Profile --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Bank Accounts --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">บัญชีธนาคาร</h3>
            @if($user->bankAccounts && $user->bankAccounts->count() > 0)
                <div class="space-y-3">
                    @foreach($user->bankAccounts as $account)
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <div>
                                <div class="text-sm font-medium text-gray-700">{{ $account->bank_name }}</div>
                                <div class="text-xs text-gray-500">{{ $account->account_number }} - {{ $account->account_name }}</div>
                            </div>
                            @if($account->is_primary)
                                <span class="ml-auto px-2 py-0.5 text-xs bg-brand-50 text-brand-600 rounded-full">หลัก</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-400">ยังไม่มีบัญชีธนาคาร</p>
            @endif
        </div>

        {{-- Risk Profile --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">โปรไฟล์ความเสี่ยง</h3>
            @if($user->riskProfile)
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">ระดับความเสี่ยง</span><span class="font-medium">{{ $user->riskProfile->risk_level ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">อัตราชนะ</span><span class="font-medium">{{ $user->riskProfile->win_rate ?? '-' }}%</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">ปรับอัตราจ่าย</span><span class="font-medium">{{ $user->riskProfile->rate_adjustment_percent ?? 0 }}%</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">ลิมิตต่อโพย</span><span class="font-medium">{{ $user->riskProfile->max_bet_per_ticket ? number_format($user->riskProfile->max_bet_per_ticket) : 'ไม่จำกัด' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">เลขอั้น</span><span class="font-medium">{{ $user->riskProfile->blocked_numbers ?: 'ไม่มี' }}</span></div>
                </div>
            @else
                <p class="text-sm text-gray-400">ยังไม่มีโปรไฟล์ความเสี่ยง</p>
            @endif
        </div>
    </div>

    {{-- Additional Info --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div><span class="text-gray-400">รหัสแนะนำ</span><div class="font-medium text-gray-700 mt-1">{{ $user->referral_code ?? '-' }}</div></div>
            <div><span class="text-gray-400">ผู้แนะนำ</span><div class="font-medium text-gray-700 mt-1">{{ $stats['referrals_count'] }} คน</div></div>
            <div><span class="text-gray-400">User ID</span><div class="font-medium text-gray-700 mt-1">#{{ $user->id }}</div></div>
            <div><span class="text-gray-400">อัปเดตล่าสุด</span><div class="font-medium text-gray-700 mt-1">{{ $user->updated_at?->format('d/m/Y H:i') }}</div></div>
        </div>
    </div>

    {{-- Back Button --}}
    <div>
        <a href="{{ route('admin.members.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            &larr; กลับไปรายการสมาชิก
        </a>
    </div>
</div>
@endsection
