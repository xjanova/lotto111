@extends('admin.layouts.app')
@section('title', 'บันทึกระบบ')
@section('page-title', 'บันทึกระบบ (Admin Logs)')
@section('breadcrumb') <span class="text-gray-700">บันทึก</span> @endsection

@section('content')
<div class="space-y-4 animate-fade-in">
    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <form method="GET" action="{{ route('admin.logs') }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs text-gray-500 mb-1 block">Action</label>
                <select name="action" class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
                    <option value="">ทั้งหมด</option>
                    @foreach($actions ?? [] as $a)
                    <option value="{{ $a }}" {{ request('action') === $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-brand-600 text-white rounded-lg text-sm hover:bg-brand-700">กรอง</button>
            <a href="{{ route('admin.logs') }}" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">ล้าง</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-xs text-gray-400 bg-gray-50 border-b border-gray-100">
                    <th class="px-4 py-3 font-medium">ID</th>
                    <th class="px-4 py-3 font-medium">แอดมิน</th>
                    <th class="px-4 py-3 font-medium">Action</th>
                    <th class="px-4 py-3 font-medium">รายละเอียด</th>
                    <th class="px-4 py-3 font-medium">เป้าหมาย</th>
                    <th class="px-4 py-3 font-medium">เวลา</th>
                </tr></thead>
                <tbody>
                    @forelse($logs ?? [] as $log)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $log['id'] }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $log['admin_name'] ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 text-xs rounded-full bg-blue-50 text-blue-600">{{ $log['action'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 max-w-xs truncate">{{ $log['description'] ?? '-' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-400">
                            @if($log['target_type'] ?? null)
                            {{ $log['target_type'] }} #{{ $log['target_id'] ?? '' }}
                            @else
                            -
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $log['created_at'] ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">ไม่มีบันทึก</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
