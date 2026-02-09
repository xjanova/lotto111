@extends('admin.layouts.app')
@section('title', 'บันทึกระบบ')
@section('page-title', 'บันทึกระบบ (Admin Logs)')
@section('breadcrumb') <span class="text-gray-700">บันทึก</span> @endsection

@section('content')
<div class="space-y-6">

    {{-- Hero Banner --}}
    <div class="relative overflow-hidden rounded-2xl p-6 md:p-8 animate-fade-up" style="background: linear-gradient(135deg, #4f46e5, #6366f1, #8b5cf6);">
        <div class="absolute top-0 right-0 w-72 h-72 bg-white/10 rounded-full filter blur-3xl animate-blob"></div>
        <div class="absolute bottom-0 left-0 w-72 h-72 bg-purple-300/10 rounded-full filter blur-3xl animate-blob delay-200"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-white mb-1">บันทึกระบบ</h2>
                <p class="text-white/70 text-sm">ประวัติการกระทำของแอดมินทั้งหมดในระบบ</p>
            </div>
            <a href="{{ route('admin.logs') }}" class="px-4 py-2.5 bg-white text-indigo-700 rounded-xl text-sm font-bold transition-all duration-200 hover:shadow-lg hover:shadow-white/20 flex items-center gap-2 self-start">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                รีเฟรช
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card-premium p-5 animate-fade-up delay-100">
        <form method="GET" action="{{ route('admin.logs') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="text-xs font-semibold text-gray-400 mb-1.5 block uppercase tracking-wide">Action</label>
                <select name="action" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none bg-white transition-all">
                    <option value="">ทั้งหมด</option>
                    @foreach($actions ?? [] as $a)
                    <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-premium text-white rounded-xl text-sm font-medium px-5 py-2.5 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    กรอง
                </button>
                <a href="{{ route('admin.logs') }}" class="px-5 py-2.5 border border-indigo-100 rounded-xl text-sm font-medium text-gray-500 hover:bg-indigo-50/50 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    ล้าง
                </a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="card-premium animate-fade-up delay-200">
        <div class="p-5 border-b border-indigo-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-800">รายการบันทึก</h3>
                <p class="text-xs text-gray-400 mt-0.5">ประวัติการกระทำในระบบย้อนหลัง</p>
            </div>
            @if(request('action'))
            <span class="px-3 py-1 text-xs rounded-full font-semibold bg-indigo-50 text-indigo-600 flex items-center gap-1.5">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                กรอง: {{ request('action') }}
            </span>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-400 border-b border-indigo-50 uppercase tracking-wide">
                        <th class="px-5 py-3.5 font-semibold">ID</th>
                        <th class="px-5 py-3.5 font-semibold">แอดมิน</th>
                        <th class="px-5 py-3.5 font-semibold">Action</th>
                        <th class="px-5 py-3.5 font-semibold">รายละเอียด</th>
                        <th class="px-5 py-3.5 font-semibold">เป้าหมาย</th>
                        <th class="px-5 py-3.5 font-semibold">เวลา</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs ?? [] as $log)
                    <tr class="border-b border-gray-50 table-row-hover transition-colors">
                        <td class="px-5 py-3.5 text-gray-400 text-xs font-mono">{{ $log['id'] }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold flex-shrink-0" style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe); color: #4f46e5;">
                                    {{ strtoupper(substr($log['admin_name'] ?? 'A', 0, 2)) }}
                                </div>
                                <span class="font-semibold text-gray-700">{{ $log['admin_name'] ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            @php
                                $actionColor = 'bg-indigo-50 text-indigo-600';
                                $action = $log['action'] ?? '';
                                if(str_contains($action, 'create') || str_contains($action, 'add')) $actionColor = 'bg-emerald-50 text-emerald-600';
                                elseif(str_contains($action, 'delete') || str_contains($action, 'ban') || str_contains($action, 'block')) $actionColor = 'bg-red-50 text-red-600';
                                elseif(str_contains($action, 'update') || str_contains($action, 'edit')) $actionColor = 'bg-amber-50 text-amber-600';
                                elseif(str_contains($action, 'approve') || str_contains($action, 'credit')) $actionColor = 'bg-emerald-50 text-emerald-600';
                                elseif(str_contains($action, 'reject') || str_contains($action, 'deny')) $actionColor = 'bg-red-50 text-red-600';
                                elseif(str_contains($action, 'login') || str_contains($action, 'auth')) $actionColor = 'bg-purple-50 text-purple-600';
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] rounded-full font-semibold uppercase tracking-wide {{ $actionColor }}">
                                {{ $action }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-gray-600 text-xs max-w-xs truncate">{{ $log['description'] ?? '-' }}</td>
                        <td class="px-5 py-3.5">
                            @if($log['target_type'] ?? null)
                            <span class="px-2 py-0.5 text-[10px] rounded-full bg-gray-50 text-gray-500 font-mono font-medium">
                                {{ class_basename($log['target_type'] ?? '') }} #{{ $log['target_id'] ?? '' }}
                            </span>
                            @else
                            <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-xs text-gray-400">{{ $log['created_at'] ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-3" style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe);">
                                    <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </div>
                                <span class="text-sm text-gray-400 font-medium">ไม่มีบันทึก</span>
                                <p class="text-xs text-gray-300 mt-1">ยังไม่มีรายการบันทึกในระบบ</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(method_exists($logs ?? collect(), 'hasPages') && $logs->hasPages())
        <div class="px-5 py-4 border-t border-indigo-50 flex items-center justify-between">
            <span class="text-xs text-gray-400">
                แสดง {{ $logs->firstItem() }}-{{ $logs->lastItem() }} จาก {{ $logs->total() }} รายการ
            </span>
            <div class="flex gap-1.5">
                @if($logs->onFirstPage())
                <span class="px-3 py-1.5 border border-indigo-50 rounded-lg text-xs text-gray-300 cursor-not-allowed">ก่อนหน้า</span>
                @else
                <a href="{{ $logs->previousPageUrl() }}" class="px-3 py-1.5 border border-indigo-100 rounded-lg text-xs text-gray-600 hover:bg-indigo-50 transition-colors font-medium">ก่อนหน้า</a>
                @endif

                @foreach($logs->getUrlRange(max(1, $logs->currentPage() - 2), min($logs->lastPage(), $logs->currentPage() + 2)) as $page => $url)
                @if($page == $logs->currentPage())
                <span class="px-3 py-1.5 rounded-lg text-xs font-bold text-white btn-premium">{{ $page }}</span>
                @else
                <a href="{{ $url }}" class="px-3 py-1.5 border border-indigo-100 rounded-lg text-xs text-gray-600 hover:bg-indigo-50 transition-colors font-medium">{{ $page }}</a>
                @endif
                @endforeach

                @if($logs->hasMorePages())
                <a href="{{ $logs->nextPageUrl() }}" class="px-3 py-1.5 border border-indigo-100 rounded-lg text-xs text-gray-600 hover:bg-indigo-50 transition-colors font-medium">ถัดไป</a>
                @else
                <span class="px-3 py-1.5 border border-indigo-50 rounded-lg text-xs text-gray-300 cursor-not-allowed">ถัดไป</span>
                @endif
            </div>
        </div>
        @elseif(is_array($logs ?? null) && count($logs) > 0)
        <div class="px-5 py-4 border-t border-indigo-50 flex items-center justify-center">
            <span class="text-xs text-gray-400">แสดงทั้งหมด {{ count($logs) }} รายการ</span>
        </div>
        @endif
    </div>
</div>
@endsection
