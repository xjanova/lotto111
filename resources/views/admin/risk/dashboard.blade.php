@extends('admin.layouts.app')
@section('title', 'Risk Control')
@section('page-title', 'Risk Control')
@section('breadcrumb') <span class="text-gray-700">Risk Control</span> @endsection

@section('content')
<div class="space-y-6">

    {{-- Hero Banner --}}
    <div class="relative overflow-hidden rounded-2xl p-6 md:p-8 animate-fade-up" style="background: linear-gradient(135deg, #4f46e5, #7c3aed, #db2777);">
        <div class="absolute top-0 left-0 w-72 h-72 bg-white/10 rounded-full filter blur-3xl animate-blob"></div>
        <div class="absolute bottom-0 right-0 w-72 h-72 bg-pink-300/10 rounded-full filter blur-3xl animate-blob delay-200"></div>
        <div class="absolute top-1/2 left-1/3 w-48 h-48 bg-purple-300/10 rounded-full filter blur-3xl animate-blob delay-400"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-white mb-1">Risk Control Center</h2>
                <p class="text-white/70 text-sm">ศูนย์ควบคุมความเสี่ยง กำไร/ขาดทุน และการแจ้งเตือน</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.risk.alerts') }}" class="px-4 py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white rounded-xl text-sm font-medium transition-all duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Alerts
                </a>
                <a href="{{ route('admin.risk.settings') }}" class="px-4 py-2.5 bg-white text-indigo-700 rounded-xl text-sm font-bold transition-all duration-200 hover:shadow-lg hover:shadow-white/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0"/></svg>
                    Risk Settings
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Profit Today --}}
        <div class="stat-card card-premium p-4 animate-fade-up delay-100">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">กำไรวันนี้</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                     style="background: linear-gradient(135deg, {{ ($riskStats['profit_today'] ?? 0) >= 0 ? '#d1fae5, #a7f3d0' : '#fee2e2, #fecaca' }});">
                    <svg class="w-4.5 h-4.5 {{ ($riskStats['profit_today'] ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold {{ ($riskStats['profit_today'] ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                ฿{{ number_format($riskStats['profit_today'] ?? 0, 2) }}
            </div>
            <div class="mt-1">
                <span class="text-xs font-medium px-1.5 py-0.5 rounded-full {{ ($riskStats['profit_today'] ?? 0) >= 0 ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50' }}">
                    {{ ($riskStats['profit_today'] ?? 0) >= 0 ? 'กำไร' : 'ขาดทุน' }}
                </span>
            </div>
        </div>

        {{-- Exposure --}}
        <div class="stat-card card-premium p-4 animate-fade-up delay-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Exposure</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ffedd5, #fed7aa);">
                    <svg class="w-4.5 h-4.5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-orange-600">฿{{ number_format($riskStats['total_exposure'] ?? 0, 2) }}</div>
            <div class="text-[10px] text-gray-400 mt-1">ความเสี่ยงรวม</div>
        </div>

        {{-- Win Rate --}}
        <div class="stat-card card-premium p-4 animate-fade-up delay-300">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Win Rate</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ede9fe, #ddd6fe);">
                    <svg class="w-4.5 h-4.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800">{{ number_format($riskStats['player_win_rate'] ?? 0, 1) }}%</div>
            <div class="text-[10px] text-gray-400 mt-1">อัตราชนะผู้เล่น</div>
        </div>

        {{-- Active Alerts --}}
        <div class="stat-card card-premium p-4 animate-fade-up delay-400">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Alerts</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #fee2e2, #fecaca);">
                    <svg class="w-4.5 h-4.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-red-600">{{ $riskStats['active_alerts'] ?? 0 }}</div>
            <div class="text-[10px] text-red-500 font-medium mt-1">
                @if(($riskStats['active_alerts'] ?? 0) > 0)
                    <span class="badge-pulse inline-flex items-center gap-1">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                        ต้องตรวจสอบ
                    </span>
                @else
                    ไม่มีการแจ้งเตือน
                @endif
            </div>
        </div>
    </div>

    {{-- Top Winners & Number Exposure --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Top Winners --}}
        <div class="card-premium p-6 animate-fade-up delay-300">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #d1fae5, #a7f3d0);">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Top Winners วันนี้</h3>
                    <p class="text-xs text-gray-400">ผู้เล่นที่ชนะมากที่สุดวันนี้</p>
                </div>
            </div>
            <div class="space-y-1">
                @forelse($topWinners ?? [] as $idx => $w)
                <div class="flex items-center justify-between py-3 px-3 rounded-xl table-row-hover transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold
                            {{ $idx === 0 ? 'bg-gradient-to-br from-amber-100 to-yellow-100 text-amber-700' : ($idx === 1 ? 'bg-gradient-to-br from-gray-100 to-slate-100 text-gray-600' : 'bg-gradient-to-br from-orange-50 to-amber-50 text-orange-600') }}">
                            #{{ $idx + 1 }}
                        </div>
                        <span class="text-sm font-medium text-gray-700">{{ $w['name'] ?? '-' }}</span>
                    </div>
                    <span class="text-sm font-bold text-emerald-600">+฿{{ number_format($w['amount'] ?? 0, 2) }}</span>
                </div>
                @empty
                <div class="py-10 text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-gray-50 to-indigo-50/30 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <span class="text-sm text-gray-400">ไม่มีข้อมูล</span>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Number Exposure --}}
        <div class="card-premium p-6 animate-fade-up delay-400">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ffedd5, #fed7aa);">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">เลขที่ถูกแทงเยอะสุด</h3>
                    <p class="text-xs text-gray-400">ตัวเลขที่มียอดรวมสูงที่สุด</p>
                </div>
            </div>
            <div class="space-y-1">
                @forelse($numberExposure ?? [] as $idx => $n)
                <div class="flex items-center justify-between py-3 px-3 rounded-xl table-row-hover transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-8 rounded-lg flex items-center justify-center font-mono font-bold text-sm" style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe); color: #4f46e5;">
                            {{ $n['number'] ?? '-' }}
                        </div>
                        <span class="px-2 py-0.5 text-[10px] rounded-full bg-purple-50 text-purple-600 font-semibold uppercase">{{ $n['bet_type'] ?? '' }}</span>
                    </div>
                    <span class="text-sm font-bold text-orange-600">฿{{ number_format($n['total_amount'] ?? 0, 2) }}</span>
                </div>
                @empty
                <div class="py-10 text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-gray-50 to-indigo-50/30 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                    </div>
                    <span class="text-sm text-gray-400">ไม่มีข้อมูล</span>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @php
            $quickLinks = [
                ['route' => 'admin.risk.users', 'label' => 'จัดการ User Risk', 'desc' => 'Win rate, block numbers', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color_from' => '#e0e7ff', 'color_to' => '#c7d2fe', 'text_color' => 'text-indigo-600'],
                ['route' => 'admin.risk.settings', 'label' => 'Risk Settings', 'desc' => 'Global risk parameters', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0', 'color_from' => '#ede9fe', 'color_to' => '#ddd6fe', 'text_color' => 'text-purple-600'],
                ['route' => 'admin.risk.alerts', 'label' => 'Alerts', 'desc' => 'ดูการแจ้งเตือน', 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', 'color_from' => '#fee2e2', 'color_to' => '#fecaca', 'text_color' => 'text-red-600'],
                ['route' => 'admin.risk.profit-snapshots', 'label' => 'Profit Snapshots', 'desc' => 'ภาพรวมกำไร', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'color_from' => '#d1fae5', 'color_to' => '#a7f3d0', 'text_color' => 'text-emerald-600'],
            ];
        @endphp
        @foreach($quickLinks as $idx => $link)
        <a href="{{ route($link['route']) }}" class="card-premium p-5 text-center group animate-fade-up delay-{{ ($idx + 5) * 100 }}">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-3 transition-transform group-hover:scale-110" style="background: linear-gradient(135deg, {{ $link['color_from'] }}, {{ $link['color_to'] }});">
                <svg class="w-6 h-6 {{ $link['text_color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}"/></svg>
            </div>
            <div class="text-sm font-bold text-gray-700 group-hover:text-indigo-700 transition-colors">{{ $link['label'] }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $link['desc'] }}</div>
        </a>
        @endforeach
    </div>
</div>
@endsection
