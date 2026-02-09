@extends('admin.layouts.app')
@section('title', 'ตั้งค่าระบบ')
@section('page-title', 'ตั้งค่าระบบ')
@section('breadcrumb') <span class="text-gray-700">ตั้งค่า</span> @endsection

@section('content')
<div x-data="settingsPage()" class="space-y-6">

    {{-- Hero Banner --}}
    <div class="relative overflow-hidden rounded-2xl p-6 md:p-8 animate-fade-up" style="background: linear-gradient(135deg, #4f46e5, #6366f1, #8b5cf6);">
        <div class="absolute top-0 right-0 w-72 h-72 bg-white/10 rounded-full filter blur-3xl animate-blob"></div>
        <div class="absolute bottom-0 left-0 w-72 h-72 bg-purple-300/10 rounded-full filter blur-3xl animate-blob delay-200"></div>
        <div class="relative z-10">
            <h2 class="text-2xl font-bold text-white mb-1">ตั้งค่าระบบ</h2>
            <p class="text-white/70 text-sm">จัดการข้อมูลแบรนด์ ค่าธรรมเนียม ช่องทางฝากถอน และอื่นๆ</p>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="card-premium animate-fade-up delay-100">
        <div class="flex border-b border-indigo-100 overflow-x-auto">
            @php
                $tabList = [
                    ['key' => 'general', 'label' => 'ทั่วไป / แบรนด์', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                    ['key' => 'fees', 'label' => 'ค่าธรรมเนียม', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                    ['key' => 'payment', 'label' => 'ฝาก/ถอน', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                    ['key' => 'lottery', 'label' => 'หวย/แทง', 'icon' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
                    ['key' => 'affiliate', 'label' => 'Affiliate', 'icon' => 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1'],
                    ['key' => 'contact', 'label' => 'ติดต่อ', 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                ];
            @endphp
            @foreach($tabList as $t)
            <button @click="tab='{{ $t['key'] }}'"
                    :class="tab==='{{ $t['key'] }}' ? 'border-indigo-600 text-indigo-600 bg-indigo-50/50' : 'border-transparent text-gray-400 hover:text-indigo-600 hover:bg-indigo-50/30'"
                    class="flex items-center gap-2 px-5 py-3.5 text-sm font-medium border-b-2 whitespace-nowrap transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $t['icon'] }}"/></svg>
                {{ $t['label'] }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- General / Branding --}}
    <div x-show="tab==='general'" x-transition class="card-premium p-6 animate-fade-up delay-200">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">ข้อมูลเว็บไซต์ & แบรนด์</h3>
                <p class="text-sm text-gray-400">ตั้งค่าชื่อเว็บ โลโก้ สี และข้อมูลพื้นฐาน</p>
            </div>
        </div>
        <form @submit.prevent="saveSettings('general')" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">ชื่อเว็บไซต์</label>
                    <input type="text" x-model="settings.site_name" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="Lotto111">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">ชื่อเว็บ (ภาษาไทย)</label>
                    <input type="text" x-model="settings.site_name_th" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="ล็อตโต้ 111">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">URL โลโก้</label>
                    <input type="url" x-model="settings.site_logo_url" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="https://example.com/logo.png">
                    <template x-if="settings.site_logo_url">
                        <img :src="settings.site_logo_url" class="h-10 mt-2 rounded-lg border border-indigo-100" alt="Logo Preview">
                    </template>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">URL Favicon</label>
                    <input type="url" x-model="settings.site_favicon_url" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="https://example.com/favicon.ico">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">สีหลัก (Primary Color)</label>
                    <div class="flex gap-2 items-center">
                        <input type="color" x-model="settings.primary_color" class="w-12 h-12 rounded-xl border border-indigo-100 cursor-pointer p-1">
                        <input type="text" x-model="settings.primary_color" class="flex-1 px-4 py-2.5 border border-indigo-100 rounded-xl text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="#3b82f6">
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">สีรอง (Secondary Color)</label>
                    <div class="flex gap-2 items-center">
                        <input type="color" x-model="settings.secondary_color" class="w-12 h-12 rounded-xl border border-indigo-100 cursor-pointer p-1">
                        <input type="text" x-model="settings.secondary_color" class="flex-1 px-4 py-2.5 border border-indigo-100 rounded-xl text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="#10b981">
                    </div>
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 mb-1.5 block">คำอธิบายเว็บ (SEO Description)</label>
                <textarea x-model="settings.site_description" rows="2" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="เว็บหวยออนไลน์ อัตราจ่ายสูง"></textarea>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 mb-1.5 block">ข้อความวิ่ง (Marquee)</label>
                <input type="text" x-model="settings.marquee_text" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="ยินดีต้อนรับ...">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 mb-1.5 block">ประกาศ</label>
                <textarea x-model="settings.announcement" rows="3" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="ประกาศสำคัญ..."></textarea>
            </div>
            <div class="flex items-center gap-3 p-4 bg-gradient-to-r from-red-50 to-orange-50 rounded-xl border border-red-100">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" x-model="settings.maintenance_mode" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-red-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                </label>
                <div>
                    <span class="text-sm font-medium text-gray-700">โหมดปิดปรับปรุง (Maintenance Mode)</span>
                    <p class="text-xs text-gray-400">เมื่อเปิดใช้งาน ผู้ใช้จะไม่สามารถเข้าถึงเว็บไซต์ได้</p>
                </div>
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" class="btn-premium text-white rounded-xl text-sm font-medium px-8 py-2.5 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    บันทึกการตั้งค่า
                </button>
            </div>
        </form>
    </div>

    {{-- Fees --}}
    <div x-show="tab==='fees'" x-transition class="card-premium p-6 animate-fade-up delay-200">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">ค่าธรรมเนียม (Fee)</h3>
                <p class="text-sm text-gray-400">กำหนดค่าธรรมเนียมฝากเงินและถอนเงิน</p>
            </div>
        </div>
        <form @submit.prevent="saveSettings('fees')" class="space-y-6">
            {{-- Deposit Fee --}}
            <div class="border border-indigo-100 rounded-2xl p-5 bg-gradient-to-br from-white to-emerald-50/20">
                <h4 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #d1fae5, #a7f3d0);">
                        <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                    </div>
                    ค่าธรรมเนียมฝากเงิน
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm text-gray-600 mb-1.5 block">ประเภท</label>
                        <select x-model="settings.deposit_fee_type" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none bg-white transition-all">
                            <option value="none">ไม่เก็บ</option>
                            <option value="fixed">คงที่ (บาท)</option>
                            <option value="percent">เปอร์เซ็นต์ (%)</option>
                            <option value="mixed">คงที่ + เปอร์เซ็นต์</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 mb-1.5 block">ค่าคงที่ (บาท)</label>
                        <input type="number" x-model="settings.deposit_fee_fixed" step="0.01" min="0" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="0.00">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 mb-1.5 block">เปอร์เซ็นต์ (%)</label>
                        <input type="number" x-model="settings.deposit_fee_percent" step="0.01" min="0" max="100" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="0.00">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="text-sm text-gray-600 mb-1.5 block">ค่าธรรมเนียมขั้นต่ำ (บาท)</label>
                        <input type="number" x-model="settings.deposit_fee_min" step="0.01" min="0" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="0">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 mb-1.5 block">ค่าธรรมเนียมสูงสุด (บาท)</label>
                        <input type="number" x-model="settings.deposit_fee_max" step="0.01" min="0" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="0 = ไม่จำกัด">
                    </div>
                </div>
            </div>

            {{-- Withdrawal Fee --}}
            <div class="border border-indigo-100 rounded-2xl p-5 bg-gradient-to-br from-white to-orange-50/20">
                <h4 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #ffedd5, #fed7aa);">
                        <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                    </div>
                    ค่าธรรมเนียมถอนเงิน
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm text-gray-600 mb-1.5 block">ประเภท</label>
                        <select x-model="settings.withdrawal_fee_type" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none bg-white transition-all">
                            <option value="none">ไม่เก็บ</option>
                            <option value="fixed">คงที่ (บาท)</option>
                            <option value="percent">เปอร์เซ็นต์ (%)</option>
                            <option value="mixed">คงที่ + เปอร์เซ็นต์</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 mb-1.5 block">ค่าคงที่ (บาท)</label>
                        <input type="number" x-model="settings.withdrawal_fee_fixed" step="0.01" min="0" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="0.00">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 mb-1.5 block">เปอร์เซ็นต์ (%)</label>
                        <input type="number" x-model="settings.withdrawal_fee_percent" step="0.01" min="0" max="100" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="0.00">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="text-sm text-gray-600 mb-1.5 block">ค่าธรรมเนียมขั้นต่ำ (บาท)</label>
                        <input type="number" x-model="settings.withdrawal_fee_min" step="0.01" min="0" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600 mb-1.5 block">ค่าธรรมเนียมสูงสุด (บาท)</label>
                        <input type="number" x-model="settings.withdrawal_fee_max" step="0.01" min="0" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="0 = ไม่จำกัด">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="text-sm text-gray-600 mb-1.5 block">ถอนฟรี/วัน</label>
                    <input type="number" x-model="settings.free_withdrawals_per_day" min="0" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="0 = เก็บทุกครั้ง">
                    <div class="text-xs text-gray-400 mt-1.5">ถอนฟรีกี่ครั้งต่อวัน ก่อนเริ่มเก็บค่าธรรมเนียม</div>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="btn-premium text-white rounded-xl text-sm font-medium px-8 py-2.5 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    บันทึกค่าธรรมเนียม
                </button>
            </div>
        </form>
    </div>

    {{-- Payment --}}
    <div x-show="tab==='payment'" x-transition class="card-premium p-6 animate-fade-up delay-200">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">ตั้งค่าฝาก/ถอน</h3>
                <p class="text-sm text-gray-400">กำหนดขั้นต่ำ-สูงสุด และการอนุมัติอัตโนมัติ</p>
            </div>
        </div>
        <form @submit.prevent="saveSettings('payment')" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">ฝากขั้นต่ำ (บาท)</label>
                    <input type="number" x-model="settings.min_deposit" min="1" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">ฝากสูงสุด (บาท)</label>
                    <input type="number" x-model="settings.max_deposit" min="1" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">ถอนขั้นต่ำ (บาท)</label>
                    <input type="number" x-model="settings.min_withdrawal" min="1" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">ถอนสูงสุด/วัน (บาท)</label>
                    <input type="number" x-model="settings.daily_withdrawal_limit" min="0" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
                </div>
            </div>
            <div class="flex items-center gap-3 p-4 bg-gradient-to-r from-emerald-50 to-green-50 rounded-xl border border-emerald-100">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" x-model="settings.auto_approve_deposit" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-emerald-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                </label>
                <div>
                    <span class="text-sm font-medium text-gray-700">อนุมัติฝากอัตโนมัติ</span>
                    <p class="text-xs text-gray-400">ระบบจะอนุมัติรายการฝากอัตโนมัติ</p>
                </div>
            </div>
            <div x-show="settings.auto_approve_deposit" x-transition>
                <label class="text-sm font-medium text-gray-700 mb-1.5 block">อนุมัติอัตโนมัติสูงสุด (บาท)</label>
                <input type="number" x-model="settings.auto_approve_max_amount" min="0" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="ยอดที่มากกว่านี้ต้องอนุมัติมือ">
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" class="btn-premium text-white rounded-xl text-sm font-medium px-8 py-2.5 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    บันทึก
                </button>
            </div>
        </form>
    </div>

    {{-- Lottery --}}
    <div x-show="tab==='lottery'" x-transition class="card-premium p-6 animate-fade-up delay-200">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">ตั้งค่าหวย/การแทง</h3>
                <p class="text-sm text-gray-400">กำหนดขั้นต่ำ-สูงสุดการแทง และการจ่ายรางวัล</p>
            </div>
        </div>
        <form @submit.prevent="saveSettings('lottery')" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">แทงขั้นต่ำ (บาท)</label>
                    <input type="number" x-model="settings.min_bet" min="1" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">แทงสูงสุด (บาท)</label>
                    <input type="number" x-model="settings.max_bet" min="1" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">จ่ายสูงสุด/โพย (บาท)</label>
                    <input type="number" x-model="settings.max_payout_per_ticket" min="1" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
                </div>
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" class="btn-premium text-white rounded-xl text-sm font-medium px-8 py-2.5 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    บันทึก
                </button>
            </div>
        </form>
    </div>

    {{-- Affiliate --}}
    <div x-show="tab==='affiliate'" x-transition class="card-premium p-6 animate-fade-up delay-200">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">ตั้งค่า Affiliate</h3>
                <p class="text-sm text-gray-400">จัดการระบบแนะนำเพื่อนและค่าคอมมิชชั่น</p>
            </div>
        </div>
        <form @submit.prevent="saveSettings('affiliate')" class="space-y-5">
            <div class="flex items-center gap-3 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl border border-indigo-100">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" x-model="settings.affiliate_enabled" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-indigo-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                </label>
                <div>
                    <span class="text-sm font-medium text-gray-700">เปิดระบบ Affiliate</span>
                    <p class="text-xs text-gray-400">อนุญาตให้ผู้ใช้แนะนำเพื่อนรับค่าคอมมิชชั่น</p>
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 mb-1.5 block">ค่าคอมมิชชั่น (%)</label>
                <input type="number" x-model="settings.affiliate_commission_rate" step="0.01" min="0" max="100" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" class="btn-premium text-white rounded-xl text-sm font-medium px-8 py-2.5 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    บันทึก
                </button>
            </div>
        </form>
    </div>

    {{-- Contact --}}
    <div x-show="tab==='contact'" x-transition class="card-premium p-6 animate-fade-up delay-200">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">ข้อมูลติดต่อ</h3>
                <p class="text-sm text-gray-400">ตั้งค่าช่องทางการติดต่อและ Footer</p>
            </div>
        </div>
        <form @submit.prevent="saveSettings('contact')" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">LINE ID</label>
                    <input type="text" x-model="settings.line_id" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="@lotto111">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1.5 block">เบอร์แอดมิน</label>
                    <input type="text" x-model="settings.admin_phone" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="0812345678">
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700 mb-1.5 block">Footer Text</label>
                <input type="text" x-model="settings.footer_text" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="© 2024 Lotto111 All rights reserved.">
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" class="btn-premium text-white rounded-xl text-sm font-medium px-8 py-2.5 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    บันทึก
                </button>
            </div>
        </form>
    </div>

    {{-- Toast --}}
    <div x-show="showToast" x-cloak x-transition
         class="fixed bottom-6 right-6 z-50 overflow-hidden rounded-xl shadow-2xl animate-fade-up" style="background: linear-gradient(135deg, #6366f1, #4f46e5);">
        <div class="flex items-center gap-3 px-5 py-3.5 text-white">
            <div class="w-7 h-7 rounded-lg bg-white/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <span class="text-sm font-medium" x-text="toastMsg"></span>
        </div>
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
