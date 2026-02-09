@extends('admin.layouts.app')
@section('title', 'ผลหวย/Scraper')
@section('page-title', 'จัดการผลหวย & Scraper')
@section('breadcrumb') <span class="text-gray-700">ผลหวย/Scraper</span> @endsection

@section('content')
<div x-data="scraperPage()" x-init="init()" class="space-y-6">

    {{-- Hero Banner --}}
    <div class="relative overflow-hidden rounded-2xl p-6 md:p-8 animate-fade-up" style="background: linear-gradient(135deg, #4f46e5, #6366f1, #8b5cf6);">
        <div class="absolute top-0 right-0 w-72 h-72 bg-white/10 rounded-full filter blur-3xl animate-blob"></div>
        <div class="absolute bottom-0 left-0 w-72 h-72 bg-purple-300/10 rounded-full filter blur-3xl animate-blob delay-200"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-white mb-1">Result Scraper</h2>
                <p class="text-white/70 text-sm">จัดการแหล่งดึงผลหวยอัตโนมัติ และกรอกผลด้วยมือ</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="fetchAll()" :disabled="fetchingAll" class="px-4 py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white rounded-xl text-sm font-medium transition-all duration-200 flex items-center gap-2 disabled:opacity-50">
                    <svg class="w-4 h-4" :class="fetchingAll && 'animate-spin'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span x-text="fetchingAll ? 'กำลังดึง...' : 'ดึงผลทั้งหมด'"></span>
                </button>
                <button @click="healthCheck()" class="px-4 py-2.5 bg-white text-indigo-700 rounded-xl text-sm font-bold transition-all duration-200 hover:shadow-lg hover:shadow-white/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Health Check
                </button>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="stat-card card-premium p-4 animate-fade-up delay-100">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Sources ทั้งหมด</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe);">
                    <svg class="w-4.5 h-4.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-800">{{ $totalSources ?? 0 }}</div>
            <div class="text-[10px] text-gray-400 mt-1">แหล่งข้อมูลที่ตั้งค่าไว้</div>
        </div>

        <div class="stat-card card-premium p-4 animate-fade-up delay-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Auto Mode</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #d1fae5, #a7f3d0);">
                    <svg class="w-4.5 h-4.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-emerald-600">{{ $autoCount ?? 0 }}</div>
            <div class="text-[10px] text-gray-400 mt-1">ดึงผลอัตโนมัติ</div>
        </div>

        <div class="stat-card card-premium p-4 animate-fade-up delay-300">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Manual Mode</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ffedd5, #fed7aa);">
                    <svg class="w-4.5 h-4.5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-orange-600">{{ $manualCount ?? 0 }}</div>
            <div class="text-[10px] text-gray-400 mt-1">กรอกผลด้วยมือ</div>
        </div>

        <div class="stat-card card-premium p-4 animate-fade-up delay-400">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Fetch 24 ชม.</span>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ede9fe, #ddd6fe);">
                    <svg class="w-4.5 h-4.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-purple-600">{{ $fetchCount24h ?? 0 }}</div>
            <div class="text-[10px] text-gray-400 mt-1">รายการที่ดึงแล้ว</div>
        </div>
    </div>

    {{-- Sources Table --}}
    <div class="card-premium animate-fade-up delay-300">
        <div class="p-5 border-b border-indigo-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-800">แหล่งดึงผลหวย</h3>
                <p class="text-xs text-gray-400 mt-0.5">จัดการ Sources สำหรับดึงผลรางวัลอัตโนมัติ</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-400 border-b border-indigo-50 uppercase tracking-wide">
                        <th class="px-5 py-3.5 font-semibold">ชื่อ</th>
                        <th class="px-5 py-3.5 font-semibold">Provider</th>
                        <th class="px-5 py-3.5 font-semibold">ประเภทหวย</th>
                        <th class="px-5 py-3.5 font-semibold">Mode</th>
                        <th class="px-5 py-3.5 font-semibold">สถานะล่าสุด</th>
                        <th class="px-5 py-3.5 font-semibold">Fetch ล่าสุด</th>
                        <th class="px-5 py-3.5 font-semibold">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sources ?? [] as $src)
                    <tr class="border-b border-gray-50 table-row-hover transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe);">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-800">{{ $src['name'] }}</div>
                                    <div class="text-[10px] text-gray-400 truncate max-w-[180px]">{{ $src['source_url'] ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="px-2 py-0.5 text-[10px] rounded-full bg-indigo-50 text-indigo-600 font-mono font-medium">{{ $src['provider'] }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-gray-600 text-xs">{{ $src['lottery_type_name'] ?? '-' }}</td>
                        <td class="px-5 py-3.5">
                            <button @click="switchMode({{ $src['id'] }}, '{{ $src['mode'] === 'auto' ? 'manual' : 'auto' }}')"
                                    class="px-2.5 py-1 text-[10px] rounded-full font-semibold uppercase tracking-wide cursor-pointer transition-all duration-200
                                    {{ $src['mode'] === 'auto' ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' : 'bg-orange-50 text-orange-600 hover:bg-orange-100' }}">
                                <span class="flex items-center gap-1">
                                    @if($src['mode'] === 'auto')
                                    <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
                                    @else
                                    <span class="w-1.5 h-1.5 bg-orange-400 rounded-full"></span>
                                    @endif
                                    {{ $src['mode'] === 'auto' ? 'AUTO' : 'MANUAL' }}
                                </span>
                            </button>
                        </td>
                        <td class="px-5 py-3.5">
                            @php $lastStatus = $src['last_status'] ?? ''; @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-[10px] rounded-full font-semibold uppercase tracking-wide
                                {{ $lastStatus === 'success' ? 'bg-emerald-50 text-emerald-600' : ($lastStatus === 'failed' ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-400') }}">
                                @if($lastStatus === 'success')
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                @elseif($lastStatus === 'failed')
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                @else
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                                {{ $lastStatus ?: 'ยังไม่เคย' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-gray-400">{{ $src['last_fetched_at'] ?? 'Never' }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex gap-1.5">
                                <button @click="testScrape({{ $src['id'] }})" class="btn-premium text-white text-[11px] px-2.5 py-1.5 rounded-lg font-medium flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                    Test
                                </button>
                                <button @click="fetchNow({{ $src['id'] }})" class="px-2.5 py-1.5 text-[11px] rounded-lg font-medium bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition-colors flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Fetch
                                </button>
                                <a href="{{ route('admin.result-sources.show', $src['id']) }}" class="px-2.5 py-1.5 text-[11px] rounded-lg font-medium bg-gray-50 text-gray-500 hover:bg-gray-100 transition-colors flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    Logs
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-3" style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe);">
                                    <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                </div>
                                <span class="text-sm text-gray-400 font-medium">ไม่พบ Sources</span>
                                <p class="text-xs text-gray-300 mt-1">รัน php artisan db:seed เพื่อเพิ่มข้อมูลเริ่มต้น</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Manual Submit --}}
    <div class="card-premium p-6 animate-fade-up delay-400">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-800">กรอกผลด้วยมือ (Manual Submit)</h3>
                <p class="text-xs text-gray-400">กรอกผลรางวัลสำหรับรอบที่ปิดรับแล้ว</p>
            </div>
        </div>
        <form @submit.prevent="manualSubmit()" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
            <div>
                <label class="text-xs font-medium text-gray-500 mb-1.5 block uppercase tracking-wide">รอบหวย</label>
                <select x-model="manualForm.lottery_round_id" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none bg-white transition-all" required>
                    <option value="">เลือกรอบ</option>
                    @foreach($closedRounds ?? [] as $r)
                    <option value="{{ $r['id'] }}">{{ $r['type_name'] ?? '' }} - {{ $r['round_code'] ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 mb-1.5 block uppercase tracking-wide">3 ตัวบน</label>
                <input type="text" x-model="manualForm.results.three_top" maxlength="3" pattern="\d{3}" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm font-mono text-center tracking-widest focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="XXX">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 mb-1.5 block uppercase tracking-wide">2 ตัวบน</label>
                <input type="text" x-model="manualForm.results.two_top" maxlength="2" pattern="\d{2}" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm font-mono text-center tracking-widest focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="XX">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 mb-1.5 block uppercase tracking-wide">2 ตัวล่าง</label>
                <input type="text" x-model="manualForm.results.two_bottom" maxlength="2" pattern="\d{2}" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm font-mono text-center tracking-widest focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="XX">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 mb-1.5 block uppercase tracking-wide">3 ตัวล่าง</label>
                <input type="text" x-model="manualForm.results.three_bottom" maxlength="3" pattern="\d{3}" class="w-full px-4 py-2.5 border border-indigo-100 rounded-xl text-sm font-mono text-center tracking-widest focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all" placeholder="XXX">
            </div>
            <div>
                <button type="submit" class="w-full btn-premium text-white rounded-xl text-sm font-medium py-2.5 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    ส่งผล
                </button>
            </div>
        </form>
    </div>

    {{-- Test Result Modal --}}
    <div x-show="showTestResult" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="showTestResult=false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-fade-up" x-transition>
            {{-- Modal Header --}}
            <div class="p-5 border-b border-indigo-50" style="background: linear-gradient(135deg, rgba(79,70,229,0.05), rgba(124,58,237,0.05));">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">ผล Test Scrape</h3>
                        <p class="text-xs text-gray-400">ดูข้อมูลที่ดึงมาจาก Source</p>
                    </div>
                </div>
            </div>
            {{-- Modal Body --}}
            <div class="p-5">
                <div class="bg-gradient-to-br from-gray-50 to-indigo-50/30 rounded-xl p-4 text-sm font-mono whitespace-pre-wrap max-h-80 overflow-auto border border-indigo-50" x-text="JSON.stringify(testResult, null, 2)"></div>
                <button @click="showTestResult=false" class="w-full mt-4 px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">ปิด</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function scraperPage() {
    return {
        fetchingAll: false,
        showTestResult: false,
        testResult: {},
        manualForm: { lottery_round_id: '', results: { three_top: '', two_top: '', two_bottom: '', three_bottom: '' } },

        init() {},

        async fetchAll() {
            if (!confirm('ดึงผลจากทุกแหล่งทันที?')) return;
            this.fetchingAll = true;
            const res = await fetchApi('/admin/result-sources/fetch-all', { method: 'POST' });
            this.fetchingAll = false;
            window.showToast(res.message || 'เสร็จสิ้น', res.success ? 'success' : 'error');
            setTimeout(() => location.reload(), 1000);
        },

        async healthCheck() {
            const res = await fetchApi('/admin/result-sources/health');
            this.testResult = res.data;
            this.showTestResult = true;
        },

        async switchMode(id, newMode) {
            if (!confirm('เปลี่ยนเป็น ' + newMode + '?')) return;
            await fetchApi('/admin/result-sources/' + id + '/mode', { method: 'PUT', body: JSON.stringify({ mode: newMode }) });
            location.reload();
        },

        async testScrape(id) {
            const res = await fetchApi('/admin/result-sources/' + id + '/test', { method: 'POST' });
            this.testResult = res;
            this.showTestResult = true;
        },

        async fetchNow(id) {
            if (!confirm('ดึงผลจาก source นี้ทันที?')) return;
            const res = await fetchApi('/admin/result-sources/' + id + '/fetch', { method: 'POST' });
            window.showToast(res.message || (res.success ? 'สำเร็จ' : 'ล้มเหลว'), res.success ? 'success' : 'error');
            if (res.success) setTimeout(() => location.reload(), 1000);
        },

        async manualSubmit() {
            if (!this.manualForm.lottery_round_id) return alert('เลือกรอบก่อน');
            const res = await fetchApi('/admin/result-sources/manual-submit', { method: 'POST', body: JSON.stringify(this.manualForm) });
            window.showToast(res.message || 'เสร็จสิ้น', res.success ? 'success' : 'error');
            if (res.success) setTimeout(() => location.reload(), 1000);
        },
    }
}
</script>
@endpush
