@extends('admin.layouts.app')
@section('title', 'รายละเอียดสมาชิก - ' . $user->name)
@section('page-title', 'รายละเอียดสมาชิก')
@section('breadcrumb')
    <a href="{{ route('admin.members.index') }}" class="text-brand-600 hover:text-brand-700 transition-colors">จัดการสมาชิก</a>
    <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-700">{{ $user->name }}</span>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Back Button --}}
    <div class="animate-fade-up">
        <a href="{{ route('admin.members.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-xl hover:bg-indigo-100 transition-all duration-200 group">
            <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            กลับไปรายการสมาชิก
        </a>
    </div>

    {{-- User Info Card --}}
    <div class="card-premium overflow-hidden animate-fade-up delay-100">
        <div class="relative p-6" style="background: linear-gradient(135deg, #4f46e5, #7c3aed, #a855f7);">
            {{-- Background decoration --}}
            <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full filter blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-purple-300/10 rounded-full filter blur-3xl"></div>

            <div class="relative z-10 flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    {{-- Gradient Avatar --}}
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-xl font-bold text-white shadow-lg" style="background: linear-gradient(135deg, rgba(255,255,255,0.3), rgba(255,255,255,0.1)); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2);">
                        {{ mb_substr($user->name, 0, 2) }}
                    </div>
                    <div>
                        <h2 class="text-xl md:text-2xl font-bold text-white">{{ $user->name }}</h2>
                        <div class="flex items-center gap-2 mt-1">
                            <svg class="w-3.5 h-3.5 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span class="text-white/70 text-sm">{{ $user->phone ?? '-' }}</span>
                            <span class="text-white/30">|</span>
                            <svg class="w-3.5 h-3.5 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span class="text-white/70 text-sm">{{ $user->email ?? '-' }}</span>
                        </div>
                        <p class="text-white/50 text-xs mt-1.5">สมัครเมื่อ {{ $user->created_at?->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl backdrop-blur-sm
                        {{ ($user->status?->value ?? $user->status) === 'active' ? 'bg-emerald-500/20 text-emerald-100 ring-1 ring-emerald-400/30' : (($user->status?->value ?? $user->status) === 'suspended' ? 'bg-amber-500/20 text-amber-100 ring-1 ring-amber-400/30' : 'bg-red-500/20 text-red-100 ring-1 ring-red-400/30') }}">
                        <span class="w-2 h-2 rounded-full {{ ($user->status?->value ?? $user->status) === 'active' ? 'bg-emerald-400 animate-pulse' : (($user->status?->value ?? $user->status) === 'suspended' ? 'bg-amber-400' : 'bg-red-400') }}"></span>
                        {{ ($user->status?->value ?? $user->status) === 'active' ? 'ใช้งาน' : (($user->status?->value ?? $user->status) === 'suspended' ? 'ระงับ' : 'แบน') }}
                    </span>
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-xl bg-white/20 text-white backdrop-blur-sm ring-1 ring-white/20">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        VIP Lv.{{ $user->vip_level ?? 0 }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        {{-- Balance --}}
        <div class="stat-card card-premium p-4 animate-fade-up delay-100">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">ยอดเงินคงเหลือ</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe);">
                    <svg class="w-4.5 h-4.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
            </div>
            <div class="text-xl font-bold text-gray-800">{{ number_format($user->balance, 2) }}</div>
            <div class="text-[10px] text-gray-400 mt-1">บาท</div>
        </div>

        {{-- Deposits --}}
        <div class="stat-card card-premium p-4 animate-fade-up delay-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">ฝากรวม</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #d1fae5, #a7f3d0);">
                    <svg class="w-4.5 h-4.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
            </div>
            <div class="text-xl font-bold text-emerald-600">{{ number_format($stats['total_deposits'] ?? 0, 2) }}</div>
            <div class="text-[10px] text-gray-400 mt-1">บาท</div>
        </div>

        {{-- Withdrawals --}}
        <div class="stat-card card-premium p-4 animate-fade-up delay-300">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">ถอนรวม</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ffedd5, #fed7aa);">
                    <svg class="w-4.5 h-4.5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                </div>
            </div>
            <div class="text-xl font-bold text-orange-600">{{ number_format($stats['total_withdrawals'] ?? 0, 2) }}</div>
            <div class="text-[10px] text-gray-400 mt-1">บาท</div>
        </div>

        {{-- Bets --}}
        <div class="stat-card card-premium p-4 animate-fade-up delay-400">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">แทงรวม</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ede9fe, #ddd6fe);">
                    <svg class="w-4.5 h-4.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
            <div class="text-xl font-bold text-gray-800">{{ number_format($stats['total_bets'] ?? 0, 2) }}</div>
            <div class="text-[10px] text-gray-400 mt-1">บาท</div>
        </div>

        {{-- Wins --}}
        <div class="stat-card card-premium p-4 animate-fade-up delay-500">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">ถูกรางวัลรวม</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #fce7f3, #fbcfe8);">
                    <svg class="w-4.5 h-4.5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
            </div>
            <div class="text-xl font-bold gradient-text">{{ number_format($stats['total_wins'] ?? 0, 2) }}</div>
            <div class="text-[10px] text-gray-400 mt-1">บาท</div>
        </div>

        {{-- Tickets --}}
        <div class="stat-card card-premium p-4 animate-fade-up delay-600">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">โพยทั้งหมด</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #dbeafe, #bfdbfe);">
                    <svg class="w-4.5 h-4.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                </div>
            </div>
            <div class="text-xl font-bold text-gray-800">{{ number_format($stats['total_tickets'] ?? 0) }}</div>
            <div class="text-[10px] text-gray-400 mt-1">ใบ</div>
        </div>
    </div>

    {{-- Bank Accounts & Risk Profile --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Bank Accounts --}}
        <div class="card-premium p-6 animate-fade-up delay-200">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #dbeafe, #bfdbfe);">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">บัญชีธนาคาร</h3>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wide">Bank Accounts</p>
                </div>
            </div>
            @if($user->bankAccounts && $user->bankAccounts->count() > 0)
                <div class="space-y-3">
                    @foreach($user->bankAccounts as $account)
                        <div class="flex items-center gap-3 p-3.5 rounded-xl border border-indigo-50 transition-all hover:border-indigo-200 hover:shadow-sm" style="background: linear-gradient(135deg, rgba(238,242,255,0.3), rgba(224,231,255,0.2));">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe);">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-semibold text-gray-700">{{ $account->bank_name }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ $account->account_number }} - {{ $account->account_name }}</div>
                            </div>
                            @if($account->is_primary)
                                <span class="px-2.5 py-1 text-[10px] font-semibold rounded-full uppercase tracking-wide flex-shrink-0" style="background: linear-gradient(135deg, #eef2ff, #e0e7ff); color: #4f46e5;">หลัก</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-3" style="background: linear-gradient(135deg, #eef2ff, #e0e7ff);">
                        <svg class="w-7 h-7 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </div>
                    <p class="text-sm text-gray-400">ยังไม่มีบัญชีธนาคาร</p>
                </div>
            @endif
        </div>

        {{-- Risk Profile --}}
        <div class="card-premium p-6 animate-fade-up delay-300">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #fee2e2, #fecaca);">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800">โปรไฟล์ความเสี่ยง</h3>
                    <p class="text-[10px] text-gray-400 uppercase tracking-wide">Risk Profile</p>
                </div>
            </div>
            @if($user->riskProfile)
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-xl border border-indigo-50" style="background: linear-gradient(135deg, rgba(238,242,255,0.3), rgba(224,231,255,0.2));">
                        <span class="text-sm text-gray-500 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            ระดับความเสี่ยง
                        </span>
                        <span class="text-sm font-bold px-3 py-1 rounded-lg
                            @php $riskLevel = $user->riskProfile->risk_level ?? '-'; @endphp
                            {{ $riskLevel === 'high' ? 'bg-red-50 text-red-600' : ($riskLevel === 'medium' ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600') }}">
                            {{ $riskLevel }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl border border-indigo-50" style="background: linear-gradient(135deg, rgba(238,242,255,0.3), rgba(224,231,255,0.2));">
                        <span class="text-sm text-gray-500 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            อัตราชนะ
                        </span>
                        <span class="text-sm font-bold text-gray-700">{{ $user->riskProfile->win_rate ?? '-' }}%</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl border border-indigo-50" style="background: linear-gradient(135deg, rgba(238,242,255,0.3), rgba(224,231,255,0.2));">
                        <span class="text-sm text-gray-500 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                            ปรับอัตราจ่าย
                        </span>
                        <span class="text-sm font-bold text-gray-700">{{ $user->riskProfile->rate_adjustment_percent ?? 0 }}%</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl border border-indigo-50" style="background: linear-gradient(135deg, rgba(238,242,255,0.3), rgba(224,231,255,0.2));">
                        <span class="text-sm text-gray-500 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            ลิมิตต่อโพย
                        </span>
                        <span class="text-sm font-bold text-gray-700">{{ $user->riskProfile->max_bet_per_ticket ? number_format($user->riskProfile->max_bet_per_ticket) : 'ไม่จำกัด' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl border border-indigo-50" style="background: linear-gradient(135deg, rgba(238,242,255,0.3), rgba(224,231,255,0.2));">
                        <span class="text-sm text-gray-500 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            เลขอั้น
                        </span>
                        <span class="text-sm font-bold text-gray-700">{{ $user->riskProfile->blocked_numbers ?: 'ไม่มี' }}</span>
                    </div>
                </div>
            @else
                <div class="py-8 text-center">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-3" style="background: linear-gradient(135deg, #fee2e2, #fecaca);">
                        <svg class="w-7 h-7 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <p class="text-sm text-gray-400">ยังไม่มีโปรไฟล์ความเสี่ยง</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Additional Info --}}
    <div class="card-premium p-6 animate-fade-up delay-400">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ede9fe, #ddd6fe);">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-gray-800">ข้อมูลเพิ่มเติม</h3>
                <p class="text-[10px] text-gray-400 uppercase tracking-wide">Additional Information</p>
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="p-4 rounded-xl border border-indigo-50" style="background: linear-gradient(135deg, rgba(238,242,255,0.3), rgba(224,231,255,0.2));">
                <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">รหัสแนะนำ</span>
                <div class="font-bold text-gray-700 mt-2 font-mono text-sm">{{ $user->referral_code ?? '-' }}</div>
            </div>
            <div class="p-4 rounded-xl border border-indigo-50" style="background: linear-gradient(135deg, rgba(238,242,255,0.3), rgba(224,231,255,0.2));">
                <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">ผู้แนะนำ</span>
                <div class="font-bold text-gray-700 mt-2 text-sm">{{ $stats['referrals_count'] ?? 0 }} คน</div>
            </div>
            <div class="p-4 rounded-xl border border-indigo-50" style="background: linear-gradient(135deg, rgba(238,242,255,0.3), rgba(224,231,255,0.2));">
                <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">User ID</span>
                <div class="font-bold gradient-text mt-2 text-sm">#{{ $user->id }}</div>
            </div>
            <div class="p-4 rounded-xl border border-indigo-50" style="background: linear-gradient(135deg, rgba(238,242,255,0.3), rgba(224,231,255,0.2));">
                <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">อัปเดตล่าสุด</span>
                <div class="font-bold text-gray-700 mt-2 text-sm">{{ $user->updated_at?->format('d/m/Y H:i') }}</div>
            </div>
        </div>
    </div>

</div>
@endsection
