@extends('admin.layouts.app')
@section('title', 'จัดการหวย')
@section('page-title', 'จัดการหวย/รอบ/อัตราจ่าย')
@section('breadcrumb') <span class="text-gray-700">หวย</span> @endsection

@section('content')
<div x-data="lotteryPage()" class="space-y-4 animate-fade-in">
    {{-- Tabs --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="flex border-b border-gray-100 overflow-x-auto">
            <a href="{{ route('admin.lottery.index') }}" class="px-5 py-3 text-sm font-medium border-b-2 whitespace-nowrap {{ request()->routeIs('admin.lottery.index') ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">ภาพรวม</a>
            <a href="{{ route('admin.lottery.types') }}" class="px-5 py-3 text-sm font-medium border-b-2 whitespace-nowrap {{ request()->routeIs('admin.lottery.types') ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">ประเภทหวย</a>
            <a href="{{ route('admin.lottery.rounds') }}" class="px-5 py-3 text-sm font-medium border-b-2 whitespace-nowrap {{ request()->routeIs('admin.lottery.rounds') ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">รอบหวย</a>
            <a href="{{ route('admin.lottery.rates') }}" class="px-5 py-3 text-sm font-medium border-b-2 whitespace-nowrap {{ request()->routeIs('admin.lottery.rates') ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">อัตราจ่าย</a>
            <a href="{{ route('admin.lottery.limits') }}" class="px-5 py-3 text-sm font-medium border-b-2 whitespace-nowrap {{ request()->routeIs('admin.lottery.limits') ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">อั้นเลข/จำกัด</a>
        </div>
    </div>

    {{-- Lottery Types Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($lotteryTypes ?? [] as $type)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-gray-800">{{ $type['name'] ?? '' }}</h3>
                <span class="px-2 py-0.5 text-xs rounded-full {{ ($type['is_active'] ?? false) ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' }}">
                    {{ ($type['is_active'] ?? false) ? 'เปิด' : 'ปิด' }}
                </span>
            </div>
            <div class="text-xs text-gray-500 mb-3">{{ $type['category'] ?? '' }} | {{ $type['slug'] ?? '' }}</div>
            <div class="grid grid-cols-2 gap-2 text-sm">
                <div class="bg-gray-50 rounded-lg p-2 text-center">
                    <div class="text-xs text-gray-400">รอบเปิด</div>
                    <div class="font-bold text-gray-700">{{ $type['open_rounds'] ?? 0 }}</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-2 text-center">
                    <div class="text-xs text-gray-400">ยอดแทงวันนี้</div>
                    <div class="font-bold text-gray-700">฿{{ number_format($type['today_bets'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Active Rounds --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">รอบที่กำลังดำเนินการ</h3>
            <button @click="showCreateRound = true" class="px-4 py-2 bg-brand-600 text-white text-sm rounded-lg hover:bg-brand-700 transition-colors">+ สร้างรอบใหม่</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-xs text-gray-400 bg-gray-50 border-b border-gray-100">
                    <th class="px-4 py-3 font-medium">ประเภท</th>
                    <th class="px-4 py-3 font-medium">รหัสรอบ</th>
                    <th class="px-4 py-3 font-medium">สถานะ</th>
                    <th class="px-4 py-3 font-medium">เปิด</th>
                    <th class="px-4 py-3 font-medium">ปิด</th>
                    <th class="px-4 py-3 font-medium">ผล</th>
                    <th class="px-4 py-3 font-medium">ยอดแทง</th>
                    <th class="px-4 py-3 font-medium">จัดการ</th>
                </tr></thead>
                <tbody>
                    @forelse($rounds ?? [] as $round)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-700">{{ $round['type_name'] ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $round['round_code'] ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @php $st = $round['status'] ?? ''; @endphp
                            <span class="px-2 py-0.5 text-xs rounded-full
                                {{ $st === 'open' ? 'bg-green-50 text-green-600' : ($st === 'closed' ? 'bg-yellow-50 text-yellow-600' : ($st === 'resulted' ? 'bg-blue-50 text-blue-600' : 'bg-gray-100 text-gray-500')) }}">
                                {{ $st === 'open' ? 'เปิดรับ' : ($st === 'closed' ? 'ปิดรับ' : ($st === 'resulted' ? 'ออกผลแล้ว' : $st)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $round['open_at'] ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $round['close_at'] ?? '-' }}</td>
                        <td class="px-4 py-3 font-mono font-bold text-brand-600">{{ $round['result'] ?? '-' }}</td>
                        <td class="px-4 py-3 font-medium">฿{{ number_format($round['total_bets'] ?? 0, 2) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                @if($st === 'closed' && empty($round['result']))
                                <button onclick="document.getElementById('result-modal-{{ $round['id'] }}').classList.remove('hidden')"
                                        class="px-2 py-1 text-xs bg-brand-50 text-brand-600 rounded hover:bg-brand-100">กรอกผล</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">ไม่มีรอบ</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Result Submit Modals --}}
    @foreach($rounds ?? [] as $round)
        @if(($round['status'] ?? '') === 'closed' && empty($round['result']))
        <div id="result-modal-{{ $round['id'] }}" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onclick="if(event.target===this) this.classList.add('hidden')">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 animate-fade-in">
                <h3 class="text-lg font-semibold mb-4">กรอกผล: {{ $round['type_name'] ?? '' }} ({{ $round['round_code'] ?? '' }})</h3>
                <form onsubmit="return submitResult(event, {{ $round['id'] }})" class="space-y-3">
                    <div>
                        <label class="text-sm text-gray-600 mb-1 block">3 ตัวบน</label>
                        <input type="text" name="three_top" maxlength="3" pattern="\d{3}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono text-center text-lg tracking-widest" placeholder="xxx">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-sm text-gray-600 mb-1 block">2 ตัวบน</label>
                            <input type="text" name="two_top" maxlength="2" pattern="\d{2}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono text-center text-lg tracking-widest" placeholder="xx">
                        </div>
                        <div>
                            <label class="text-sm text-gray-600 mb-1 block">2 ตัวล่าง</label>
                            <input type="text" name="two_bottom" maxlength="2" pattern="\d{2}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono text-center text-lg tracking-widest" placeholder="xx">
                        </div>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 mb-1 block">3 ตัวล่าง</label>
                        <input type="text" name="three_bottom" maxlength="3" pattern="\d{3}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono text-center text-lg tracking-widest" placeholder="xxx">
                    </div>
                    <div class="flex gap-3 mt-4">
                        <button type="button" onclick="this.closest('.fixed').classList.add('hidden')" class="flex-1 px-4 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-50">ยกเลิก</button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-brand-600 text-white rounded-lg text-sm hover:bg-brand-700">ยืนยันผล</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    @endforeach

    {{-- Create Round Modal --}}
    <div x-show="showCreateRound" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showCreateRound=false">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 animate-fade-in">
            <h3 class="text-lg font-semibold mb-4">สร้างรอบใหม่</h3>
            <form method="POST" action="{{ route('admin.lottery.rounds.create') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="text-sm text-gray-600 mb-1 block">ประเภทหวย</label>
                    <select name="lottery_type_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm" required>
                        @foreach($lotteryTypes ?? [] as $t)
                        <option value="{{ $t['id'] }}">{{ $t['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm text-gray-600 mb-1 block">รหัสรอบ</label>
                    <input type="text" name="round_code" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm" placeholder="เช่น 20260209-001" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm text-gray-600 mb-1 block">เปิดรับ</label>
                        <input type="datetime-local" name="open_at" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm" required>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 mb-1 block">ปิดรับ</label>
                        <input type="datetime-local" name="close_at" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm" required>
                    </div>
                </div>
                <div class="flex gap-3 mt-4">
                    <button type="button" @click="showCreateRound=false" class="flex-1 px-4 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-50">ยกเลิก</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-brand-600 text-white rounded-lg text-sm hover:bg-brand-700">สร้าง</button>
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
            alert(res.message || 'สำเร็จ');
            location.reload();
        } else {
            alert(res.message || 'เกิดข้อผิดพลาด');
        }
    } catch (e) { alert('เกิดข้อผิดพลาด: ' + e.message); }
    return false;
}
</script>
@endpush
