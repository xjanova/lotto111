@extends('admin.layouts.app')
@section('title', 'จัดการถอนเงิน')
@section('page-title', 'จัดการการเงิน')
@section('breadcrumb') <span class="text-gray-700">การเงิน</span> <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg> <span class="text-gray-700">ถอนเงิน</span> @endsection

@section('content')
<div x-data="withdrawalPage()" class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 animate-fade-up">
        <div>
            <h1 class="text-2xl font-bold gradient-text">จัดการถอนเงิน</h1>
            <p class="text-sm text-gray-400 mt-1">ตรวจสอบและอนุมัติรายการถอนเงินของสมาชิก</p>
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

    {{-- Stats Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        {{-- Total Withdrawals Today --}}
        <div class="stat-card card-premium p-5 animate-fade-up delay-100">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">ถอนวันนี้</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ffedd5, #fed7aa);">
                    <svg class="w-4.5 h-4.5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800">฿{{ number_format($todayWithdrawals ?? 0, 2) }}</div>
            <div class="text-[10px] text-orange-600 font-medium mt-1">ยอดถอนรวมวันนี้</div>
        </div>

        {{-- Pending Count --}}
        <div class="stat-card card-premium p-5 animate-fade-up delay-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">รอดำเนินการ</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #fef3c7, #fde68a);">
                    <svg class="w-4.5 h-4.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800">{{ $pendingCount ?? 0 }}</div>
            <div class="text-[10px] text-amber-600 font-medium mt-1">รายการรออนุมัติ</div>
        </div>

        {{-- Approved Count --}}
        <div class="stat-card card-premium p-5 animate-fade-up delay-300">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">อนุมัติแล้ว</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #d1fae5, #a7f3d0);">
                    <svg class="w-4.5 h-4.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800">{{ $approvedCount ?? 0 }}</div>
            <div class="text-[10px] text-emerald-600 font-medium mt-1">รายการที่อนุมัติวันนี้</div>
        </div>

        {{-- Rejected Count --}}
        <div class="stat-card card-premium p-5 animate-fade-up delay-400">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">ปฏิเสธ</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #fee2e2, #fecaca);">
                    <svg class="w-4.5 h-4.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800">{{ $rejectedCount ?? 0 }}</div>
            <div class="text-[10px] text-red-600 font-medium mt-1">รายการที่ปฏิเสธวันนี้</div>
        </div>
    </div>

    {{-- Withdrawals Table --}}
    <div class="card-premium overflow-hidden animate-fade-up delay-500">
        <div class="px-6 py-4 border-b border-indigo-100/50 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-800">รายการถอนเงิน</h3>
                <p class="text-xs text-gray-400 mt-0.5">รายการถอนเงินทั้งหมดของสมาชิก</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-400 border-b border-indigo-50 uppercase tracking-wide">
                        <th class="px-6 py-3.5 font-semibold">ID</th>
                        <th class="px-6 py-3.5 font-semibold">สมาชิก</th>
                        <th class="px-6 py-3.5 font-semibold">จำนวน</th>
                        <th class="px-6 py-3.5 font-semibold">ธนาคาร</th>
                        <th class="px-6 py-3.5 font-semibold">เลขบัญชี</th>
                        <th class="px-6 py-3.5 font-semibold">สถานะ</th>
                        <th class="px-6 py-3.5 font-semibold">เวลา</th>
                        <th class="px-6 py-3.5 font-semibold">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($withdrawals ?? [] as $w)
                    <tr class="border-b border-gray-50 table-row-hover transition-colors">
                        <td class="px-6 py-4">
                            <span class="text-xs font-mono text-gray-400">#{{ $w['id'] }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 bg-gradient-to-br from-orange-100 to-amber-100 rounded-xl flex items-center justify-center text-xs font-bold text-orange-600">
                                    {{ mb_substr($w['user'] ?? 'U', 0, 2) }}
                                </div>
                                <span class="font-semibold text-gray-700">{{ $w['user'] ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-orange-600">-฿{{ number_format($w['amount'] ?? 0, 2) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-lg bg-indigo-50 text-indigo-600">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                {{ $w['bank_name'] ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-gray-500 font-mono text-xs bg-gray-50 px-2 py-1 rounded">{{ $w['account_number'] ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @php $st = $w['status'] ?? ''; @endphp
                            @if($st === 'approved' || $st === 'completed')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-semibold rounded-full bg-emerald-50 text-emerald-600 uppercase tracking-wide">
                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                    อนุมัติ
                                </span>
                            @elseif($st === 'pending')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-semibold rounded-full bg-amber-50 text-amber-600 uppercase tracking-wide badge-pulse">
                                    <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
                                    รอตรวจสอบ
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-semibold rounded-full bg-red-50 text-red-600 uppercase tracking-wide">
                                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                    ปฏิเสธ
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-400">{{ $w['created_at'] ?? '-' }}</td>
                        <td class="px-6 py-4">
                            @if($st === 'pending')
                            <div class="flex gap-2">
                                <button onclick="approveWithdrawal({{ $w['id'] }})"
                                        class="btn-premium text-white text-xs px-3.5 py-1.5 rounded-lg font-medium flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    อนุมัติ
                                </button>
                                <button onclick="rejectWithdrawal({{ $w['id'] }})"
                                        class="text-xs px-3.5 py-1.5 rounded-lg font-medium flex items-center gap-1.5 text-white transition-all duration-200 hover:shadow-lg"
                                        style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    ปฏิเสธ
                                </button>
                            </div>
                            @else
                            <span class="text-xs text-gray-300">--</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-gradient-to-br from-orange-50 to-amber-50 rounded-2xl flex items-center justify-center mb-3">
                                    <svg class="w-8 h-8 text-orange-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                </div>
                                <span class="text-sm text-gray-400 font-medium">ไม่มีรายการถอนเงิน</span>
                                <span class="text-xs text-gray-300 mt-1">รายการจะแสดงเมื่อมีการถอนเงินเข้ามา</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(isset($paginated) && $paginated->hasPages())
        <div class="px-6 py-4 border-t border-indigo-50 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm">
            <span class="text-gray-400">
                แสดง <span class="font-semibold text-gray-700">{{ $paginated->firstItem() }}</span>
                ถึง <span class="font-semibold text-gray-700">{{ $paginated->lastItem() }}</span>
                จากทั้งหมด <span class="font-semibold text-gray-700">{{ $paginated->total() }}</span> รายการ
            </span>
            <div class="flex items-center gap-2">
                @if($paginated->onFirstPage())
                    <span class="px-3.5 py-2 text-xs font-medium text-gray-300 bg-gray-50 rounded-lg cursor-not-allowed">ก่อนหน้า</span>
                @else
                    <a href="{{ $paginated->previousPageUrl() }}" class="px-3.5 py-2 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">ก่อนหน้า</a>
                @endif

                @foreach($paginated->getUrlRange(max(1, $paginated->currentPage() - 2), min($paginated->lastPage(), $paginated->currentPage() + 2)) as $page => $url)
                    @if($page == $paginated->currentPage())
                        <span class="btn-premium text-white text-xs px-3.5 py-2 rounded-lg font-bold">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="px-3.5 py-2 text-xs font-medium text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition-colors">{{ $page }}</a>
                    @endif
                @endforeach

                @if($paginated->hasMorePages())
                    <a href="{{ $paginated->nextPageUrl() }}" class="px-3.5 py-2 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">ถัดไป</a>
                @else
                    <span class="px-3.5 py-2 text-xs font-medium text-gray-300 bg-gray-50 rounded-lg cursor-not-allowed">ถัดไป</span>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Reject Reason Modal --}}
    <div x-show="showRejectModal" x-cloak
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @click.self="showRejectModal = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="card-premium w-full max-w-md p-6 animate-fade-up" @click.stop>
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #fee2e2, #fecaca);">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">ปฏิเสธรายการถอนเงิน</h3>
                    <p class="text-xs text-gray-400">กรุณาระบุเหตุผล (ถ้ามี)</p>
                </div>
            </div>
            <div class="mb-5">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2 block">เหตุผลที่ปฏิเสธ</label>
                <textarea x-model="rejectReason" rows="3"
                          class="w-full px-4 py-3 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 outline-none transition-all resize-none"
                          placeholder="ระบุเหตุผลที่ปฏิเสธรายการนี้..."></textarea>
            </div>
            <div class="flex gap-3">
                <button @click="showRejectModal = false"
                        class="flex-1 px-4 py-2.5 border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors">
                    ยกเลิก
                </button>
                <button @click="confirmReject()"
                        class="flex-1 px-4 py-2.5 rounded-xl text-sm font-medium text-white transition-all duration-200 hover:shadow-lg"
                        style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                    ยืนยันปฏิเสธ
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function withdrawalPage() {
    return {
        showRejectModal: false,
        rejectId: null,
        rejectReason: '',

        openRejectModal(id) {
            this.rejectId = id;
            this.rejectReason = '';
            this.showRejectModal = true;
        },

        async confirmReject() {
            if (!this.rejectId) return;
            try {
                const res = await fetchApi('/admin/finance/withdrawals/' + this.rejectId + '/reject', {
                    method: 'PUT',
                    body: JSON.stringify({ reason: this.rejectReason })
                });
                if (res.success) {
                    this.showRejectModal = false;
                    showToast('ปฏิเสธรายการสำเร็จ', 'success');
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToast(res.message || 'เกิดข้อผิดพลาด', 'error');
                }
            } catch (e) {
                showToast('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
            }
        }
    };
}

async function approveWithdrawal(id) {
    if (!confirm('ยืนยันอนุมัติรายการถอนเงินนี้?\n\nกรุณาตรวจสอบข้อมูลธนาคารและจำนวนเงินก่อนอนุมัติ')) return;
    try {
        const res = await fetchApi('/admin/finance/withdrawals/' + id + '/approve', { method: 'PUT' });
        if (res.success) {
            showToast('อนุมัติรายการสำเร็จ', 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(res.message || 'เกิดข้อผิดพลาด', 'error');
        }
    } catch (e) {
        showToast('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
    }
}

function rejectWithdrawal(id) {
    // Use Alpine.js component method via DOM
    const el = document.querySelector('[x-data]');
    if (el && el.__x) {
        el.__x.$data.openRejectModal(id);
    } else {
        // Fallback for Alpine v3
        Alpine.evaluate(el, `openRejectModal(${id})`);
    }
}
</script>
@endpush
