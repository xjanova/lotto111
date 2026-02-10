@extends('admin.layouts.app')
@section('title', 'จัดการหวย')
@section('page-title', 'จัดการหวย/รอบ/อัตราจ่าย')
@section('breadcrumb') <span class="text-gray-700">หวย</span> @endsection

@section('content')
<div x-data="lotteryPage()" class="space-y-6">

    {{-- Hero Banner --}}
    <div class="relative overflow-hidden rounded-2xl p-6 md:p-8 animate-fade-up" style="background: linear-gradient(135deg, #4f46e5, #7c3aed, #a855f7);">
        <div class="absolute top-0 left-0 w-72 h-72 bg-white/10 rounded-full filter blur-3xl animate-blob"></div>
        <div class="absolute bottom-0 right-0 w-72 h-72 bg-purple-300/10 rounded-full filter blur-3xl animate-blob delay-200"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-white mb-1">จัดการหวย</h2>
                <p class="text-white/70 text-sm">จัดการประเภทหวย, รอบหวย, อัตราจ่าย และอั้นเลข</p>
            </div>
            <button @click="showCreateRound = true" class="px-5 py-2.5 bg-white text-indigo-700 rounded-xl text-sm font-bold transition-all duration-200 hover:shadow-lg hover:shadow-white/20 flex items-center gap-2 self-start">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                สร้างรอบใหม่
            </button>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="card-premium animate-fade-up delay-100">
        <div class="flex border-b border-indigo-100 overflow-x-auto">
            @php
                $tabs = [
                    ['id' => 'overview', 'label' => 'ภาพรวม', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                    ['id' => 'types', 'label' => 'ประเภทหวย', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z'],
                    ['id' => 'rounds', 'label' => 'รอบหวย', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['id' => 'rates', 'label' => 'อัตราจ่าย', 'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                    ['id' => 'limits', 'label' => 'อั้นเลข/จำกัด', 'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
                ];
            @endphp
            @foreach($tabs as $tab)
            <button @click="activeTab = '{{ $tab['id'] }}'"
               :class="activeTab === '{{ $tab['id'] }}' ? 'border-indigo-600 text-indigo-600 bg-indigo-50/50' : 'border-transparent text-gray-400 hover:text-indigo-600 hover:bg-indigo-50/30'"
               class="flex items-center gap-2 px-5 py-3.5 text-sm font-medium border-b-2 whitespace-nowrap transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/></svg>
                {{ $tab['label'] }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- ═══════════════════════════ TAB: Overview ═══════════════════════════ --}}
    <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="space-y-6">

        {{-- Lottery Types Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($lotteryTypes ?? [] as $idx => $type)
            <div class="card-premium p-5 animate-fade-up delay-{{ ($idx % 4 + 1) * 100 }} group">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe);">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800 group-hover:text-indigo-700 transition-colors">{{ $type['name'] ?? '' }}</h3>
                            <div class="text-xs text-gray-400">{{ $type['category'] ?? '' }} | {{ $type['slug'] ?? '' }}</div>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 text-[10px] font-semibold rounded-full uppercase tracking-wide
                        {{ ($type['is_active'] ?? false) ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
                        {{ ($type['is_active'] ?? false) ? 'เปิด' : 'ปิด' }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-xl p-3 text-center">
                        <div class="text-[10px] text-gray-400 uppercase tracking-wide font-semibold mb-1">รอบเปิด</div>
                        <div class="text-xl font-bold text-indigo-600">{{ $type['open_rounds'] ?? 0 }}</div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-3 text-center">
                        <div class="text-[10px] text-gray-400 uppercase tracking-wide font-semibold mb-1">ยอดแทงวันนี้</div>
                        <div class="text-xl font-bold text-purple-600">฿{{ number_format($type['today_bets'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Active Rounds Table --}}
        <div class="card-premium animate-fade-up delay-300">
            <div class="p-5 border-b border-indigo-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">รอบที่กำลังดำเนินการ</h3>
                    <p class="text-xs text-gray-400 mt-0.5">รอบหวยทั้งหมดที่กำลังเปิดรับแทง</p>
                </div>
                <button @click="showCreateRound = true" class="btn-premium text-white text-xs px-4 py-2 rounded-xl font-medium flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    สร้างรอบ
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-400 border-b border-indigo-50 uppercase tracking-wide">
                            <th class="px-5 py-3.5 font-semibold">ประเภท</th>
                            <th class="px-5 py-3.5 font-semibold">รหัสรอบ</th>
                            <th class="px-5 py-3.5 font-semibold">สถานะ</th>
                            <th class="px-5 py-3.5 font-semibold">เปิดรับ</th>
                            <th class="px-5 py-3.5 font-semibold">ปิดรับ</th>
                            <th class="px-5 py-3.5 font-semibold">ผลรางวัล</th>
                            <th class="px-5 py-3.5 font-semibold">ยอดแทง</th>
                            <th class="px-5 py-3.5 font-semibold">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rounds ?? [] as $round)
                        <tr class="border-b border-gray-50 table-row-hover transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe);">
                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                    </div>
                                    <span class="font-semibold text-gray-700">{{ $round['type_name'] ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-gray-500 font-mono text-xs">{{ $round['round_code'] ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                @php $st = $round['status'] ?? ''; @endphp
                                <span class="px-2.5 py-1 text-[10px] rounded-full font-semibold uppercase tracking-wide
                                    {{ $st === 'open' ? 'bg-emerald-50 text-emerald-600' : ($st === 'closed' ? 'bg-amber-50 text-amber-600' : ($st === 'resulted' ? 'bg-indigo-50 text-indigo-600' : 'bg-gray-100 text-gray-400')) }}">
                                    {{ $st === 'open' ? 'เปิดรับ' : ($st === 'closed' ? 'ปิดรับ' : ($st === 'resulted' ? 'ออกผลแล้ว' : $st)) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-gray-400">{{ $round['open_at'] ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-xs text-gray-400">{{ $round['close_at'] ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                @if(!empty($round['result']))
                                    <span class="font-mono font-bold gradient-text text-sm">{{ $round['result'] }}</span>
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 font-bold text-gray-700">฿{{ number_format($round['total_bets'] ?? 0, 2) }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex gap-1.5">
                                    @if($st === 'closed' && empty($round['result']))
                                    <button onclick="document.getElementById('result-modal-{{ $round['id'] }}').classList.remove('hidden')"
                                            class="btn-premium text-white text-[11px] px-3 py-1.5 rounded-lg font-medium flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        กรอกผล
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-3" style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe);">
                                        <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <span class="text-sm text-gray-400 font-medium">ไม่มีรอบที่กำลังดำเนินการ</span>
                                    <p class="text-xs text-gray-300 mt-1">คลิก "สร้างรอบใหม่" เพื่อเริ่มต้น</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════ TAB: Types ═══════════════════════════ --}}
    <div x-show="activeTab === 'types'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="space-y-6">
        <div class="card-premium">
            <div class="p-5 border-b border-indigo-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">ประเภทหวยทั้งหมด</h3>
                    <p class="text-xs text-gray-400 mt-0.5">จัดการเปิด/ปิด และตั้งค่าประเภทหวย</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-400 border-b border-indigo-50 uppercase tracking-wide">
                            <th class="px-5 py-3.5 font-semibold">ID</th>
                            <th class="px-5 py-3.5 font-semibold">ชื่อ</th>
                            <th class="px-5 py-3.5 font-semibold">หมวดหมู่</th>
                            <th class="px-5 py-3.5 font-semibold">Slug</th>
                            <th class="px-5 py-3.5 font-semibold">รอบเปิด</th>
                            <th class="px-5 py-3.5 font-semibold">ยอดวันนี้</th>
                            <th class="px-5 py-3.5 font-semibold">สถานะ</th>
                            <th class="px-5 py-3.5 font-semibold">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lotteryTypes ?? [] as $type)
                        <tr class="border-b border-gray-50 table-row-hover transition-colors">
                            <td class="px-5 py-3.5 text-gray-400 font-mono text-xs">{{ $type['id'] }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe);">
                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                                    </div>
                                    <span class="font-semibold text-gray-700">{{ $type['name'] }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-gray-500 text-xs">{{ $type['category'] ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-gray-400 font-mono text-xs">{{ $type['slug'] ?? '-' }}</td>
                            <td class="px-5 py-3.5 font-semibold text-indigo-600">{{ $type['open_rounds'] ?? 0 }}</td>
                            <td class="px-5 py-3.5 font-bold text-gray-700">฿{{ number_format($type['today_bets'] ?? 0) }}</td>
                            <td class="px-5 py-3.5">
                                <span class="px-2.5 py-1 text-[10px] rounded-full font-semibold uppercase tracking-wide
                                    {{ ($type['is_active'] ?? false) ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
                                    {{ ($type['is_active'] ?? false) ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <button @click="toggleType({{ $type['id'] }}, {{ ($type['is_active'] ?? false) ? 'true' : 'false' }})"
                                        class="text-xs px-3 py-1.5 rounded-lg font-medium transition-all {{ ($type['is_active'] ?? false) ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }}">
                                    {{ ($type['is_active'] ?? false) ? 'ปิด' : 'เปิด' }}
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-gray-400">ไม่มีประเภทหวย</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════ TAB: Rounds ═══════════════════════════ --}}
    <div x-show="activeTab === 'rounds'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="space-y-6">
        <div class="card-premium">
            <div class="p-5 border-b border-indigo-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">รอบหวยทั้งหมด</h3>
                    <p class="text-xs text-gray-400 mt-0.5">จัดการรอบหวย เปิด/ปิดรับแทง กรอกผล</p>
                </div>
                <button @click="showCreateRound = true" class="btn-premium text-white text-xs px-4 py-2 rounded-xl font-medium flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    สร้างรอบใหม่
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-400 border-b border-indigo-50 uppercase tracking-wide">
                            <th class="px-5 py-3.5 font-semibold">ประเภท</th>
                            <th class="px-5 py-3.5 font-semibold">รหัสรอบ</th>
                            <th class="px-5 py-3.5 font-semibold">สถานะ</th>
                            <th class="px-5 py-3.5 font-semibold">เปิดรับ</th>
                            <th class="px-5 py-3.5 font-semibold">ปิดรับ</th>
                            <th class="px-5 py-3.5 font-semibold">ผลรางวัล</th>
                            <th class="px-5 py-3.5 font-semibold">ยอดแทง</th>
                            <th class="px-5 py-3.5 font-semibold">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rounds ?? [] as $round)
                        <tr class="border-b border-gray-50 table-row-hover transition-colors">
                            <td class="px-5 py-3.5 font-semibold text-gray-700">{{ $round['type_name'] ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-gray-500 font-mono text-xs">{{ $round['round_code'] ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                @php $st = $round['status'] ?? ''; @endphp
                                <span class="px-2.5 py-1 text-[10px] rounded-full font-semibold uppercase tracking-wide
                                    {{ $st === 'open' ? 'bg-emerald-50 text-emerald-600' : ($st === 'closed' ? 'bg-amber-50 text-amber-600' : ($st === 'resulted' ? 'bg-indigo-50 text-indigo-600' : ($st === 'upcoming' ? 'bg-blue-50 text-blue-600' : 'bg-gray-100 text-gray-400'))) }}">
                                    {{ $st === 'open' ? 'เปิดรับ' : ($st === 'closed' ? 'ปิดรับ' : ($st === 'resulted' ? 'ออกผลแล้ว' : ($st === 'upcoming' ? 'รอเปิด' : $st))) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-gray-400">{{ $round['open_at'] ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-xs text-gray-400">{{ $round['close_at'] ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                @if(!empty($round['result']))
                                    <span class="font-mono font-bold gradient-text text-sm">{{ $round['result'] }}</span>
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 font-bold text-gray-700">฿{{ number_format($round['total_bets'] ?? 0, 2) }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex gap-1.5">
                                    @if($st === 'closed' && empty($round['result']))
                                    <button onclick="document.getElementById('result-modal-{{ $round['id'] }}').classList.remove('hidden')"
                                            class="btn-premium text-white text-[11px] px-3 py-1.5 rounded-lg font-medium">กรอกผล</button>
                                    @endif
                                    @if($st === 'open')
                                    <button @click="closeRound({{ $round['id'] }})" class="bg-amber-50 text-amber-600 hover:bg-amber-100 text-[11px] px-3 py-1.5 rounded-lg font-medium transition-colors">ปิดรับ</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-3" style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe);">
                                        <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <span class="text-sm text-gray-400 font-medium">ไม่มีรอบหวย</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════ TAB: Rates ═══════════════════════════ --}}
    <div x-show="activeTab === 'rates'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="space-y-6">
        <div class="card-premium">
            <div class="p-5 border-b border-indigo-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe);">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-800">อัตราจ่ายตามประเภทหวย</h3>
                        <p class="text-xs text-gray-400 mt-0.5">กำหนดอัตราจ่ายสำหรับแต่ละประเภทการแทง</p>
                    </div>
                </div>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @foreach($lotteryTypes ?? [] as $type)
                    <div class="border border-indigo-100 rounded-xl p-4">
                        <h4 class="font-bold text-gray-800 mb-3">{{ $type['name'] }}</h4>
                        <div class="space-y-2">
                            @php
                                $defaultRates = [
                                    ['name' => '3 ตัวบน', 'code' => 'top3', 'rate' => 800],
                                    ['name' => '3 ตัวโต๊ด', 'code' => 'tod3', 'rate' => 125],
                                    ['name' => '2 ตัวบน', 'code' => 'top2', 'rate' => 90],
                                    ['name' => '2 ตัวล่าง', 'code' => 'bot2', 'rate' => 90],
                                    ['name' => 'วิ่งบน', 'code' => 'run_top', 'rate' => 3.2],
                                    ['name' => 'วิ่งล่าง', 'code' => 'run_bot', 'rate' => 4.2],
                                ];
                            @endphp
                            @foreach($defaultRates as $rate)
                            <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-gray-50/50">
                                <span class="text-xs text-gray-600 font-medium">{{ $rate['name'] }}</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-indigo-600">x{{ $rate['rate'] }}</span>
                                    <button class="text-[10px] px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-600 hover:bg-indigo-100 transition-colors font-medium">แก้ไข</button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                @if(empty($lotteryTypes))
                <div class="py-12 text-center text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <p class="text-sm">ยังไม่มีประเภทหวยในระบบ</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════ TAB: Limits ═══════════════════════════ --}}
    <div x-show="activeTab === 'limits'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="space-y-6">
        {{-- Add Limit Form --}}
        <div class="card-premium p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #fee2e2, #fecaca);">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-800">เพิ่มเลขอั้น / จำกัดวงเงิน</h3>
                    <p class="text-xs text-gray-400">กำหนดเลขที่ห้ามแทงหรือจำกัดวงเงินสูงสุด</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <label class="text-xs font-semibold text-gray-400 mb-1.5 block uppercase tracking-wide">รอบหวย</label>
                    <select x-model="limitForm.round_id" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none bg-white transition-all">
                        <option value="">เลือกรอบ</option>
                        @foreach($rounds ?? [] as $r)
                            @if(in_array($r['status'] ?? '', ['open', 'upcoming']))
                            <option value="{{ $r['id'] }}">{{ $r['type_name'] }} - {{ $r['round_code'] }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-400 mb-1.5 block uppercase tracking-wide">ประเภทแทง</label>
                    <select x-model="limitForm.bet_type" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none bg-white transition-all">
                        <option value="top3">3 ตัวบน</option>
                        <option value="tod3">3 ตัวโต๊ด</option>
                        <option value="top2">2 ตัวบน</option>
                        <option value="bot2">2 ตัวล่าง</option>
                        <option value="run_top">วิ่งบน</option>
                        <option value="run_bot">วิ่งล่าง</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-400 mb-1.5 block uppercase tracking-wide">เลข</label>
                    <input type="text" x-model="limitForm.number" placeholder="เช่น 123, 45, 7" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-400 mb-1.5 block uppercase tracking-wide">วงเงินสูงสุด (0=อั้น)</label>
                    <div class="flex gap-2">
                        <input type="number" x-model="limitForm.max_amount" placeholder="0" class="flex-1 px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
                        <button @click="addLimit()" class="btn-premium text-white px-4 rounded-xl text-sm font-medium">เพิ่ม</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Current Limits (placeholder) --}}
        <div class="card-premium">
            <div class="p-5 border-b border-indigo-100">
                <h3 class="text-sm font-bold text-gray-800">เลขอั้นปัจจุบัน</h3>
            </div>
            <div class="px-5 py-12 text-center">
                <div class="w-14 h-14 bg-gradient-to-br from-gray-50 to-indigo-50/30 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <p class="text-sm text-gray-400">ยังไม่มีเลขอั้น</p>
                <p class="text-xs text-gray-300 mt-1">เพิ่มเลขอั้นจากฟอร์มด้านบน</p>
            </div>
        </div>
    </div>

    {{-- Result Submit Modals --}}
    @foreach($rounds ?? [] as $round)
        @if(($round['status'] ?? '') === 'closed' && empty($round['result']))
        <div id="result-modal-{{ $round['id'] }}" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="if(event.target===this) this.classList.add('hidden')">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-fade-up">
                <div class="p-5 border-b border-indigo-50" style="background: linear-gradient(135deg, rgba(79,70,229,0.05), rgba(124,58,237,0.05));">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">กรอกผลรางวัล</h3>
                            <p class="text-xs text-gray-400">{{ $round['type_name'] ?? '' }} ({{ $round['round_code'] ?? '' }})</p>
                        </div>
                    </div>
                </div>
                <form onsubmit="return submitResult(event, {{ $round['id'] }})" class="p-5 space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-600 mb-1.5 block">3 ตัวบน</label>
                        <input type="text" name="three_top" maxlength="3" pattern="\d{3}" class="w-full px-4 py-3 border border-indigo-100 rounded-xl text-sm font-mono text-center text-xl tracking-[0.3em] focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="X X X">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-sm font-medium text-gray-600 mb-1.5 block">2 ตัวบน</label>
                            <input type="text" name="two_top" maxlength="2" pattern="\d{2}" class="w-full px-4 py-3 border border-indigo-100 rounded-xl text-sm font-mono text-center text-xl tracking-[0.3em] focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="X X">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600 mb-1.5 block">2 ตัวล่าง</label>
                            <input type="text" name="two_bottom" maxlength="2" pattern="\d{2}" class="w-full px-4 py-3 border border-indigo-100 rounded-xl text-sm font-mono text-center text-xl tracking-[0.3em] focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="X X">
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600 mb-1.5 block">3 ตัวล่าง</label>
                        <input type="text" name="three_bottom" maxlength="3" pattern="\d{3}" class="w-full px-4 py-3 border border-indigo-100 rounded-xl text-sm font-mono text-center text-xl tracking-[0.3em] focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="X X X">
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" onclick="this.closest('.fixed').classList.add('hidden')" class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">ยกเลิก</button>
                        <button type="submit" class="flex-1 btn-premium text-white rounded-xl text-sm font-medium py-2.5">ยืนยันผล</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    @endforeach

    {{-- Create Round Modal --}}
    <div x-show="showCreateRound" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="showCreateRound=false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-fade-up" x-transition>
            <div class="p-5 border-b border-indigo-50" style="background: linear-gradient(135deg, rgba(79,70,229,0.05), rgba(124,58,237,0.05));">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">สร้างรอบใหม่</h3>
                        <p class="text-xs text-gray-400">เปิดรอบหวยรับแทงใหม่</p>
                    </div>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.lottery.rounds.create') }}" class="p-5 space-y-4">
                @csrf
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">ประเภทหวย</label>
                    <select name="lottery_type_id" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none bg-white transition-all" required>
                        @foreach($lotteryTypes ?? [] as $t)
                        <option value="{{ $t['id'] }}">{{ $t['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">รหัสรอบ</label>
                    <input type="text" name="round_code" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="เช่น 20260209-001" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-1.5 block">เปิดรับ</label>
                        <input type="datetime-local" name="open_at" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-1.5 block">ปิดรับ</label>
                        <input type="datetime-local" name="close_at" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" required>
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showCreateRound=false" class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">ยกเลิก</button>
                    <button type="submit" class="flex-1 btn-premium text-white rounded-xl text-sm font-medium py-2.5">สร้างรอบ</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function lotteryPage() {
    return {
        activeTab: 'overview',
        showCreateRound: false,
        limitForm: { round_id: '', bet_type: 'top3', number: '', max_amount: 0 },

        async toggleType(typeId, currentState) {
            if (!confirm(currentState ? 'ปิดใช้งานประเภทหวยนี้?' : 'เปิดใช้งานประเภทหวยนี้?')) return;
            try {
                const res = await fetchApi('/admin/lottery/types/' + typeId, {
                    method: 'PUT',
                    body: JSON.stringify({ is_active: !currentState }),
                });
                if (res.success) { location.reload(); }
                else { alert(res.message || 'เกิดข้อผิดพลาด'); }
            } catch (e) { alert('เกิดข้อผิดพลาด: ' + e.message); }
        },

        async closeRound(roundId) {
            if (!confirm('ปิดรับแทงรอบนี้?')) return;
            try {
                const res = await fetchApi('/admin/lottery/rounds/' + roundId, {
                    method: 'PUT',
                    body: JSON.stringify({ status: 'closed' }),
                });
                if (res.success) { location.reload(); }
                else { alert(res.message || 'เกิดข้อผิดพลาด'); }
            } catch (e) { alert('เกิดข้อผิดพลาด: ' + e.message); }
        },

        async addLimit() {
            if (!this.limitForm.round_id) return alert('เลือกรอบหวย');
            if (!this.limitForm.number) return alert('กรอกเลข');
            try {
                const res = await fetchApi('/admin/lottery/limits', {
                    method: 'POST',
                    body: JSON.stringify({
                        lottery_round_id: this.limitForm.round_id,
                        bet_type_id: this.limitForm.bet_type,
                        number: this.limitForm.number,
                        max_amount: parseFloat(this.limitForm.max_amount) || 0,
                    }),
                });
                if (res.success) {
                    alert(res.message || 'เพิ่มสำเร็จ');
                    this.limitForm.number = '';
                    this.limitForm.max_amount = 0;
                } else { alert(res.message || 'เกิดข้อผิดพลาด'); }
            } catch (e) { alert('เกิดข้อผิดพลาด: ' + e.message); }
        },
    };
}

async function submitResult(event, roundId) {
    event.preventDefault();
    const form = event.target;
    const data = new FormData(form);
    const results = {};
    ['three_top','two_top','two_bottom','three_bottom'].forEach(k => {
        const v = data.get(k);
        if (v) results[k] = v;
    });
    if (!results.three_top && !results.two_bottom) {
        alert('กรุณากรอกผลอย่างน้อย 3 ตัวบน หรือ 2 ตัวล่าง');
        return false;
    }
    if (!confirm('ยืนยันการกรอกผลรอบนี้?')) return false;
    try {
        const res = await fetchApi('/admin/lottery/results/' + roundId, {
            method: 'POST',
            body: JSON.stringify({ results }),
        });
        if (res.success) {
            window.showToast?.(res.message || 'บันทึกผลรางวัลสำเร็จ', 'success') || alert(res.message || 'สำเร็จ');
            setTimeout(() => location.reload(), 1000);
        } else {
            alert(res.message || 'เกิดข้อผิดพลาด');
        }
    } catch (e) { alert('เกิดข้อผิดพลาด: ' + e.message); }
    return false;
}
</script>
@endpush
