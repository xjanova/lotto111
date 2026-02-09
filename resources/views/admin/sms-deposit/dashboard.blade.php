@extends('admin.layouts.app')
@section('title', 'SMS ฝากเงิน')
@section('page-title', 'SMS ฝากเงิน')
@section('page-subtitle', 'จัดการอุปกรณ์ SMS และการฝากเงินอัตโนมัติ')
@section('breadcrumb')
<span class="text-gray-700 font-medium">SMS ฝากเงิน</span>
@endsection

@section('content')
<div x-data="smsPage()" class="space-y-6">

    {{-- Hero --}}
    <div class="relative overflow-hidden rounded-2xl p-6 md:p-8" style="background: linear-gradient(135deg, #4f46e5, #7c3aed, #a855f7);">
        <div class="absolute top-0 left-0 w-72 h-72 bg-white/10 rounded-full filter blur-3xl animate-blob"></div>
        <div class="absolute bottom-0 right-0 w-72 h-72 bg-purple-300/10 rounded-full filter blur-3xl animate-blob delay-200"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-white">SMS Auto-Deposit System</h2>
                    <p class="text-white/70 text-sm">จัดการอุปกรณ์ SMS Checker และฝากเงินอัตโนมัติ</p>
                </div>
            </div>
            <button @click="showAddDevice = true" class="px-5 py-2.5 bg-white text-indigo-700 rounded-xl text-sm font-bold transition-all hover:shadow-lg flex items-center gap-2 w-fit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                เพิ่มอุปกรณ์
            </button>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="stat-card card-premium p-4 animate-fade-up">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">อุปกรณ์ทั้งหมด</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe);">
                    <svg class="w-4.5 h-4.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800">{{ $devices->count() }}</div>
            <div class="text-[10px] text-gray-400 mt-1">{{ $devices->where('status', 'active')->count() }} ใช้งานอยู่</div>
        </div>

        <div class="stat-card card-premium p-4 animate-fade-up delay-100">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">ออนไลน์</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #d1fae5, #a7f3d0);">
                    <svg class="w-4.5 h-4.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
                </div>
            </div>
            @php $onlineCount = $devices->filter(fn($d) => $d->last_active_at && $d->last_active_at->gt(now()->subMinutes(5)))->count(); @endphp
            <div class="text-2xl font-bold text-emerald-600">{{ $onlineCount }}</div>
            <div class="text-[10px] text-emerald-500 mt-1">เชื่อมต่ออยู่</div>
        </div>

        <div class="stat-card card-premium p-4 animate-fade-up delay-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">รอจับคู่</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #fef3c7, #fde68a);">
                    <svg class="w-4.5 h-4.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-amber-600">{{ $pendingCount }}</div>
            <div class="text-[10px] text-amber-500 mt-1">รายการรอดำเนินการ</div>
        </div>

        <div class="stat-card card-premium p-4 animate-fade-up delay-300">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">วันนี้</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ede9fe, #ddd6fe);">
                    <svg class="w-4.5 h-4.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800" x-text="stats.today_count || 0">0</div>
            <div class="text-[10px] text-gray-400 mt-1">SMS ที่รับวันนี้</div>
        </div>
    </div>

    {{-- Devices Table --}}
    <div class="card-premium p-6 animate-fade-up delay-300">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-sm font-bold text-gray-800">อุปกรณ์ SMS Checker</h3>
                <p class="text-xs text-gray-400 mt-0.5">รายการอุปกรณ์ที่ลงทะเบียนแล้ว</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-xs text-gray-400 border-b border-gray-100 uppercase tracking-wide">
                    <th class="pb-3 font-semibold">อุปกรณ์</th>
                    <th class="pb-3 font-semibold">Device ID</th>
                    <th class="pb-3 font-semibold">สถานะ</th>
                    <th class="pb-3 font-semibold">ออนไลน์</th>
                    <th class="pb-3 font-semibold">ล่าสุด</th>
                    <th class="pb-3 font-semibold">จัดการ</th>
                </tr></thead>
                <tbody>
                    @forelse($devices as $device)
                    @php $isOnline = $device->last_active_at && $device->last_active_at->gt(now()->subMinutes(5)); @endphp
                    <tr class="border-b border-gray-50 table-row-hover transition-colors">
                        <td class="py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe);">
                                    <svg class="w-4.5 h-4.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </div>
                                <span class="font-medium text-gray-700">{{ $device->name ?? 'ไม่มีชื่อ' }}</span>
                            </div>
                        </td>
                        <td class="py-3 font-mono text-xs text-gray-500">{{ $device->device_id }}</td>
                        <td class="py-3">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] rounded-full font-semibold
                                {{ $device->status === 'active' ? 'bg-emerald-50 text-emerald-600' : ($device->status === 'blocked' ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-500') }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $device->status === 'active' ? 'bg-emerald-500' : ($device->status === 'blocked' ? 'bg-red-500' : 'bg-gray-400') }}"></span>
                                {{ $device->status }}
                            </span>
                        </td>
                        <td class="py-3">
                            @if($isOnline)
                            <span class="inline-flex items-center gap-1 text-xs text-emerald-600 font-medium">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 badge-pulse"></span> Online
                            </span>
                            @else
                            <span class="text-xs text-gray-400">Offline</span>
                            @endif
                        </td>
                        <td class="py-3 text-xs text-gray-500">{{ $device->last_active_at?->diffForHumans() ?? '-' }}</td>
                        <td class="py-3">
                            <div class="flex items-center gap-1">
                                <button onclick="regenerateKeys({{ $device->id }})" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="สร้าง Key ใหม่">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </button>
                                <button onclick="deleteDevice({{ $device->id }})" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="ลบอุปกรณ์">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center">
                            <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <svg class="w-8 h-8 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-gray-400 text-sm">ยังไม่มีอุปกรณ์ที่ลงทะเบียน</p>
                            <button @click="showAddDevice = true" class="mt-3 btn-premium text-white text-xs px-4 py-2 rounded-xl font-medium">เพิ่มอุปกรณ์แรก</button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add Device Modal --}}
    <div x-show="showAddDevice" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="showAddDevice = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl border border-indigo-100 max-w-md w-full animate-fade-up">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800">เพิ่มอุปกรณ์ SMS Checker</h3>
                <p class="text-xs text-gray-400 mt-1">ลงทะเบียนอุปกรณ์ใหม่สำหรับรับ SMS</p>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">ชื่ออุปกรณ์</label>
                    <input x-model="newDeviceName" type="text" placeholder="เช่น Samsung A54 - สำนักงาน"
                           class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 transition-all">
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                <button @click="showAddDevice = false" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">ยกเลิก</button>
                <button @click="createDevice()" class="btn-premium text-white text-sm px-5 py-2 rounded-xl font-medium">
                    สร้างอุปกรณ์
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function smsPage() {
    return {
        showAddDevice: false,
        newDeviceName: '',
        stats: @json($stats ?? []),

        async createDevice() {
            if (!this.newDeviceName.trim()) {
                showToast('กรุณาใส่ชื่ออุปกรณ์', 'warning');
                return;
            }
            try {
                const res = await fetchApi('{{ route("admin.sms-deposit.devices.create") }}', {
                    method: 'POST',
                    body: JSON.stringify({ name: this.newDeviceName }),
                });
                if (res.success) {
                    showToast('สร้างอุปกรณ์เรียบร้อย', 'success');
                    this.showAddDevice = false;
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(res.message || 'เกิดข้อผิดพลาด', 'error');
                }
            } catch (e) {
                showToast('เกิดข้อผิดพลาด', 'error');
            }
        }
    };
}

async function regenerateKeys(id) {
    if (!confirm('สร้าง Key ใหม่? อุปกรณ์จะต้อง scan QR ใหม่')) return;
    try {
        const res = await fetchApi(`/admin/sms-deposit/devices/${id}/regenerate-keys`, { method: 'POST' });
        if (res.success) {
            showToast('สร้าง Key ใหม่เรียบร้อย', 'success');
            setTimeout(() => location.reload(), 1000);
        }
    } catch (e) {
        showToast('เกิดข้อผิดพลาด', 'error');
    }
}

async function deleteDevice(id) {
    if (!confirm('ลบอุปกรณ์นี้?')) return;
    try {
        const res = await fetchApi(`/admin/sms-deposit/devices/${id}`, { method: 'DELETE' });
        if (res.success) {
            showToast('ลบอุปกรณ์เรียบร้อย', 'success');
            setTimeout(() => location.reload(), 1000);
        }
    } catch (e) {
        showToast('เกิดข้อผิดพลาด', 'error');
    }
}
</script>
@endpush
