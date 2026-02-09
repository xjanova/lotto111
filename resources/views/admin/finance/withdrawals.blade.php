@extends('admin.layouts.app')
@section('title', 'จัดการถอนเงิน')
@section('page-title', 'จัดการการเงิน')
@section('breadcrumb') <span class="text-gray-700">การเงิน</span> @endsection

@section('content')
<div x-data="withdrawalPage()" class="space-y-4 animate-fade-in">
    {{-- Tabs --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="flex border-b border-gray-100">
            <a href="{{ route('admin.finance.deposits') }}" class="px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">ฝากเงิน</a>
            <a href="{{ route('admin.finance.withdrawals') }}" class="px-6 py-3 text-sm font-medium border-b-2 border-brand-600 text-brand-600">ถอนเงิน</a>
            <a href="{{ route('admin.finance.report') }}" class="px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">รายงาน</a>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-xs text-gray-400 bg-gray-50 border-b border-gray-100">
                    <th class="px-4 py-3 font-medium">ID</th>
                    <th class="px-4 py-3 font-medium">สมาชิก</th>
                    <th class="px-4 py-3 font-medium">จำนวน</th>
                    <th class="px-4 py-3 font-medium">ธนาคาร</th>
                    <th class="px-4 py-3 font-medium">เลขบัญชี</th>
                    <th class="px-4 py-3 font-medium">สถานะ</th>
                    <th class="px-4 py-3 font-medium">เวลา</th>
                    <th class="px-4 py-3 font-medium">จัดการ</th>
                </tr></thead>
                <tbody>
                    @forelse($withdrawals ?? [] as $w)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500">{{ $w['id'] }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $w['user'] ?? '-' }}</td>
                        <td class="px-4 py-3 font-bold text-orange-600">-฿{{ number_format($w['amount'] ?? 0, 2) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $w['bank_name'] ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $w['account_number'] ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @php $st = $w['status'] ?? ''; @endphp
                            <span class="px-2 py-0.5 text-xs rounded-full
                                {{ $st === 'approved' || $st === 'completed' ? 'bg-green-50 text-green-600' : ($st === 'pending' ? 'bg-yellow-50 text-yellow-600' : 'bg-red-50 text-red-600') }}">
                                {{ $st === 'approved' || $st === 'completed' ? 'อนุมัติ' : ($st === 'pending' ? 'รอตรวจสอบ' : 'ปฏิเสธ') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $w['created_at'] ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if($st === 'pending')
                            <div class="flex gap-1">
                                <button onclick="approveWithdrawal({{ $w['id'] }})" class="px-2 py-1 text-xs bg-green-50 text-green-600 rounded hover:bg-green-100">อนุมัติ</button>
                                <button onclick="rejectWithdrawal({{ $w['id'] }})" class="px-2 py-1 text-xs bg-red-50 text-red-600 rounded hover:bg-red-100">ปฏิเสธ</button>
                            </div>
                            @else
                            <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">ไม่มีรายการ</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function withdrawalPage() { return {}; }
async function approveWithdrawal(id) {
    if (!confirm('ยืนยันอนุมัติรายการถอนนี้?')) return;
    const res = await fetchApi('/admin/finance/withdrawals/' + id + '/approve', { method: 'PUT' });
    if (res.success) location.reload(); else alert(res.message || 'เกิดข้อผิดพลาด');
}
async function rejectWithdrawal(id) {
    const reason = prompt('เหตุผลที่ปฏิเสธ (ถ้ามี):');
    const res = await fetchApi('/admin/finance/withdrawals/' + id + '/reject', { method: 'PUT', body: JSON.stringify({ reason }) });
    if (res.success) location.reload(); else alert(res.message || 'เกิดข้อผิดพลาด');
}
</script>
@endpush
