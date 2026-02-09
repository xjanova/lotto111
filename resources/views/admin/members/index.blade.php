@extends('admin.layouts.app')
@section('title', 'จัดการสมาชิก')
@section('page-title', 'จัดการสมาชิก')
@section('breadcrumb') <span class="text-gray-700">จัดการสมาชิก</span> @endsection

@section('content')
<div x-data="membersPage()" x-init="load()" class="space-y-4 animate-fade-in">
    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <div class="flex flex-wrap gap-3 items-center">
            <input type="text" x-model="search" @input.debounce.300ms="load()"
                   placeholder="ค้นหาชื่อ, เบอร์, อีเมล..." class="flex-1 min-w-[200px] px-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none">
            <select x-model="statusFilter" @change="load()" class="px-4 py-2 border border-gray-200 rounded-lg text-sm bg-white">
                <option value="">ทุกสถานะ</option>
                <option value="active">ใช้งาน</option>
                <option value="suspended">ระงับ</option>
                <option value="banned">แบน</option>
            </select>
            <div class="text-sm text-gray-400">พบ <span x-text="total" class="font-medium text-gray-700"></span> รายการ</div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-xs text-gray-400 bg-gray-50 border-b border-gray-100">
                    <th class="px-4 py-3 font-medium">ID</th>
                    <th class="px-4 py-3 font-medium">ชื่อ</th>
                    <th class="px-4 py-3 font-medium">เบอร์โทร</th>
                    <th class="px-4 py-3 font-medium">ยอดเงิน</th>
                    <th class="px-4 py-3 font-medium">VIP</th>
                    <th class="px-4 py-3 font-medium">สถานะ</th>
                    <th class="px-4 py-3 font-medium">สมัครเมื่อ</th>
                    <th class="px-4 py-3 font-medium">จัดการ</th>
                </tr></thead>
                <tbody>
                    <template x-for="m in members" :key="m.id">
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-500" x-text="m.id"></td>
                            <td class="px-4 py-3 font-medium text-gray-800" x-text="m.name"></td>
                            <td class="px-4 py-3 text-gray-600" x-text="m.phone || '-'"></td>
                            <td class="px-4 py-3 font-medium text-gray-700">฿<span x-text="fmtMoney(m.balance)"></span></td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 text-xs rounded-full bg-purple-50 text-purple-600" x-text="'Lv.' + (m.vip_level || 0)"></span></td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs rounded-full"
                                    :class="m.status==='active' ? 'bg-green-50 text-green-600' : m.status==='suspended' ? 'bg-yellow-50 text-yellow-600' : 'bg-red-50 text-red-600'"
                                    x-text="m.status==='active' ? 'ใช้งาน' : m.status==='suspended' ? 'ระงับ' : 'แบน'"></span>
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs" x-text="m.created_at"></td>
                            <td class="px-4 py-3">
                                <div class="flex gap-1">
                                    <a :href="'/admin/members/' + m.id" class="px-2 py-1 text-xs bg-brand-50 text-brand-600 rounded hover:bg-brand-100 transition-colors">ดู</a>
                                    <button @click="openCreditModal(m)" class="px-2 py-1 text-xs bg-green-50 text-green-600 rounded hover:bg-green-100 transition-colors">เครดิต</button>
                                    <button @click="openStatusModal(m)" class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded hover:bg-gray-200 transition-colors">สถานะ</button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="members.length === 0 && !loading">
                        <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">ไม่พบสมาชิก</td></tr>
                    </template>
                </tbody>
            </table>
        </div>
        {{-- Pagination --}}
        <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
            <span>หน้า <span x-text="page"></span> จาก <span x-text="lastPage"></span></span>
            <div class="flex gap-2">
                <button @click="page > 1 && (page--, load())" :disabled="page <= 1" class="px-3 py-1 border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-40">ก่อนหน้า</button>
                <button @click="page < lastPage && (page++, load())" :disabled="page >= lastPage" class="px-3 py-1 border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-40">ถัดไป</button>
            </div>
        </div>
    </div>

    {{-- Credit Modal --}}
    <div x-show="showCreditModal" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showCreditModal=false">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 animate-fade-in">
            <h3 class="text-lg font-semibold mb-4">ปรับเครดิต - <span x-text="selectedMember?.name"></span></h3>
            <div class="space-y-3">
                <div>
                    <label class="text-sm text-gray-600 mb-1 block">ยอดปัจจุบัน</label>
                    <div class="text-lg font-bold text-gray-800">฿<span x-text="fmtMoney(selectedMember?.balance)"></span></div>
                </div>
                <div>
                    <label class="text-sm text-gray-600 mb-1 block">ประเภท</label>
                    <select x-model="creditForm.type" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                        <option value="add">เพิ่มเครดิต</option>
                        <option value="deduct">หักเครดิต</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm text-gray-600 mb-1 block">จำนวน (บาท)</label>
                    <input type="number" x-model="creditForm.amount" min="1" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm" placeholder="0.00">
                </div>
                <div>
                    <label class="text-sm text-gray-600 mb-1 block">หมายเหตุ</label>
                    <input type="text" x-model="creditForm.reason" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm" placeholder="เหตุผลการปรับ">
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button @click="showCreditModal=false" class="flex-1 px-4 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-50">ยกเลิก</button>
                <button @click="submitCredit()" class="flex-1 px-4 py-2 bg-brand-600 text-white rounded-lg text-sm hover:bg-brand-700">ยืนยัน</button>
            </div>
        </div>
    </div>

    {{-- Status Modal --}}
    <div x-show="showStatusModal" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showStatusModal=false">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 animate-fade-in">
            <h3 class="text-lg font-semibold mb-4">เปลี่ยนสถานะ - <span x-text="selectedMember?.name"></span></h3>
            <div class="space-y-2">
                <button @click="updateStatus('active')" class="w-full text-left px-4 py-3 rounded-lg hover:bg-green-50 flex items-center gap-3 transition-colors">
                    <span class="w-3 h-3 bg-green-500 rounded-full"></span> <span class="text-sm">ใช้งาน (Active)</span>
                </button>
                <button @click="updateStatus('suspended')" class="w-full text-left px-4 py-3 rounded-lg hover:bg-yellow-50 flex items-center gap-3 transition-colors">
                    <span class="w-3 h-3 bg-yellow-500 rounded-full"></span> <span class="text-sm">ระงับชั่วคราว (Suspended)</span>
                </button>
                <button @click="updateStatus('banned')" class="w-full text-left px-4 py-3 rounded-lg hover:bg-red-50 flex items-center gap-3 transition-colors">
                    <span class="w-3 h-3 bg-red-500 rounded-full"></span> <span class="text-sm">แบนถาวร (Banned)</span>
                </button>
            </div>
            <button @click="showStatusModal=false" class="w-full mt-4 px-4 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-50">ยกเลิก</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function membersPage() {
    return {
        members: @json($members ?? []),
        total: {{ $total ?? 0 }},
        page: {{ $page ?? 1 }},
        lastPage: {{ $lastPage ?? 1 }},
        search: '{{ request('search', '') }}',
        statusFilter: '{{ request('status', '') }}',
        loading: false,
        showCreditModal: false,
        showStatusModal: false,
        selectedMember: null,
        creditForm: { type: 'add', amount: '', reason: '' },

        load() {
            const params = new URLSearchParams({ page: this.page, search: this.search, status: this.statusFilter });
            window.location.href = '{{ route("admin.members.index") }}?' + params.toString();
        },

        openCreditModal(m) { this.selectedMember = m; this.creditForm = { type: 'add', amount: '', reason: '' }; this.showCreditModal = true; },
        openStatusModal(m) { this.selectedMember = m; this.showStatusModal = true; },

        async submitCredit() {
            if (!this.creditForm.amount || this.creditForm.amount <= 0) return alert('กรุณากรอกจำนวน');
            const res = await fetchApi('/admin/members/' + this.selectedMember.id + '/credit', {
                method: 'POST', body: JSON.stringify(this.creditForm)
            });
            if (res.success) { this.showCreditModal = false; location.reload(); }
            else alert(res.message || 'เกิดข้อผิดพลาด');
        },

        async updateStatus(status) {
            const res = await fetchApi('/admin/members/' + this.selectedMember.id + '/status', {
                method: 'PUT', body: JSON.stringify({ status })
            });
            if (res.success) { this.showStatusModal = false; location.reload(); }
            else alert(res.message || 'เกิดข้อผิดพลาด');
        },

        fmtMoney(n) { return new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2 }).format(n || 0); },
    }
}
</script>
@endpush
