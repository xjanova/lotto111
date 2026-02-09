@extends('admin.layouts.app')
@section('title', 'ผลหวย/Scraper')
@section('page-title', 'จัดการผลหวย & Scraper')
@section('breadcrumb') <span class="text-gray-700">ผลหวย/Scraper</span> @endsection

@section('content')
<div x-data="scraperPage()" x-init="init()" class="space-y-4 animate-fade-in">
    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <div class="text-xs text-gray-400 mb-1">Sources ทั้งหมด</div>
            <div class="text-2xl font-bold text-gray-800">{{ $totalSources ?? 0 }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <div class="text-xs text-green-500 mb-1">Auto Mode</div>
            <div class="text-2xl font-bold text-green-600">{{ $autoCount ?? 0 }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <div class="text-xs text-orange-500 mb-1">Manual Mode</div>
            <div class="text-2xl font-bold text-orange-600">{{ $manualCount ?? 0 }}</div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center">
            <div class="text-xs text-blue-500 mb-1">Fetch 24 ชม.</div>
            <div class="text-2xl font-bold text-blue-600">{{ $fetchCount24h ?? 0 }}</div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex flex-wrap gap-3">
        <button @click="fetchAll()" :disabled="fetchingAll" class="px-4 py-2 bg-brand-600 text-white text-sm rounded-lg hover:bg-brand-700 disabled:opacity-50 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" :class="fetchingAll && 'animate-spin'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span x-text="fetchingAll ? 'กำลังดึง...' : 'ดึงผลทั้งหมดทันที'"></span>
        </button>
        <button @click="healthCheck()" class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Health Check
        </button>
    </div>

    {{-- Sources Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-xs text-gray-400 bg-gray-50 border-b border-gray-100">
                    <th class="px-4 py-3 font-medium">ชื่อ</th>
                    <th class="px-4 py-3 font-medium">Provider</th>
                    <th class="px-4 py-3 font-medium">ประเภทหวย</th>
                    <th class="px-4 py-3 font-medium">Mode</th>
                    <th class="px-4 py-3 font-medium">สถานะล่าสุด</th>
                    <th class="px-4 py-3 font-medium">Fetch ล่าสุด</th>
                    <th class="px-4 py-3 font-medium">จัดการ</th>
                </tr></thead>
                <tbody>
                    @forelse($sources ?? [] as $src)
                    <tr class="border-b border-gray-50 hover:bg-gray-50" x-data="{ showDetail: false }">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $src['name'] }}</div>
                            <div class="text-xs text-gray-400 truncate max-w-[200px]">{{ $src['source_url'] ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $src['provider'] }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $src['lottery_type_name'] ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <button @click="switchMode({{ $src['id'] }}, '{{ $src['mode'] === 'auto' ? 'manual' : 'auto' }}')"
                                    class="px-2 py-1 text-xs rounded-full cursor-pointer transition-colors
                                    {{ $src['mode'] === 'auto' ? 'bg-green-50 text-green-600 hover:bg-green-100' : 'bg-orange-50 text-orange-600 hover:bg-orange-100' }}">
                                {{ $src['mode'] === 'auto' ? 'Auto' : 'Manual' }}
                            </button>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 text-xs rounded-full
                                {{ ($src['last_status'] ?? '') === 'success' ? 'bg-green-50 text-green-600' : (($src['last_status'] ?? '') === 'failed' ? 'bg-red-50 text-red-600' : 'bg-gray-100 text-gray-400') }}">
                                {{ $src['last_status'] ?? 'ยังไม่เคย' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-400">{{ $src['last_fetched_at'] ?? 'Never' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                <button @click="testScrape({{ $src['id'] }})" class="px-2 py-1 text-xs bg-blue-50 text-blue-600 rounded hover:bg-blue-100">Test</button>
                                <button @click="fetchNow({{ $src['id'] }})" class="px-2 py-1 text-xs bg-green-50 text-green-600 rounded hover:bg-green-100">Fetch</button>
                                <a href="{{ route('admin.result-sources.show', $src['id']) }}" class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded hover:bg-gray-200">Logs</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">ไม่พบ Sources - รัน php artisan db:seed</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Manual Submit --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-4">กรอกผลด้วยมือ (Manual Submit)</h3>
        <form @submit.prevent="manualSubmit()" class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
            <div>
                <label class="text-xs text-gray-500 mb-1 block">รอบหวย</label>
                <select x-model="manualForm.lottery_round_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm" required>
                    <option value="">เลือกรอบ</option>
                    @foreach($closedRounds ?? [] as $r)
                    <option value="{{ $r['id'] }}">{{ $r['type_name'] ?? '' }} - {{ $r['round_code'] ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500 mb-1 block">3 ตัวบน</label>
                <input type="text" x-model="manualForm.results.three_top" maxlength="3" pattern="\d{3}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono" placeholder="XXX">
            </div>
            <div>
                <label class="text-xs text-gray-500 mb-1 block">2 ตัวบน</label>
                <input type="text" x-model="manualForm.results.two_top" maxlength="2" pattern="\d{2}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono" placeholder="XX">
            </div>
            <div>
                <label class="text-xs text-gray-500 mb-1 block">2 ตัวล่าง</label>
                <input type="text" x-model="manualForm.results.two_bottom" maxlength="2" pattern="\d{2}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono" placeholder="XX">
            </div>
            <div>
                <label class="text-xs text-gray-500 mb-1 block">3 ตัวล่าง</label>
                <input type="text" x-model="manualForm.results.three_bottom" maxlength="3" pattern="\d{3}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono" placeholder="XXX">
            </div>
            <div>
                <button type="submit" class="w-full px-4 py-2 bg-brand-600 text-white rounded-lg text-sm hover:bg-brand-700">ส่งผล</button>
            </div>
        </form>
    </div>

    {{-- Test Result Modal --}}
    <div x-show="showTestResult" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showTestResult=false">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 animate-fade-in">
            <h3 class="text-lg font-semibold mb-4">ผล Test Scrape</h3>
            <div class="bg-gray-50 rounded-lg p-4 text-sm font-mono whitespace-pre-wrap max-h-80 overflow-auto" x-text="JSON.stringify(testResult, null, 2)"></div>
            <button @click="showTestResult=false" class="w-full mt-4 px-4 py-2 border border-gray-200 rounded-lg text-sm hover:bg-gray-50">ปิด</button>
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
            alert(res.message || 'เสร็จสิ้น');
            location.reload();
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
            alert(res.message || (res.success ? 'สำเร็จ' : 'ล้มเหลว'));
            location.reload();
        },

        async manualSubmit() {
            if (!this.manualForm.lottery_round_id) return alert('เลือกรอบก่อน');
            const res = await fetchApi('/admin/result-sources/manual-submit', { method: 'POST', body: JSON.stringify(this.manualForm) });
            alert(res.message || 'เสร็จสิ้น');
            if (res.success) location.reload();
        },
    }
}
</script>
@endpush
