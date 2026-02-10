@extends('admin.layouts.app')
@section('title', 'จัดการสมาชิก')
@section('page-title', 'จัดการสมาชิก')
@section('breadcrumb') <span class="text-gray-700">จัดการสมาชิก</span> @endsection

@section('content')
<div x-data="membersPage()" class="space-y-6">

    {{-- Page Hero --}}
    <div class="relative overflow-hidden rounded-2xl p-5 md:p-6" style="background: linear-gradient(135deg, #4f46e5, #7c3aed, #a855f7);">
        <div class="absolute top-0 left-0 w-56 h-56 bg-white/10 rounded-full filter blur-3xl animate-blob"></div>
        <div class="absolute bottom-0 right-0 w-56 h-56 bg-purple-300/10 rounded-full filter blur-3xl animate-blob delay-300"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-white mb-1 flex items-center gap-2">
                    <svg class="w-6 h-6 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    จัดการสมาชิก
                </h2>
                <p class="text-white/70 text-sm">ค้นหา ดูข้อมูล และจัดการสมาชิกทั้งหมดในระบบ</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="px-4 py-2 bg-white/20 backdrop-blur-sm rounded-xl text-white text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span x-text="total"></span> สมาชิก
                </div>
            </div>
        </div>
    </div>

    {{-- Search & Filters --}}
    <div class="card-premium p-4 animate-fade-up">
        <div class="flex flex-wrap gap-3 items-center">
            <div class="relative flex-1 min-w-[220px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="search" @input.debounce.300ms="load()"
                       placeholder="ค้นหาชื่อ, เบอร์โทร, อีเมล..."
                       class="w-full pl-10 pr-4 py-2.5 border border-indigo-100 rounded-xl text-sm bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 outline-none transition-all placeholder-gray-400">
            </div>
            <select x-model="statusFilter" @change="load()"
                    class="px-4 py-2.5 border border-indigo-100 rounded-xl text-sm bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 outline-none transition-all cursor-pointer">
                <option value="">ทุกสถานะ</option>
                <option value="active">ใช้งาน</option>
                <option value="suspended">ระงับ</option>
                <option value="banned">แบน</option>
            </select>
            <div class="flex items-center gap-2 text-sm text-gray-400">
                <div class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></div>
                พบ <span x-text="total" class="font-semibold text-gray-700"></span> รายการ
            </div>
        </div>
    </div>

    {{-- Members Table --}}
    <div class="card-premium overflow-hidden animate-fade-up delay-100">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-400 uppercase tracking-wide border-b border-indigo-100" style="background: linear-gradient(135deg, rgba(238,242,255,0.7), rgba(224,231,255,0.5));">
                        <th class="px-5 py-3.5 font-semibold">ID</th>
                        <th class="px-5 py-3.5 font-semibold">สมาชิก</th>
                        <th class="px-5 py-3.5 font-semibold">เบอร์โทร</th>
                        <th class="px-5 py-3.5 font-semibold">ยอดเงิน</th>
                        <th class="px-5 py-3.5 font-semibold">VIP</th>
                        <th class="px-5 py-3.5 font-semibold">สถานะ</th>
                        <th class="px-5 py-3.5 font-semibold">สมัครเมื่อ</th>
                        <th class="px-5 py-3.5 font-semibold">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="m in members" :key="m.id">
                        <tr class="border-b border-indigo-50 table-row-hover transition-all duration-200 cursor-default">
                            <td class="px-5 py-3.5">
                                <span class="text-xs font-mono text-gray-400">#<span x-text="m.id"></span></span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold text-indigo-600 flex-shrink-0" style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe);" x-text="(m.name || 'U').substring(0,2).toUpperCase()"></div>
                                    <div>
                                        <div class="font-semibold text-gray-800" x-text="m.name"></div>
                                        <div class="text-[11px] text-gray-400" x-text="m.email || '-'"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600" x-text="m.phone || '-'"></td>
                            <td class="px-5 py-3.5">
                                <span class="font-bold text-gray-800">฿<span x-text="fmtMoney(m.balance)"></span></span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] font-semibold rounded-full uppercase tracking-wide" style="background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: #7c3aed;">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    Lv.<span x-text="m.vip_level || 0"></span>
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-[10px] font-semibold rounded-full uppercase tracking-wide"
                                    :class="m.status==='active' ? 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200' : m.status==='suspended' ? 'bg-amber-50 text-amber-600 ring-1 ring-amber-200' : 'bg-red-50 text-red-600 ring-1 ring-red-200'">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                          :class="m.status==='active' ? 'bg-emerald-500' : m.status==='suspended' ? 'bg-amber-500' : 'bg-red-500'"></span>
                                    <span x-text="m.status==='active' ? 'ใช้งาน' : m.status==='suspended' ? 'ระงับ' : 'แบน'"></span>
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-400 text-xs" x-text="m.created_at"></td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5">
                                    <a :href="'/admin/members/' + m.id"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded-lg transition-all duration-200"
                                       style="background: linear-gradient(135deg, #eef2ff, #e0e7ff); color: #4f46e5;"
                                       onmouseover="this.style.background='linear-gradient(135deg, #e0e7ff, #c7d2fe)'"
                                       onmouseout="this.style.background='linear-gradient(135deg, #eef2ff, #e0e7ff)'">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        ดู
                                    </a>
                                    <button @click="openCreditModal(m)"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-100 transition-all duration-200 ring-1 ring-emerald-100">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        เครดิต
                                    </button>
                                    <button @click="openStatusModal(m)"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100 transition-all duration-200 ring-1 ring-gray-100">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        สถานะ
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="members.length === 0 && !loading">
                        <tr>
                            <td colspan="8" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center" style="background: linear-gradient(135deg, #eef2ff, #e0e7ff);">
                                        <svg class="w-8 h-8 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                    <span class="text-sm text-gray-400">ไม่พบสมาชิกที่ตรงกับการค้นหา</span>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- Premium Pagination --}}
        <div class="px-5 py-4 border-t border-indigo-100 flex flex-wrap items-center justify-between gap-3" style="background: linear-gradient(135deg, rgba(238,242,255,0.4), rgba(224,231,255,0.3));">
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <span>แสดงหน้า</span>
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 font-bold text-xs" x-text="page"></span>
                <span>จาก</span>
                <span class="font-medium text-gray-700" x-text="lastPage"></span>
            </div>
            <div class="flex items-center gap-2">
                <button @click="page > 1 && (page--, load())" :disabled="page <= 1"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium rounded-xl border border-indigo-100 bg-white text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    ก่อนหน้า
                </button>
                <button @click="page < lastPage && (page++, load())" :disabled="page >= lastPage"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-medium rounded-xl border border-indigo-100 bg-white text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 disabled:opacity-40 disabled:cursor-not-allowed transition-all duration-200">
                    ถัดไป
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Credit Modal --}}
    <div x-show="showCreditModal" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @click.self="showCreditModal=false">
        <div x-show="showCreditModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden border border-indigo-100">
            {{-- Modal Header --}}
            <div class="p-5 border-b border-indigo-100" style="background: linear-gradient(135deg, rgba(238,242,255,0.8), rgba(224,231,255,0.5));">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #d1fae5, #a7f3d0);">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">ปรับเครดิต</h3>
                        <p class="text-xs text-gray-500" x-text="selectedMember?.name"></p>
                    </div>
                    <button @click="showCreditModal=false" class="ml-auto w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            {{-- Modal Body --}}
            <div class="p-5 space-y-4">
                <div class="p-4 rounded-xl" style="background: linear-gradient(135deg, #eef2ff, #e0e7ff);">
                    <div class="text-xs text-indigo-400 font-medium mb-1">ยอดเงินปัจจุบัน</div>
                    <div class="text-2xl font-bold gradient-text">฿<span x-text="fmtMoney(selectedMember?.balance)"></span></div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5 block">ประเภท</label>
                    <select x-model="creditForm.type" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 outline-none transition-all">
                        <option value="add">เพิ่มเครดิต</option>
                        <option value="deduct">หักเครดิต</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5 block">จำนวน (บาท)</label>
                    <input type="number" x-model="creditForm.amount" min="1" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 outline-none transition-all" placeholder="0.00">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5 block">หมายเหตุ</label>
                    <input type="text" x-model="creditForm.reason" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-300 outline-none transition-all" placeholder="เหตุผลการปรับเครดิต...">
                </div>
            </div>
            {{-- Modal Footer --}}
            <div class="p-5 border-t border-indigo-100 flex gap-3" style="background: linear-gradient(135deg, rgba(238,242,255,0.4), rgba(224,231,255,0.3));">
                <button @click="showCreditModal=false" class="flex-1 px-4 py-2.5 border border-indigo-100 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition-all">ยกเลิก</button>
                <button @click="submitCredit()" class="flex-1 btn-premium text-white rounded-xl text-sm font-medium py-2.5">ยืนยัน</button>
            </div>
        </div>
    </div>

    {{-- Status Modal --}}
    <div x-show="showStatusModal" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         @click.self="showStatusModal=false">
        <div x-show="showStatusModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden border border-indigo-100">
            {{-- Modal Header --}}
            <div class="p-5 border-b border-indigo-100" style="background: linear-gradient(135deg, rgba(238,242,255,0.8), rgba(224,231,255,0.5));">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe);">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">เปลี่ยนสถานะ</h3>
                        <p class="text-xs text-gray-500" x-text="selectedMember?.name"></p>
                    </div>
                    <button @click="showStatusModal=false" class="ml-auto w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            {{-- Status Options --}}
            <div class="p-5 space-y-2">
                <button @click="updateStatus('active')" class="w-full text-left px-4 py-3.5 rounded-xl hover:bg-emerald-50 flex items-center gap-3 transition-all group border border-transparent hover:border-emerald-200">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #d1fae5, #a7f3d0);">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-gray-700 group-hover:text-emerald-700">ใช้งาน (Active)</div>
                        <div class="text-[10px] text-gray-400">อนุญาตให้ใช้งานระบบได้ตามปกติ</div>
                    </div>
                </button>
                <button @click="updateStatus('suspended')" class="w-full text-left px-4 py-3.5 rounded-xl hover:bg-amber-50 flex items-center gap-3 transition-all group border border-transparent hover:border-amber-200">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #fef3c7, #fde68a);">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-gray-700 group-hover:text-amber-700">ระงับชั่วคราว (Suspended)</div>
                        <div class="text-[10px] text-gray-400">ระงับการใช้งานชั่วคราว</div>
                    </div>
                </button>
                <button @click="updateStatus('banned')" class="w-full text-left px-4 py-3.5 rounded-xl hover:bg-red-50 flex items-center gap-3 transition-all group border border-transparent hover:border-red-200">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #fee2e2, #fecaca);">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-gray-700 group-hover:text-red-700">แบนถาวร (Banned)</div>
                        <div class="text-[10px] text-gray-400">ปิดกั้นการเข้าใช้งานถาวร</div>
                    </div>
                </button>
            </div>
            <div class="px-5 pb-5">
                <button @click="showStatusModal=false" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition-all">ยกเลิก</button>
            </div>
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
            if (!this.creditForm.reason) return alert('กรุณากรอกหมายเหตุ');
            const amount = this.creditForm.type === 'deduct' ? -Math.abs(this.creditForm.amount) : Math.abs(this.creditForm.amount);
            const res = await fetchApi('/admin/members/' + this.selectedMember.id + '/credit', {
                method: 'POST', body: JSON.stringify({ amount, reason: this.creditForm.reason })
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
