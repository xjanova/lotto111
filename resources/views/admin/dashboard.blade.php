@extends('admin.layouts.app')
@section('title', 'แดชบอร์ด')
@section('page-title', 'แดชบอร์ด')

@section('content')
<div x-data="dashboardPage()" x-init="init()" class="space-y-6 animate-fade-in">

    {{-- Stats Cards Row 1 --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="stat-card bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-gray-500">สมาชิกทั้งหมด</span>
                <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800" x-text="fmtNum(stats.total_members)">0</div>
            <div class="text-xs text-green-500 mt-1">+<span x-text="stats.new_members_today">0</span> วันนี้</div>
        </div>

        <div class="stat-card bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-gray-500">ฝากวันนี้</span>
                <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800">฿<span x-text="fmtMoney(stats.deposits_today)">0</span></div>
            <div class="text-xs text-gray-400"><span x-text="stats.deposit_count_today">0</span> รายการ</div>
        </div>

        <div class="stat-card bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-gray-500">ถอนวันนี้</span>
                <div class="w-8 h-8 bg-orange-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800">฿<span x-text="fmtMoney(stats.withdrawals_today)">0</span></div>
            <div class="text-xs text-gray-400"><span x-text="stats.withdrawal_count_today">0</span> รายการ</div>
        </div>

        <div class="stat-card bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-gray-500">ยอดแทงวันนี้</span>
                <div class="w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800">฿<span x-text="fmtMoney(stats.bets_today)">0</span></div>
            <div class="text-xs text-gray-400"><span x-text="stats.bet_count_today">0</span> โพย</div>
        </div>

        <div class="stat-card bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-gray-500">กำไร/ขาดทุน</span>
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="stats.profit_today >= 0 ? 'bg-green-50' : 'bg-red-50'">
                    <svg class="w-4 h-4" :class="stats.profit_today >= 0 ? 'text-green-500' : 'text-red-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold" :class="stats.profit_today >= 0 ? 'text-green-600' : 'text-red-600'">
                ฿<span x-text="fmtMoney(Math.abs(stats.profit_today))">0</span>
            </div>
            <div class="text-xs" :class="stats.profit_today >= 0 ? 'text-green-500' : 'text-red-500'" x-text="stats.profit_today >= 0 ? 'กำไร' : 'ขาดทุน'"></div>
        </div>

        <div class="stat-card bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-medium text-gray-500">รอดำเนินการ</span>
                <div class="w-8 h-8 bg-yellow-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800" x-text="stats.pending_withdrawals">0</div>
            <div class="text-xs text-yellow-500">รายการถอนรออนุมัติ</div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Revenue Chart (Main) --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-800">รายรับ-รายจ่าย 7 วันย้อนหลัง</h3>
                <div class="flex gap-2">
                    <button @click="chartRange='7d'; updateRevenueChart()"
                            :class="chartRange==='7d' ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600'"
                            class="text-xs px-3 py-1 rounded-full transition-colors">7 วัน</button>
                    <button @click="chartRange='30d'; updateRevenueChart()"
                            :class="chartRange==='30d' ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600'"
                            class="text-xs px-3 py-1 rounded-full transition-colors">30 วัน</button>
                </div>
            </div>
            <div class="h-64"><canvas id="revenueChart"></canvas></div>
        </div>

        {{-- Lottery Type Pie --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">สัดส่วนยอดแทงตามประเภท</h3>
            <div class="h-64 flex items-center justify-center"><canvas id="lotteryPieChart"></canvas></div>
        </div>
    </div>

    {{-- Second Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Member Growth --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">สมาชิกใหม่ 30 วัน</h3>
            <div class="h-52"><canvas id="memberChart"></canvas></div>
        </div>

        {{-- Deposit vs Withdrawal --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">เปรียบเทียบฝาก-ถอน</h3>
            <div class="h-52"><canvas id="depositWithdrawChart"></canvas></div>
        </div>
    </div>

    {{-- Quick Actions + Recent Activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Quick Actions --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-800 mb-4">ทางลัด</h3>
            <div class="space-y-2">
                <a href="{{ route('admin.finance.deposits') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                    <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center group-hover:bg-green-100 transition-colors">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-800">อนุมัติฝาก/ถอน</div>
                        <div class="text-xs text-gray-400">ตรวจสอบรายการรอดำเนินการ</div>
                    </div>
                </a>
                <a href="{{ route('admin.lottery.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                    <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center group-hover:bg-purple-100 transition-colors">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-800">จัดการหวย/รอบ</div>
                        <div class="text-xs text-gray-400">เปิด/ปิดรอบ, กรอกผล</div>
                    </div>
                </a>
                <a href="{{ route('admin.result-sources.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                    <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center group-hover:bg-indigo-100 transition-colors">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-800">ผลหวย/Scraper</div>
                        <div class="text-xs text-gray-400">ดึงผลอัตโนมัติ, สลับ mode</div>
                    </div>
                </a>
                <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center group-hover:bg-gray-200 transition-colors">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-800">ตั้งค่าระบบ</div>
                        <div class="text-xs text-gray-400">ค่าธรรมเนียม, โลโก้, ชื่อเว็บ</div>
                    </div>
                </a>
                <a href="{{ route('admin.risk.dashboard') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition-colors group">
                    <div class="w-10 h-10 bg-red-50 rounded-lg flex items-center justify-center group-hover:bg-red-100 transition-colors">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-800">Risk Control</div>
                        <div class="text-xs text-gray-400">ควบคุมกำไร, จัดการความเสี่ยง</div>
                    </div>
                </a>
            </div>
        </div>

        {{-- Recent Deposits --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-800">ฝากเงินล่าสุด</h3>
                <a href="{{ route('admin.finance.deposits') }}" class="text-xs text-brand-600 hover:underline">ดูทั้งหมด</a>
            </div>
            <div class="space-y-3">
                <template x-for="d in recentDeposits" :key="d.id">
                    <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-xs font-medium text-gray-600" x-text="d.user?.substring(0,2) || 'U'"></div>
                            <div>
                                <div class="text-sm font-medium text-gray-700" x-text="d.user || '-'"></div>
                                <div class="text-xs text-gray-400" x-text="d.time"></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-green-600">+฿<span x-text="fmtMoney(d.amount)"></span></div>
                            <span class="text-xs px-1.5 py-0.5 rounded-full"
                                  :class="d.status === 'approved' ? 'bg-green-50 text-green-600' : d.status === 'pending' ? 'bg-yellow-50 text-yellow-600' : 'bg-red-50 text-red-600'"
                                  x-text="d.status_text"></span>
                        </div>
                    </div>
                </template>
                <template x-if="recentDeposits.length === 0">
                    <div class="py-6 text-center text-gray-400 text-sm">ยังไม่มีรายการ</div>
                </template>
            </div>
        </div>

        {{-- Recent Bets --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-800">แทงล่าสุด</h3>
                <a href="{{ route('admin.lottery.index') }}" class="text-xs text-brand-600 hover:underline">ดูทั้งหมด</a>
            </div>
            <div class="space-y-3">
                <template x-for="b in recentBets" :key="b.id">
                    <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                        <div>
                            <div class="text-sm font-medium text-gray-700" x-text="b.user || '-'"></div>
                            <div class="text-xs text-gray-400" x-text="b.lottery_type + ' - ' + b.number"></div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-purple-600">฿<span x-text="fmtMoney(b.amount)"></span></div>
                            <div class="text-xs text-gray-400" x-text="b.time"></div>
                        </div>
                    </div>
                </template>
                <template x-if="recentBets.length === 0">
                    <div class="py-6 text-center text-gray-400 text-sm">ยังไม่มีรายการ</div>
                </template>
            </div>
        </div>
    </div>

    {{-- Active Rounds --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-800">รอบที่เปิดอยู่</h3>
            <a href="{{ route('admin.lottery.rounds') }}" class="text-xs text-brand-600 hover:underline">จัดการรอบ</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-xs text-gray-400 border-b border-gray-100">
                    <th class="pb-2 font-medium">ประเภท</th>
                    <th class="pb-2 font-medium">รหัสรอบ</th>
                    <th class="pb-2 font-medium">สถานะ</th>
                    <th class="pb-2 font-medium">ปิดรับ</th>
                    <th class="pb-2 font-medium">ยอดแทง</th>
                </tr></thead>
                <tbody>
                    <template x-for="r in activeRounds" :key="r.id">
                        <tr class="border-b border-gray-50 hover:bg-gray-50">
                            <td class="py-2.5 font-medium text-gray-700" x-text="r.type"></td>
                            <td class="py-2.5 text-gray-600" x-text="r.code"></td>
                            <td class="py-2.5">
                                <span class="px-2 py-0.5 text-xs rounded-full"
                                      :class="r.status === 'open' ? 'bg-green-50 text-green-600' : 'bg-yellow-50 text-yellow-600'"
                                      x-text="r.status === 'open' ? 'เปิด' : r.status"></span>
                            </td>
                            <td class="py-2.5 text-gray-500" x-text="r.close_at"></td>
                            <td class="py-2.5 font-medium text-gray-700">฿<span x-text="fmtMoney(r.total_bets)"></span></td>
                        </tr>
                    </template>
                    <template x-if="activeRounds.length === 0">
                        <tr><td colspan="5" class="py-6 text-center text-gray-400">ไม่มีรอบที่เปิดอยู่</td></tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function dashboardPage() {
    return {
        chartRange: '7d',
        stats: {
            total_members: {{ $stats['total_members'] ?? 0 }},
            new_members_today: {{ $stats['new_members_today'] ?? 0 }},
            deposits_today: {{ $stats['deposits_today'] ?? 0 }},
            deposit_count_today: {{ $stats['deposit_count_today'] ?? 0 }},
            withdrawals_today: {{ $stats['withdrawals_today'] ?? 0 }},
            withdrawal_count_today: {{ $stats['withdrawal_count_today'] ?? 0 }},
            bets_today: {{ $stats['bets_today'] ?? 0 }},
            bet_count_today: {{ $stats['bet_count_today'] ?? 0 }},
            profit_today: {{ $stats['profit_today'] ?? 0 }},
            pending_withdrawals: {{ $stats['pending_withdrawals'] ?? 0 }},
        },
        recentDeposits: @json($recentDeposits ?? []),
        recentBets: @json($recentBets ?? []),
        activeRounds: @json($activeRounds ?? []),
        revenueChart: null,
        memberChart: null,
        depositWithdrawChart: null,
        lotteryPieChart: null,

        init() {
            this.$nextTick(() => {
                this.initRevenueChart();
                this.initMemberChart();
                this.initDepositWithdrawChart();
                this.initLotteryPieChart();
            });
        },

        initRevenueChart() {
            const ctx = document.getElementById('revenueChart');
            if (!ctx) return;
            const data = @json($chartData['revenue'] ?? ['labels'=>[],'deposits'=>[],'withdrawals'=>[],'profit'=>[]]);
            this.revenueChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        { label: 'ฝาก', data: data.deposits, borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,0.1)', fill: true, tension: 0.4, borderWidth: 2, pointRadius: 3 },
                        { label: 'ถอน', data: data.withdrawals, borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.1)', fill: true, tension: 0.4, borderWidth: 2, pointRadius: 3 },
                        { label: 'กำไร', data: data.profit, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.05)', fill: false, tension: 0.4, borderWidth: 2, pointRadius: 3, borderDash: [5,5] },
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 6, font: { size: 11 } } } }, scales: { y: { beginAtZero: true, ticks: { font: { size: 10 }, callback: v => '฿' + (v/1000) + 'k' } }, x: { ticks: { font: { size: 10 } } } } }
            });
        },

        initMemberChart() {
            const ctx = document.getElementById('memberChart');
            if (!ctx) return;
            const data = @json($chartData['members'] ?? ['labels'=>[],'data'=>[]]);
            new Chart(ctx, {
                type: 'bar',
                data: { labels: data.labels, datasets: [{ label: 'สมาชิกใหม่', data: data.data, backgroundColor: 'rgba(99,102,241,0.7)', borderRadius: 4, barThickness: 12 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { font: { size: 10 }, stepSize: 1 } }, x: { ticks: { font: { size: 9 }, maxRotation: 45 } } } }
            });
        },

        initDepositWithdrawChart() {
            const ctx = document.getElementById('depositWithdrawChart');
            if (!ctx) return;
            const data = @json($chartData['deposit_withdraw'] ?? ['labels'=>[],'deposits'=>[],'withdrawals'=>[]]);
            new Chart(ctx, {
                type: 'bar',
                data: { labels: data.labels, datasets: [
                    { label: 'ฝาก', data: data.deposits, backgroundColor: 'rgba(34,197,94,0.7)', borderRadius: 4, barThickness: 14 },
                    { label: 'ถอน', data: data.withdrawals, backgroundColor: 'rgba(249,115,22,0.7)', borderRadius: 4, barThickness: 14 },
                ]},
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 6, font: { size: 11 } } } }, scales: { y: { beginAtZero: true, ticks: { font: { size: 10 }, callback: v => '฿' + (v/1000) + 'k' } }, x: { ticks: { font: { size: 9 } } } } }
            });
        },

        initLotteryPieChart() {
            const ctx = document.getElementById('lotteryPieChart');
            if (!ctx) return;
            const data = @json($chartData['lottery_types'] ?? ['labels'=>[],'data'=>[],'colors'=>[]]);
            new Chart(ctx, {
                type: 'doughnut',
                data: { labels: data.labels, datasets: [{ data: data.data, backgroundColor: data.colors.length ? data.colors : ['#3b82f6','#22c55e','#f97316','#8b5cf6','#ec4899','#14b8a6','#f59e0b','#ef4444'] }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, font: { size: 11 }, padding: 12 } } }, cutout: '65%' }
            });
        },

        updateRevenueChart() {
            // Would reload data via API for different range
        },

        fmtNum(n) { return new Intl.NumberFormat('th-TH').format(n || 0); },
        fmtMoney(n) { return new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n || 0); },
    }
}
</script>
@endpush
