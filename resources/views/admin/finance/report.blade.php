@extends('admin.layouts.app')
@section('title', 'รายงานการเงิน')
@section('page-title', 'จัดการการเงิน')
@section('breadcrumb') <span class="text-gray-700">การเงิน</span> @endsection

@section('content')
<div x-data="reportPage()" class="space-y-4 animate-fade-in">
    {{-- Tabs --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="flex border-b border-gray-100">
            <a href="{{ route('admin.finance.deposits') }}" class="px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">ฝากเงิน</a>
            <a href="{{ route('admin.finance.withdrawals') }}" class="px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">ถอนเงิน</a>
            <a href="{{ route('admin.finance.report') }}" class="px-6 py-3 text-sm font-medium border-b-2 border-brand-600 text-brand-600">รายงาน</a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <div class="text-xs text-green-500 mb-1">ยอดฝาก</div>
            <div class="text-xl font-bold text-green-600">฿{{ number_format($summary['deposits'] ?? 0, 2) }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <div class="text-xs text-orange-500 mb-1">ยอดถอน</div>
            <div class="text-xl font-bold text-orange-600">฿{{ number_format($summary['withdrawals'] ?? 0, 2) }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <div class="text-xs text-blue-500 mb-1">ยอดแทง</div>
            <div class="text-xl font-bold text-blue-600">฿{{ number_format($summary['bets'] ?? 0, 2) }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <div class="text-xs text-gray-500 mb-1">ยอดจ่ายรางวัล</div>
            <div class="text-xl font-bold text-gray-700">฿{{ number_format($summary['wins'] ?? 0, 2) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <div class="text-xs text-purple-500 mb-1">ค่าคอมมิชชั่น</div>
            <div class="text-xl font-bold text-purple-600">฿{{ number_format($summary['commissions'] ?? 0, 2) }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <div class="text-xs mb-1 {{ ($summary['profit'] ?? 0) >= 0 ? 'text-green-500' : 'text-red-500' }}">กำไร/ขาดทุน</div>
            <div class="text-2xl font-bold {{ ($summary['profit'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">฿{{ number_format($summary['profit'] ?? 0, 2) }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <div class="text-xs text-gray-500 mb-1">Margin</div>
            <div class="text-xl font-bold text-gray-700">{{ number_format($summary['margin'] ?? 0, 2) }}%</div>
        </div>
    </div>

    {{-- Date Filter --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <form method="GET" action="{{ route('admin.finance.report') }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs text-gray-500 mb-1 block">จาก</label>
                <input type="date" name="from" value="{{ $period['from'] ?? '' }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
            </div>
            <div>
                <label class="text-xs text-gray-500 mb-1 block">ถึง</label>
                <input type="date" name="to" value="{{ $period['to'] ?? '' }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-brand-600 text-white rounded-lg text-sm hover:bg-brand-700">ดูรายงาน</button>
        </form>
    </div>

    {{-- Daily Chart --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-4">รายวัน</h3>
        <canvas id="dailyChart" height="120"></canvas>
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
                    { label: 'แทง', data: dailyData.bets, backgroundColor: 'rgba(59,130,246,0.6)' },
                    { label: 'จ่ายรางวัล', data: dailyData.wins, backgroundColor: 'rgba(239,68,68,0.6)' },
                    { label: 'กำไร', data: dailyData.profit, backgroundColor: 'rgba(16,185,129,0.6)' },
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
        });
    }
});
</script>
@endpush
