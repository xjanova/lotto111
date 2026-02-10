@extends('admin.layouts.app')
@section('title', 'แดชบอร์ด')
@section('page-title', 'แดชบอร์ด')
@section('page-subtitle', 'ภาพรวมระบบและข้อมูลสำคัญ')

@section('content')
<div x-data="dashboardPage()" x-init="init()" class="space-y-6">

    {{-- Hero Welcome Section --}}
    <div class="relative overflow-hidden rounded-2xl p-6 md:p-8" style="background: linear-gradient(135deg, #4f46e5, #7c3aed, #db2777);">
        {{-- Animated Blobs --}}
        <div class="absolute top-0 left-0 w-72 h-72 bg-white/10 rounded-full filter blur-3xl animate-blob"></div>
        <div class="absolute top-0 right-0 w-72 h-72 bg-purple-300/10 rounded-full filter blur-3xl animate-blob delay-200"></div>
        <div class="absolute bottom-0 left-1/3 w-72 h-72 bg-pink-300/10 rounded-full filter blur-3xl animate-blob delay-400"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl md:text-3xl font-bold text-white mb-1">
                    สวัสดี, {{ auth()->user()->name ?? 'Admin' }}
                </h2>
                <p class="text-white/70 text-sm">
                    {{ now()->locale('th')->translatedFormat('l j F Y') }} | ข้อมูลอัปเดตล่าสุด
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.finance.deposits') }}" class="px-4 py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white rounded-xl text-sm font-medium transition-all duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    อนุมัติรายการ
                </a>
                <a href="{{ route('admin.risk.dashboard') }}" class="px-4 py-2.5 bg-white text-indigo-700 rounded-xl text-sm font-bold transition-all duration-200 hover:shadow-lg hover:shadow-white/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Risk Control
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Cards Row --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        {{-- Members --}}
        <div class="stat-card card-premium p-4 animate-fade-up">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">สมาชิก</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #dbeafe, #bfdbfe);">
                    <svg class="w-4.5 h-4.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800" x-text="fmtNum(stats.total_members)">0</div>
            <div class="flex items-center gap-1 mt-1">
                <span class="inline-flex items-center gap-0.5 text-xs font-medium text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-full">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    +<span x-text="stats.new_members_today">0</span>
                </span>
                <span class="text-[10px] text-gray-400">วันนี้</span>
            </div>
        </div>

        {{-- Deposits Today --}}
        <div class="stat-card card-premium p-4 animate-fade-up delay-100">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">ฝากวันนี้</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #d1fae5, #a7f3d0);">
                    <svg class="w-4.5 h-4.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800">฿<span x-text="fmtMoney(stats.deposits_today)">0</span></div>
            <div class="text-[10px] text-gray-400 mt-1"><span x-text="stats.deposit_count_today">0</span> รายการ</div>
        </div>

        {{-- Withdrawals Today --}}
        <div class="stat-card card-premium p-4 animate-fade-up delay-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">ถอนวันนี้</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ffedd5, #fed7aa);">
                    <svg class="w-4.5 h-4.5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800">฿<span x-text="fmtMoney(stats.withdrawals_today)">0</span></div>
            <div class="text-[10px] text-gray-400 mt-1"><span x-text="stats.withdrawal_count_today">0</span> รายการ</div>
        </div>

        {{-- Bets Today --}}
        <div class="stat-card card-premium p-4 animate-fade-up delay-300">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">ยอดแทง</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ede9fe, #ddd6fe);">
                    <svg class="w-4.5 h-4.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800">฿<span x-text="fmtMoney(stats.bets_today)">0</span></div>
            <div class="text-[10px] text-gray-400 mt-1"><span x-text="stats.bet_count_today">0</span> โพย</div>
        </div>

        {{-- Profit/Loss --}}
        <div class="stat-card card-premium p-4 animate-fade-up delay-400">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">กำไร/ขาดทุน</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                     :style="stats.profit_today >= 0 ? 'background: linear-gradient(135deg, #d1fae5, #a7f3d0)' : 'background: linear-gradient(135deg, #fee2e2, #fecaca)'">
                    <svg class="w-4.5 h-4.5" :class="stats.profit_today >= 0 ? 'text-emerald-600' : 'text-red-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold" :class="stats.profit_today >= 0 ? 'text-emerald-600' : 'text-red-600'">
                ฿<span x-text="fmtMoney(Math.abs(stats.profit_today))">0</span>
            </div>
            <div class="mt-1">
                <span class="text-xs font-medium px-1.5 py-0.5 rounded-full"
                      :class="stats.profit_today >= 0 ? 'text-emerald-600 bg-emerald-50' : 'text-red-600 bg-red-50'"
                      x-text="stats.profit_today >= 0 ? 'กำไร' : 'ขาดทุน'"></span>
            </div>
        </div>

        {{-- Pending --}}
        <div class="stat-card card-premium p-4 animate-fade-up delay-500">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">รอดำเนินการ</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #fef3c7, #fde68a);">
                    <svg class="w-4.5 h-4.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800" x-text="stats.pending_withdrawals">0</div>
            <div class="text-[10px] text-amber-600 font-medium mt-1">รายการถอนรออนุมัติ</div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Revenue Chart (Main) --}}
        <div class="lg:col-span-2 card-premium p-6 animate-fade-up delay-200">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">รายรับ-รายจ่าย</h3>
                    <p class="text-xs text-gray-400 mt-0.5">ข้อมูลย้อนหลัง 7 วัน</p>
                </div>
                <div class="flex gap-1 bg-gray-100 p-1 rounded-xl">
                    <button @click="chartRange='7d'; updateRevenueChart()"
                            :class="chartRange==='7d' ? 'bg-white text-brand-600 shadow-sm' : 'text-gray-500'"
                            class="text-xs px-3 py-1.5 rounded-lg font-medium transition-all">7 วัน</button>
                    <button @click="chartRange='30d'; updateRevenueChart()"
                            :class="chartRange==='30d' ? 'bg-white text-brand-600 shadow-sm' : 'text-gray-500'"
                            class="text-xs px-3 py-1.5 rounded-lg font-medium transition-all">30 วัน</button>
                </div>
            </div>
            <div class="h-64"><canvas id="revenueChart"></canvas></div>
        </div>

        {{-- Lottery Type Pie --}}
        <div class="card-premium p-6 animate-fade-up delay-300">
            <div class="mb-5">
                <h3 class="text-sm font-bold text-gray-800">สัดส่วนยอดแทง</h3>
                <p class="text-xs text-gray-400 mt-0.5">ตามประเภทหวย (30 วัน)</p>
            </div>
            <div class="h-64 flex items-center justify-center"><canvas id="lotteryPieChart"></canvas></div>
        </div>
    </div>

    {{-- Second Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card-premium p-6 animate-fade-up delay-300">
            <div class="mb-5">
                <h3 class="text-sm font-bold text-gray-800">สมาชิกใหม่</h3>
                <p class="text-xs text-gray-400 mt-0.5">30 วันย้อนหลัง</p>
            </div>
            <div class="h-52"><canvas id="memberChart"></canvas></div>
        </div>

        <div class="card-premium p-6 animate-fade-up delay-400">
            <div class="mb-5">
                <h3 class="text-sm font-bold text-gray-800">เปรียบเทียบฝาก-ถอน</h3>
                <p class="text-xs text-gray-400 mt-0.5">7 วันย้อนหลัง</p>
            </div>
            <div class="h-52"><canvas id="depositWithdrawChart"></canvas></div>
        </div>
    </div>

    {{-- Quick Actions + Recent Activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Quick Actions --}}
        <div class="card-premium p-6 animate-fade-up delay-400">
            <h3 class="text-sm font-bold text-gray-800 mb-4">ทางลัด</h3>
            <div class="space-y-1.5">
                @php
                    $shortcuts = [
                        ['route' => 'admin.finance.deposits', 'label' => 'อนุมัติฝาก/ถอน', 'desc' => 'ตรวจสอบรายการรอดำเนินการ', 'color' => 'emerald', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['route' => 'admin.lottery.index', 'label' => 'จัดการหวย/รอบ', 'desc' => 'เปิด/ปิดรอบ, กรอกผล', 'color' => 'purple', 'icon' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
                        ['route' => 'admin.result-sources.index', 'label' => 'ผลหวย/Scraper', 'desc' => 'ดึงผลอัตโนมัติ, สลับ mode', 'color' => 'indigo', 'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4'],
                        ['route' => 'admin.settings.index', 'label' => 'ตั้งค่าระบบ', 'desc' => 'ค่าธรรมเนียม, โลโก้, ชื่อเว็บ', 'color' => 'gray', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                        ['route' => 'admin.risk.dashboard', 'label' => 'Risk Control', 'desc' => 'ควบคุมกำไร, จัดการความเสี่ยง', 'color' => 'rose', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                    ];
                @endphp
                @foreach($shortcuts as $s)
                <a href="{{ route($s['route']) }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-{{ $s['color'] }}-50/50 transition-all group">
                    <div class="w-10 h-10 bg-{{ $s['color'] }}-50 rounded-xl flex items-center justify-center group-hover:bg-{{ $s['color'] }}-100 transition-colors flex-shrink-0">
                        <svg class="w-5 h-5 text-{{ $s['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $s['icon'] }}"/></svg>
                    </div>
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-gray-700 group-hover:text-{{ $s['color'] }}-700 transition-colors">{{ $s['label'] }}</div>
                        <div class="text-[11px] text-gray-400 truncate">{{ $s['desc'] }}</div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>

        {{-- Recent Deposits --}}
        <div class="card-premium p-6 animate-fade-up delay-500">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-800">ฝากเงินล่าสุด</h3>
                <a href="{{ route('admin.finance.deposits') }}" class="text-xs text-brand-600 hover:text-brand-700 font-medium transition-colors">ดูทั้งหมด &rarr;</a>
            </div>
            <div class="space-y-3">
                <template x-for="d in recentDeposits" :key="d.id">
                    <div class="flex items-center justify-between py-2.5 border-b border-gray-50 last:border-0">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-xl flex items-center justify-center text-xs font-bold text-indigo-600" x-text="d.user?.substring(0,2) || 'U'"></div>
                            <div>
                                <div class="text-sm font-medium text-gray-700" x-text="d.user || '-'"></div>
                                <div class="text-[10px] text-gray-400" x-text="d.time"></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-bold text-emerald-600">+฿<span x-text="fmtMoney(d.amount)"></span></div>
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-medium"
                                  :class="d.status === 'approved' || d.status === 'credited' ? 'bg-emerald-50 text-emerald-600' : d.status === 'pending' ? 'bg-amber-50 text-amber-600' : 'bg-red-50 text-red-600'"
                                  x-text="d.status_text"></span>
                        </div>
                    </div>
                </template>
                <template x-if="recentDeposits.length === 0">
                    <div class="py-8 text-center">
                        <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        </div>
                        <span class="text-sm text-gray-400">ยังไม่มีรายการ</span>
                    </div>
                </template>
            </div>
        </div>

        {{-- Recent Bets --}}
        <div class="card-premium p-6 animate-fade-up delay-600">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-gray-800">แทงล่าสุด</h3>
                <a href="{{ route('admin.lottery.index') }}" class="text-xs text-brand-600 hover:text-brand-700 font-medium transition-colors">ดูทั้งหมด &rarr;</a>
            </div>
            <div class="space-y-3">
                <template x-for="b in recentBets" :key="b.id">
                    <div class="flex items-center justify-between py-2.5 border-b border-gray-50 last:border-0">
                        <div>
                            <div class="text-sm font-medium text-gray-700" x-text="b.user || '-'"></div>
                            <div class="text-[10px] text-gray-400" x-text="b.lottery_type + ' - ' + b.number"></div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-bold text-purple-600">฿<span x-text="fmtMoney(b.amount)"></span></div>
                            <div class="text-[10px] text-gray-400" x-text="b.time"></div>
                        </div>
                    </div>
                </template>
                <template x-if="recentBets.length === 0">
                    <div class="py-8 text-center">
                        <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        </div>
                        <span class="text-sm text-gray-400">ยังไม่มีรายการ</span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Active Rounds --}}
    <div class="card-premium p-6 animate-fade-up delay-600">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-sm font-bold text-gray-800">รอบที่เปิดอยู่</h3>
                <p class="text-xs text-gray-400 mt-0.5">รอบหวยที่กำลังดำเนินการ</p>
            </div>
            <a href="{{ route('admin.lottery.index') }}" class="btn-premium text-white text-xs px-4 py-2 rounded-xl font-medium flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0"/></svg>
                จัดการรอบ
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-xs text-gray-400 border-b border-gray-100 uppercase tracking-wide">
                    <th class="pb-3 font-semibold">ประเภท</th>
                    <th class="pb-3 font-semibold">รหัสรอบ</th>
                    <th class="pb-3 font-semibold">สถานะ</th>
                    <th class="pb-3 font-semibold">ปิดรับ</th>
                    <th class="pb-3 font-semibold">ยอดแทง</th>
                </tr></thead>
                <tbody>
                    <template x-for="r in activeRounds" :key="r.id">
                        <tr class="border-b border-gray-50 table-row-hover transition-colors">
                            <td class="py-3 font-semibold text-gray-700" x-text="r.type"></td>
                            <td class="py-3 text-gray-500 font-mono text-xs" x-text="r.code"></td>
                            <td class="py-3">
                                <span class="px-2.5 py-1 text-[10px] rounded-full font-semibold uppercase tracking-wide"
                                      :class="r.status === 'open' ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600'"
                                      x-text="r.status === 'open' ? 'เปิด' : r.status"></span>
                            </td>
                            <td class="py-3 text-gray-500 text-xs" x-text="r.close_at"></td>
                            <td class="py-3 font-bold text-gray-700">฿<span x-text="fmtMoney(r.total_bets)"></span></td>
                        </tr>
                    </template>
                    <template x-if="activeRounds.length === 0">
                        <tr><td colspan="5" class="py-8 text-center text-gray-400 text-sm">ไม่มีรอบที่เปิดอยู่</td></tr>
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
            const data = @json($chartData['revenue'] ?? []);
            this.revenueChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels || [],
                    datasets: [
                        { label: 'ฝาก', data: data.deposits || [], borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.08)', fill: true, tension: 0.4, borderWidth: 2.5, pointRadius: 4, pointBackgroundColor: '#10b981' },
                        { label: 'ถอน', data: data.withdrawals || [], borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.08)', fill: true, tension: 0.4, borderWidth: 2.5, pointRadius: 4, pointBackgroundColor: '#f97316' },
                        { label: 'กำไร', data: data.profit || [], borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.05)', fill: false, tension: 0.4, borderWidth: 2, pointRadius: 3, borderDash: [5,5], pointBackgroundColor: '#6366f1' },
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false, interaction: { intersect: false, mode: 'index' }, plugins: { legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 6, font: { size: 11, family: 'Noto Sans Thai' }, padding: 16 } } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(99,102,241,0.06)' }, ticks: { font: { size: 10 }, callback: v => '฿' + (v/1000) + 'k' } }, x: { grid: { display: false }, ticks: { font: { size: 10 } } } } }
            });
        },

        initMemberChart() {
            const ctx = document.getElementById('memberChart');
            if (!ctx) return;
            const data = @json($chartData['members'] ?? []);
            new Chart(ctx, {
                type: 'bar',
                data: { labels: data.labels || [], datasets: [{ label: 'สมาชิกใหม่', data: data.data || [], backgroundColor: 'rgba(99,102,241,0.7)', hoverBackgroundColor: 'rgba(99,102,241,0.9)', borderRadius: 6, barThickness: 10 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(99,102,241,0.06)' }, ticks: { font: { size: 10 }, stepSize: 1 } }, x: { grid: { display: false }, ticks: { font: { size: 8 }, maxRotation: 45 } } } }
            });
        },

        initDepositWithdrawChart() {
            const ctx = document.getElementById('depositWithdrawChart');
            if (!ctx) return;
            const data = @json($chartData['deposit_withdraw'] ?? []);
            new Chart(ctx, {
                type: 'bar',
                data: { labels: data.labels || [], datasets: [
                    { label: 'ฝาก', data: data.deposits || [], backgroundColor: 'rgba(16,185,129,0.7)', hoverBackgroundColor: 'rgba(16,185,129,0.9)', borderRadius: 6, barThickness: 14 },
                    { label: 'ถอน', data: data.withdrawals || [], backgroundColor: 'rgba(249,115,22,0.7)', hoverBackgroundColor: 'rgba(249,115,22,0.9)', borderRadius: 6, barThickness: 14 },
                ]},
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 6, font: { size: 11, family: 'Noto Sans Thai' } } } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(99,102,241,0.06)' }, ticks: { font: { size: 10 }, callback: v => '฿' + (v/1000) + 'k' } }, x: { grid: { display: false }, ticks: { font: { size: 10 } } } } }
            });
        },

        initLotteryPieChart() {
            const ctx = document.getElementById('lotteryPieChart');
            if (!ctx) return;
            const data = @json($chartData['lottery_types'] ?? []);
            new Chart(ctx, {
                type: 'doughnut',
                data: { labels: data.labels || [], datasets: [{ data: data.data || [], backgroundColor: (data.colors && data.colors.length) ? data.colors : ['#6366f1','#10b981','#f97316','#8b5cf6','#ec4899','#14b8a6','#f59e0b','#ef4444'] }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, font: { size: 11, family: 'Noto Sans Thai' }, padding: 14 } } }, cutout: '68%' }
            });
        },

        updateRevenueChart() {
            // Chart range toggle - data refresh would be via API
        },

        fmtNum(n) { return window.fmtNum(n); },
        fmtMoney(n) { return window.fmtMoney(n); },
    }
}
</script>
@endpush
