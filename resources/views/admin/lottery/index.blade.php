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
                    ['route' => 'admin.lottery.index', 'label' => 'ภาพรวม', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                    ['route' => 'admin.lottery.types', 'label' => 'ประเภทหวย', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z'],
                    ['route' => 'admin.lottery.rounds', 'label' => 'รอบหวย', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['route' => 'admin.lottery.rates', 'label' => 'อัตราจ่าย', 'icon' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                    ['route' => 'admin.lottery.limits', 'label' => 'อั้นเลข/จำกัด', 'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
                ];
            @endphp
            @foreach($tabs as $tab)
            <a href="{{ route($tab['route']) }}"
               class="flex items-center gap-2 px-5 py-3.5 text-sm font-medium border-b-2 whitespace-nowrap transition-all duration-200
               {{ request()->routeIs($tab['route']) ? 'border-indigo-600 text-indigo-600 bg-indigo-50/50' : 'border-transparent text-gray-400 hover:text-indigo-600 hover:bg-indigo-50/30' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/></svg>
                {{ $tab['label'] }}
            </a>
            @endforeach
        </div>
    </div>

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

    {{-- Result Submit Modals --}}
    @foreach($rounds ?? [] as $round)
        @if(($round['status'] ?? '') === 'closed' && empty($round['result']))
        <div id="result-modal-{{ $round['id'] }}" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="if(event.target===this) this.classList.add('hidden')">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-fade-up">
                {{-- Modal Header --}}
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
                {{-- Modal Body --}}
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
            {{-- Modal Header --}}
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
            {{-- Modal Body --}}
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
function lotteryPage() { return { showCreateRound: false }; }
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
            window.showToast(res.message || 'บันทึกผลรางวัลสำเร็จ', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            alert(res.message || 'เกิดข้อผิดพลาด');
        }
    } catch (e) { alert('เกิดข้อผิดพลาด: ' + e.message); }
    return false;
}
</script>
@endpush
