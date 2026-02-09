@extends('admin.layouts.app')
@section('title', 'รายงานการเงิน')
@section('page-title', 'จัดการการเงิน')
@section('breadcrumb') <span class="text-gray-700">การเงิน</span> <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg> <span class="text-gray-700">รายงาน</span> @endsection

@section('content')
<div x-data="reportPage()" class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 animate-fade-up">
        <div>
            <h1 class="text-2xl font-bold gradient-text">รายงานการเงิน</h1>
            <p class="text-sm text-gray-400 mt-1">ภาพรวมรายรับ-รายจ่ายและกำไรของระบบ</p>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="card-premium animate-fade-up delay-100">
        <div class="flex border-b border-indigo-100/50">
            <a href="{{ route('admin.finance.deposits') }}"
               class="relative px-6 py-4 text-sm font-semibold transition-all duration-200 flex items-center gap-2
                      {{ request()->routeIs('admin.finance.deposits') ? 'text-indigo-600' : 'text-gray-400 hover:text-gray-600' }}">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.finance.deposits') ? 'bg-indigo-100' : 'bg-gray-100' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
                ฝากเงิน
                @if(request()->routeIs('admin.finance.deposits'))
                <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full"></span>
                @endif
            </a>
            <a href="{{ route('admin.finance.withdrawals') }}"
               class="relative px-6 py-4 text-sm font-semibold transition-all duration-200 flex items-center gap-2
                      {{ request()->routeIs('admin.finance.withdrawals') ? 'text-indigo-600' : 'text-gray-400 hover:text-gray-600' }}">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.finance.withdrawals') ? 'bg-indigo-100' : 'bg-gray-100' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                </div>
                ถอนเงิน
                @if(request()->routeIs('admin.finance.withdrawals'))
                <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full"></span>
                @endif
            </a>
            <a href="{{ route('admin.finance.report') }}"
               class="relative px-6 py-4 text-sm font-semibold transition-all duration-200 flex items-center gap-2
                      {{ request()->routeIs('admin.finance.report') ? 'text-indigo-600' : 'text-gray-400 hover:text-gray-600' }}">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.finance.report') ? 'bg-indigo-100' : 'bg-gray-100' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                รายงาน
                @if(request()->routeIs('admin.finance.report'))
                <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full"></span>
                @endif
            </a>
        </div>
    </div>

    {{-- Summary Cards Row 1 (4 cards) --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Deposits --}}
        <div class="stat-card card-premium p-5 animate-fade-up delay-100">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">ยอดฝาก</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #d1fae5, #a7f3d0);">
                    <svg class="w-4.5 h-4.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800">฿{{ number_format($summary['deposits'] ?? 0, 2) }}</div>
            <div class="text-[10px] text-emerald-600 font-medium mt-1">ยอดรวมฝากเงิน</div>
        </div>

        {{-- Withdrawals --}}
        <div class="stat-card card-premium p-5 animate-fade-up delay-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">ยอดถอน</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ffedd5, #fed7aa);">
                    <svg class="w-4.5 h-4.5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800">฿{{ number_format($summary['withdrawals'] ?? 0, 2) }}</div>
            <div class="text-[10px] text-orange-600 font-medium mt-1">ยอดรวมถอนเงิน</div>
        </div>

        {{-- Bets --}}
        <div class="stat-card card-premium p-5 animate-fade-up delay-300">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">ยอดแทง</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #dbeafe, #bfdbfe);">
                    <svg class="w-4.5 h-4.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800">฿{{ number_format($summary['bets'] ?? 0, 2) }}</div>
            <div class="text-[10px] text-blue-600 font-medium mt-1">ยอดรวมการแทง</div>
        </div>

        {{-- Wins --}}
        <div class="stat-card card-premium p-5 animate-fade-up delay-400">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">ยอดจ่ายรางวัล</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #fce7f3, #fbcfe8);">
                    <svg class="w-4.5 h-4.5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800">฿{{ number_format($summary['wins'] ?? 0, 2) }}</div>
            <div class="text-[10px] text-pink-600 font-medium mt-1">จ่ายรางวัลทั้งหมด</div>
        </div>
    </div>

    {{-- Summary Cards Row 2 (3 cards) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Commissions --}}
        <div class="stat-card card-premium p-5 animate-fade-up delay-500">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">ค่าคอมมิชชั่น</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ede9fe, #ddd6fe);">
                    <svg class="w-4.5 h-4.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800">฿{{ number_format($summary['commissions'] ?? 0, 2) }}</div>
            <div class="text-[10px] text-purple-600 font-medium mt-1">รายได้จากคอมมิชชั่น</div>
        </div>

        {{-- Profit/Loss (Highlighted) --}}
        @php $profit = $summary['profit'] ?? 0; @endphp
        <div class="stat-card card-premium p-5 animate-fade-up delay-600 relative overflow-hidden">
            {{-- Subtle gradient overlay for emphasis --}}
            <div class="absolute inset-0 opacity-5" style="background: linear-gradient(135deg, {{ $profit >= 0 ? '#10b981' : '#ef4444' }}, transparent);"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">กำไร/ขาดทุน</span>
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                         style="background: linear-gradient(135deg, {{ $profit >= 0 ? '#d1fae5, #a7f3d0' : '#fee2e2, #fecaca' }});">
                        <svg class="w-4.5 h-4.5 {{ $profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                </div>
                <div class="text-3xl font-bold {{ $profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                    ฿{{ number_format(abs($profit), 2) }}
                </div>
                <div class="mt-1">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-semibold rounded-full uppercase tracking-wide {{ $profit >= 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $profit >= 0 ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                        {{ $profit >= 0 ? 'กำไร' : 'ขาดทุน' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Margin --}}
        <div class="stat-card card-premium p-5 animate-fade-up delay-700">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Margin</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe);">
                    <svg class="w-4.5 h-4.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800">{{ number_format($summary['margin'] ?? 0, 2) }}%</div>
            <div class="text-[10px] text-indigo-600 font-medium mt-1">อัตรากำไรสุทธิ</div>
        </div>
    </div>

    {{-- Date Filter --}}
    <div class="card-premium p-5 animate-fade-up delay-600">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe);">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-800">กรองข้อมูลตามช่วงเวลา</h3>
                <p class="text-xs text-gray-400">เลือกช่วงวันที่ที่ต้องการดูรายงาน</p>
            </div>
        </div>
        <form method="GET" action="{{ route('admin.finance.report') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[160px]">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2 block">จากวันที่</label>
                <input type="date" name="from" value="{{ $period['from'] ?? '' }}"
                       class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 outline-none transition-all bg-white">
            </div>
            <div class="flex-1 min-w-[160px]">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2 block">ถึงวันที่</label>
                <input type="date" name="to" value="{{ $period['to'] ?? '' }}"
                       class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 outline-none transition-all bg-white">
            </div>
            <button type="submit"
                    class="btn-premium text-white px-6 py-2.5 rounded-xl text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                ดูรายงาน
            </button>
        </form>
    </div>

    {{-- Daily Chart --}}
    <div class="card-premium p-6 animate-fade-up delay-700">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-sm font-bold text-gray-800">กราฟรายวัน</h3>
                <p class="text-xs text-gray-400 mt-0.5">เปรียบเทียบยอดแทง, จ่ายรางวัล และกำไรรายวัน</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 text-[10px] font-medium text-blue-600">
                    <span class="w-2.5 h-2.5 rounded-sm" style="background: rgba(59,130,246,0.6);"></span>
                    แทง
                </span>
                <span class="inline-flex items-center gap-1.5 text-[10px] font-medium text-red-600">
                    <span class="w-2.5 h-2.5 rounded-sm" style="background: rgba(239,68,68,0.6);"></span>
                    จ่ายรางวัล
                </span>
                <span class="inline-flex items-center gap-1.5 text-[10px] font-medium text-emerald-600">
                    <span class="w-2.5 h-2.5 rounded-sm" style="background: rgba(16,185,129,0.6);"></span>
                    กำไร
                </span>
            </div>
        </div>
        <div class="h-72">
            <canvas id="dailyChart"></canvas>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function reportPage() { return {}; }

document.addEventListener('DOMContentLoaded', function() {
    const dailyData = @json($dailyChart ?? []);
    if (dailyData.labels && dailyData.labels.length > 0) {
        new Chart(document.getElementById('dailyChart'), {
            type: 'bar',
            data: {
                labels: dailyData.labels,
                datasets: [
                    {
                        label: 'แทง',
                        data: dailyData.bets,
                        backgroundColor: 'rgba(99,102,241,0.7)',
                        hoverBackgroundColor: 'rgba(99,102,241,0.9)',
                        borderRadius: 6,
                        barThickness: 14
                    },
                    {
                        label: 'จ่ายรางวัล',
                        data: dailyData.wins,
                        backgroundColor: 'rgba(239,68,68,0.6)',
                        hoverBackgroundColor: 'rgba(239,68,68,0.8)',
                        borderRadius: 6,
                        barThickness: 14
                    },
                    {
                        label: 'กำไร',
                        data: dailyData.profit,
                        backgroundColor: 'rgba(16,185,129,0.6)',
                        hoverBackgroundColor: 'rgba(16,185,129,0.8)',
                        borderRadius: 6,
                        barThickness: 14
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 6,
                            font: { size: 11, family: 'Noto Sans Thai' },
                            padding: 16
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(99,102,241,0.06)' },
                        ticks: {
                            font: { size: 10, family: 'Noto Sans Thai' },
                            callback: v => '฿' + new Intl.NumberFormat('th-TH').format(v)
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10, family: 'Noto Sans Thai' } }
                    }
                }
            }
        });
    } else {
        const canvas = document.getElementById('dailyChart');
        if (canvas) {
            const parent = canvas.parentElement;
            parent.innerHTML = '<div class="flex flex-col items-center justify-center h-full"><div class="w-16 h-16 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl flex items-center justify-center mb-3"><svg class="w-8 h-8 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></div><span class="text-sm text-gray-400 font-medium">ไม่มีข้อมูลกราฟ</span><span class="text-xs text-gray-300 mt-1">ลองเลือกช่วงวันที่ที่มีข้อมูล</span></div>';
        }
    }
});
</script>
@endpush
