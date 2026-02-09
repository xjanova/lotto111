@extends('admin.layouts.app')
@section('title', 'Risk Control')
@section('page-title', 'Risk Control')
@section('breadcrumb') <span class="text-gray-700">Risk Control</span> @endsection

@section('content')
<div class="space-y-4 animate-fade-in">
    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 stat-card">
            <div class="text-xs text-gray-400 mb-1">กำไรวันนี้</div>
            <div class="text-2xl font-bold {{ ($riskStats['profit_today'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">฿{{ number_format($riskStats['profit_today'] ?? 0, 2) }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 stat-card">
            <div class="text-xs text-gray-400 mb-1">Exposure (ความเสี่ยง)</div>
            <div class="text-2xl font-bold text-orange-600">฿{{ number_format($riskStats['total_exposure'] ?? 0, 2) }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 stat-card">
            <div class="text-xs text-gray-400 mb-1">Win Rate (ผู้เล่น)</div>
            <div class="text-2xl font-bold text-gray-800">{{ number_format($riskStats['player_win_rate'] ?? 0, 1) }}%</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 stat-card">
            <div class="text-xs text-gray-400 mb-1">Alerts Active</div>
            <div class="text-2xl font-bold text-red-600">{{ $riskStats['active_alerts'] ?? 0 }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Top Winners --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">Top Winners วันนี้</h3>
            <div class="space-y-2">
                @forelse($topWinners ?? [] as $w)
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <span class="text-sm text-gray-700">{{ $w['name'] ?? '-' }}</span>
                    <span class="text-sm font-bold text-green-600">+฿{{ number_format($w['amount'] ?? 0, 2) }}</span>
                </div>
                @empty
                <div class="py-4 text-center text-gray-400 text-sm">ไม่มีข้อมูล</div>
                @endforelse
            </div>
        </div>

        {{-- Number Exposure --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">เลขที่ถูกแทงเยอะสุด</h3>
            <div class="space-y-2">
                @forelse($numberExposure ?? [] as $n)
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <div>
                        <span class="font-mono font-bold text-gray-800">{{ $n['number'] ?? '-' }}</span>
                        <span class="text-xs text-gray-400 ml-2">{{ $n['bet_type'] ?? '' }}</span>
                    </div>
                    <span class="text-sm font-bold text-orange-600">฿{{ number_format($n['total_amount'] ?? 0, 2) }}</span>
                </div>
                @empty
                <div class="py-4 text-center text-gray-400 text-sm">ไม่มีข้อมูล</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <a href="{{ route('admin.risk.users') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center hover:shadow-md transition-shadow">
            <div class="text-sm font-medium text-gray-700">จัดการ User Risk</div>
            <div class="text-xs text-gray-400 mt-1">Win rate, block numbers</div>
        </a>
        <a href="{{ route('admin.risk.settings') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center hover:shadow-md transition-shadow">
            <div class="text-sm font-medium text-gray-700">Risk Settings</div>
            <div class="text-xs text-gray-400 mt-1">Global risk parameters</div>
        </a>
        <a href="{{ route('admin.risk.alerts') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center hover:shadow-md transition-shadow">
            <div class="text-sm font-medium text-gray-700">Alerts</div>
            <div class="text-xs text-gray-400 mt-1">ดูการแจ้งเตือน</div>
        </a>
        <a href="{{ route('admin.risk.profit-snapshots') }}" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center hover:shadow-md transition-shadow">
            <div class="text-sm font-medium text-gray-700">Profit Snapshots</div>
            <div class="text-xs text-gray-400 mt-1">ภาพรวมกำไร</div>
        </a>
    </div>
</div>
@endsection
