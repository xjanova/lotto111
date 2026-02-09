@extends('admin.layouts.app')
@section('title', 'ตั้งค่าระบบ')
@section('page-title', 'ตั้งค่าระบบ')
@section('breadcrumb') <span class="text-gray-700">ตั้งค่า</span> @endsection

@section('content')
<div x-data="settingsPage()" class="space-y-4 animate-fade-in">
    {{-- Tabs --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="flex border-b border-gray-100 overflow-x-auto">
            <button @click="tab='general'" :class="tab==='general' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-5 py-3 text-sm font-medium border-b-2 whitespace-nowrap">ทั่วไป / แบรนด์</button>
            <button @click="tab='fees'" :class="tab==='fees' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-5 py-3 text-sm font-medium border-b-2 whitespace-nowrap">ค่าธรรมเนียม</button>
            <button @click="tab='payment'" :class="tab==='payment' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-5 py-3 text-sm font-medium border-b-2 whitespace-nowrap">ฝาก/ถอน</button>
            <button @click="tab='lottery'" :class="tab==='lottery' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-5 py-3 text-sm font-medium border-b-2 whitespace-nowrap">หวย/แทง</button>
            <button @click="tab='affiliate'" :class="tab==='affiliate' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-5 py-3 text-sm font-medium border-b-2 whitespace-nowrap">Affiliate</button>
            <button @click="tab='contact'" :class="tab==='contact' ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-5 py-3 text-sm font-medium border-b-2 whitespace-nowrap">ติดต่อ</button>
        </div>
    </div>

    {{-- General / Branding --}}
    <div x-show="tab==='general'" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-1">ข้อมูลเว็บไซต์ & แบรนด์</h3>
        <p class="text-sm text-gray-400 mb-6">ตั้งค่าชื่อเว็บ โลโก้ สี และข้อมูลพื้นฐาน</p>
        <form @submit.prevent="saveSettings('general')" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">ชื่อเว็บไซต์</label>
                    <input type="text" x-model="settings.site_name" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none" placeholder="Lotto111">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">ชื่อเว็บ (ภาษาไทย)</label>
                    <input type="text" x-model="settings.site_name_th" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none" placeholder="ล็อตโต้ 111">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">URL โลโก้</label>
                    <input type="url" x-model="settings.site_logo_url" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none" placeholder="https://example.com/logo.png">
                    <template x-if="settings.site_logo_url">
                        <img :src="settings.site_logo_url" class="h-10 mt-2 rounded" alt="Logo Preview">
                    </template>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">URL Favicon</label>
                    <input type="url" x-model="settings.site_favicon_url" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none" placeholder="https://example.com/favicon.ico">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">สีหลัก (Primary Color)</label>
                    <div class="flex gap-2 items-center">
                        <input type="color" x-model="settings.primary_color" class="w-10 h-10 rounded-lg border border-gray-200 cursor-pointer">
                        <input type="text" x-model="settings.primary_color" class="flex-1 px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-mono" placeholder="#3b82f6">
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">สีรอง (Secondary Color)</label>
                    <div class="flex gap-2 items-center">
                        <input type="color" x-model="settings.secondary_color" class="w-10 h-10 rounded-lg border border-gray-200 cursor-pointer">
                        <input type="text" x-model="settings.secondary_color" class="flex-1 px-4 py-2.5 border border-gray-200 rounded-lg text-sm font-mono" placeholder="#10b981">
                    </div>
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 mb-1.5 block">คำอธิบายเว็บ (SEO Description)</label>
                <textarea x-model="settings.site_description" rows="2" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent outline-none" placeholder="เว็บหวยออนไลน์ อัตราจ่ายสูง"></textarea>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 mb-1.5 block">ข้อความวิ่ง (Marquee)</label>
                <input type="text" x-model="settings.marquee_text" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm" placeholder="ยินดีต้อนรับ...">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 mb-1.5 block">ประกาศ</label>
                <textarea x-model="settings.announcement" rows="3" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm" placeholder="ประกาศสำคัญ..."></textarea>
            </div>
            <div class="flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" x-model="settings.maintenance_mode" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-red-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                </label>
                <span class="text-sm text-gray-700">โหมดปิดปรับปรุง (Maintenance Mode)</span>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-brand-600 text-white rounded-lg text-sm font-medium hover:bg-brand-700 transition-colors">บันทึกการตั้งค่า</button>
            </div>
        </form>
    </div>

    {{-- Fees --}}
    <div x-show="tab==='fees'" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-1">ค่าธรรมเนียม (Fee)</h3>
        <p class="text-sm text-gray-400 mb-6">กำหนดค่าธรรมเนียมฝากเงินและถอนเงิน</p>
        <form @submit.prevent="saveSettings('fees')" class="space-y-6">
            {{-- Deposit Fee --}}
            <div class="border border-gray-100 rounded-xl p-5">
                <h4 class="font-medium text-gray-700 mb-4 flex items-center gap-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full"></div> ค่าธรรมเนียมฝากเงิน
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm text-gray-600 mb-1 block">ประเภท</label>
                        <select x-model="settings.deposit_fee_type" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                            <option value="none">ไม่เก็บ</option>
                            <option value="fixed">คงที่ (บาท)</option>
                            <option value="percent">เปอร์เซ็นต์ (%)</option>
                            <option value="mixed">คงที่ + เปอร์เซ็นต์</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 mb-1 block">ค่าคงที่ (บาท)</label>
                        <input type="number" x-model="settings.deposit_fee_fixed" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm" placeholder="0.00">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 mb-1 block">เปอร์เซ็นต์ (%)</label>
                        <input type="number" x-model="settings.deposit_fee_percent" step="0.01" min="0" max="100" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm" placeholder="0.00">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                    <div>
                        <label class="text-sm text-gray-600 mb-1 block">ค่าธรรมเนียมขั้นต่ำ (บาท)</label>
                        <input type="number" x-model="settings.deposit_fee_min" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm" placeholder="0">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 mb-1 block">ค่าธรรมเนียมสูงสุด (บาท)</label>
                        <input type="number" x-model="settings.deposit_fee_max" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm" placeholder="0 = ไม่จำกัด">
                    </div>
                </div>
            </div>

            {{-- Withdrawal Fee --}}
            <div class="border border-gray-100 rounded-xl p-5">
                <h4 class="font-medium text-gray-700 mb-4 flex items-center gap-2">
                    <div class="w-2 h-2 bg-orange-500 rounded-full"></div> ค่าธรรมเนียมถอนเงิน
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm text-gray-600 mb-1 block">ประเภท</label>
                        <select x-model="settings.withdrawal_fee_type" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                            <option value="none">ไม่เก็บ</option>
                            <option value="fixed">คงที่ (บาท)</option>
                            <option value="percent">เปอร์เซ็นต์ (%)</option>
                            <option value="mixed">คงที่ + เปอร์เซ็นต์</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 mb-1 block">ค่าคงที่ (บาท)</label>
                        <input type="number" x-model="settings.withdrawal_fee_fixed" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm" placeholder="0.00">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 mb-1 block">เปอร์เซ็นต์ (%)</label>
                        <input type="number" x-model="settings.withdrawal_fee_percent" step="0.01" min="0" max="100" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm" placeholder="0.00">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                    <div>
                        <label class="text-sm text-gray-600 mb-1 block">ค่าธรรมเนียมขั้นต่ำ (บาท)</label>
                        <input type="number" x-model="settings.withdrawal_fee_min" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 mb-1 block">ค่าธรรมเนียมสูงสุด (บาท)</label>
                        <input type="number" x-model="settings.withdrawal_fee_max" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm" placeholder="0 = ไม่จำกัด">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="text-sm text-gray-600 mb-1 block">ถอนฟรี/วัน</label>
                    <input type="number" x-model="settings.free_withdrawals_per_day" min="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm" placeholder="0 = เก็บทุกครั้ง">
                    <div class="text-xs text-gray-400 mt-1">ถอนฟรีกี่ครั้งต่อวัน ก่อนเริ่มเก็บค่าธรรมเนียม</div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-brand-600 text-white rounded-lg text-sm font-medium hover:bg-brand-700 transition-colors">บันทึกค่าธรรมเนียม</button>
            </div>
        </form>
    </div>

    {{-- Payment --}}
    <div x-show="tab==='payment'" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-1">ตั้งค่าฝาก/ถอน</h3>
        <p class="text-sm text-gray-400 mb-6">กำหนดขั้นต่ำ-สูงสุด และการอนุมัติอัตโนมัติ</p>
        <form @submit.prevent="saveSettings('payment')" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">ฝากขั้นต่ำ (บาท)</label>
                    <input type="number" x-model="settings.min_deposit" min="1" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">ฝากสูงสุด (บาท)</label>
                    <input type="number" x-model="settings.max_deposit" min="1" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">ถอนขั้นต่ำ (บาท)</label>
                    <input type="number" x-model="settings.min_withdrawal" min="1" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">ถอนสูงสุด/วัน (บาท)</label>
                    <input type="number" x-model="settings.daily_withdrawal_limit" min="0" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm">
                </div>
            </div>
            <div class="flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" x-model="settings.auto_approve_deposit" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-green-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                </label>
                <span class="text-sm text-gray-700">อนุมัติฝากอัตโนมัติ</span>
            </div>
            <div x-show="settings.auto_approve_deposit">
                <label class="text-sm font-medium text-gray-700 mb-1.5 block">อนุมัติอัตโนมัติสูงสุด (บาท)</label>
                <input type="number" x-model="settings.auto_approve_max_amount" min="0" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm" placeholder="ยอดที่มากกว่านี้ต้องอนุมัติมือ">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-brand-600 text-white rounded-lg text-sm font-medium hover:bg-brand-700">บันทึก</button>
            </div>
        </form>
    </div>

    {{-- Lottery --}}
    <div x-show="tab==='lottery'" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-1">ตั้งค่าหวย/การแทง</h3>
        <p class="text-sm text-gray-400 mb-6">กำหนดขั้นต่ำ-สูงสุดการแทง และการจ่ายรางวัล</p>
        <form @submit.prevent="saveSettings('lottery')" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">แทงขั้นต่ำ (บาท)</label>
                    <input type="number" x-model="settings.min_bet" min="1" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">แทงสูงสุด (บาท)</label>
                    <input type="number" x-model="settings.max_bet" min="1" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">จ่ายสูงสุด/โพย (บาท)</label>
                    <input type="number" x-model="settings.max_payout_per_ticket" min="1" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-brand-600 text-white rounded-lg text-sm font-medium hover:bg-brand-700">บันทึก</button>
            </div>
        </form>
    </div>

    {{-- Affiliate --}}
    <div x-show="tab==='affiliate'" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-1">ตั้งค่า Affiliate</h3>
        <form @submit.prevent="saveSettings('affiliate')" class="space-y-5">
            <div class="flex items-center gap-3">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" x-model="settings.affiliate_enabled" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-green-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                </label>
                <span class="text-sm text-gray-700">เปิดระบบ Affiliate</span>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 mb-1.5 block">ค่าคอมมิชชั่น (%)</label>
                <input type="number" x-model="settings.affiliate_commission_rate" step="0.01" min="0" max="100" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-brand-600 text-white rounded-lg text-sm font-medium hover:bg-brand-700">บันทึก</button>
            </div>
        </form>
    </div>

    {{-- Contact --}}
    <div x-show="tab==='contact'" class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-1">ข้อมูลติดต่อ</h3>
        <form @submit.prevent="saveSettings('contact')" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">LINE ID</label>
                    <input type="text" x-model="settings.line_id" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm" placeholder="@lotto111">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">เบอร์แอดมิน</label>
                    <input type="text" x-model="settings.admin_phone" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm" placeholder="0812345678">
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 mb-1.5 block">Footer Text</label>
                <input type="text" x-model="settings.footer_text" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm" placeholder="© 2024 Lotto111 All rights reserved.">
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-brand-600 text-white rounded-lg text-sm font-medium hover:bg-brand-700">บันทึก</button>
            </div>
        </form>
    </div>

    {{-- Toast --}}
    <div x-show="showToast" x-cloak x-transition
         class="fixed bottom-6 right-6 bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-2 z-50">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span x-text="toastMsg"></span>
    </div>
</div>
@endsection

@push('scripts')
<script>
function settingsPage() {
    return {
        tab: 'general',
        showToast: false,
        toastMsg: '',
        settings: @json($settings ?? []),

        async saveSettings(group) {
            const res = await fetchApi('{{ route("admin.settings.update") }}', {
                method: 'PUT',
                body: JSON.stringify({ group, settings: this.settings })
            });
            if (res.success !== false) {
                this.toast('บันทึกสำเร็จ');
            } else {
                alert(res.message || 'เกิดข้อผิดพลาด');
            }
        },

        toast(msg) {
            this.toastMsg = msg;
            this.showToast = true;
            setTimeout(() => this.showToast = false, 3000);
        }
    }
}
</script>
@endpush
