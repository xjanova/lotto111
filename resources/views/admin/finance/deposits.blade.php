@extends('admin.layouts.app')
@section('title', 'จัดการการเงิน')
@section('page-title', 'จัดการการเงิน')
@section('breadcrumb') <span class="text-gray-700">การเงิน</span> @endsection

@section('content')
<div x-data="financePage()" class="space-y-4 animate-fade-in">
    {{-- Tabs --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="flex border-b border-gray-100">
            <a href="{{ route('admin.finance.deposits') }}" class="px-6 py-3 text-sm font-medium border-b-2 {{ request()->routeIs('admin.finance.deposits') ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">ฝากเงิน</a>
            <a href="{{ route('admin.finance.withdrawals') }}" class="px-6 py-3 text-sm font-medium border-b-2 {{ request()->routeIs('admin.finance.withdrawals') ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">ถอนเงิน</a>
            <a href="{{ route('admin.finance.report') }}" class="px-6 py-3 text-sm font-medium border-b-2 {{ request()->routeIs('admin.finance.report') ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">รายงาน</a>
        </div>

        {{-- Stats Summary --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4">
            <div class="text-center p-3 bg-green-50 rounded-lg">
                <div class="text-xs text-green-600 mb-1">ฝากวันนี้</div>
                <div class="text-lg font-bold text-green-700">฿{{ number_format($todayDeposits ?? 0, 2) }}</div>
            </div>
            <div class="text-center p-3 bg-orange-50 rounded-lg">
                <div class="text-xs text-orange-600 mb-1">ถอนวันนี้</div>
                <div class="text-lg font-bold text-orange-700">฿{{ number_format($todayWithdrawals ?? 0, 2) }}</div>
            </div>
            <div class="text-center p-3 bg-yellow-50 rounded-lg">
                <div class="text-xs text-yellow-600 mb-1">รอดำเนินการ</div>
                <div class="text-lg font-bold text-yellow-700">{{ $pendingCount ?? 0 }} รายการ</div>
            </div>
            <div class="text-center p-3 bg-blue-50 rounded-lg">
                <div class="text-xs text-blue-600 mb-1">คงเหลือในระบบ</div>
                <div class="text-lg font-bold text-blue-700">฿{{ number_format($totalBalance ?? 0, 2) }}</div>
            </div>
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
                    <th class="px-4 py-3 font-medium">ค่าธรรมเนียม</th>
                    <th class="px-4 py-3 font-medium">ช่องทาง</th>
                    <th class="px-4 py-3 font-medium">สถานะ</th>
                    <th class="px-4 py-3 font-medium">เวลา</th>
                    <th class="px-4 py-3 font-medium">จัดการ</th>
                </tr></thead>
                <tbody>
                    @forelse($transactions ?? [] as $tx)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500">{{ $tx['id'] }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $tx['user'] ?? '-' }}</td>
                        <td class="px-4 py-3 font-bold text-green-600">+฿{{ number_format($tx['amount'] ?? 0, 2) }}</td>
                        <td class="px-4 py-3 text-gray-500">฿{{ number_format($tx['fee'] ?? 0, 2) }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $tx['channel'] ?? 'SMS' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 text-xs rounded-full
                                {{ ($tx['status'] ?? '') === 'approved' ? 'bg-green-50 text-green-600' : (($tx['status'] ?? '') === 'pending' ? 'bg-yellow-50 text-yellow-600' : 'bg-red-50 text-red-600') }}">
                                {{ ($tx['status'] ?? '') === 'approved' ? 'อนุมัติ' : (($tx['status'] ?? '') === 'pending' ? 'รอตรวจสอบ' : 'ปฏิเสธ') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $tx['created_at'] ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if(($tx['status'] ?? '') === 'pending')
                            <div class="flex gap-1">
                                <button onclick="approveDeposit({{ $tx['id'] }})" class="px-2 py-1 text-xs bg-green-50 text-green-600 rounded hover:bg-green-100">อนุมัติ</button>
                                <button onclick="rejectDeposit({{ $tx['id'] }})" class="px-2 py-1 text-xs bg-red-50 text-red-600 rounded hover:bg-red-100">ปฏิเสธ</button>
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
function financePage() { return {}; }
async function approveDeposit(id) {
    if (!confirm('ยืนยันอนุมัติรายการนี้?')) return;
    const res = await fetchApi('/admin/finance/deposits/' + id + '/approve', { method: 'PUT' });
    if (res.success) location.reload(); else alert(res.message || 'เกิดข้อผิดพลาด');
}
async function rejectDeposit(id) {
    if (!confirm('ยืนยันปฏิเสธรายการนี้?')) return;
    const res = await fetchApi('/admin/finance/deposits/' + id + '/reject', { method: 'PUT' });
    if (res.success) location.reload(); else alert(res.message || 'เกิดข้อผิดพลาด');
}
</script>
@endpush
