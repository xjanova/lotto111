@extends('member.layouts.app')
@section('title', 'ผลหวย')

@section('content')
<div x-data="resultsPage()" class="space-y-6 pb-4">

    {{-- Page Header --}}
    <div class="animate-fade-up">
        <h1 class="text-xl font-bold text-white">ผลหวย</h1>
        <p class="text-xs text-white/30 mt-1">ตรวจผลรางวัลทุกประเภทหวย</p>
    </div>

    {{-- Search Box --}}
    <div class="card-dark p-4 animate-fade-up delay-100">
        <div class="flex gap-2">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="searchNumber"
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl text-sm text-white font-mono border border-white/10 focus:border-gold-400/50 focus:ring-2 focus:ring-gold-400/20 outline-none transition-all"
                       style="background:rgba(255,255,255,0.03)"
                       placeholder="ค้นหาเลข...">
            </div>
            <button class="btn-gold px-5 py-2.5 rounded-xl text-xs font-bold">ค้นหา</button>
        </div>
    </div>

    {{-- Type Filter --}}
    <div class="flex gap-2 overflow-x-auto scrollbar-hide animate-fade-up delay-200">
        <button @click="activeType = 'all'"
                :class="activeType === 'all' ? 'bg-gold-400/20 text-gold-400 border-gold-400/30' : 'text-white/30 border-white/5'"
                class="whitespace-nowrap px-4 py-2 rounded-xl text-xs font-semibold border transition-all">ทั้งหมด</button>
        @foreach($lotteryTypes as $type)
        <button @click="activeType = '{{ $type['id'] }}'"
                :class="activeType === '{{ $type['id'] }}' ? 'bg-gold-400/20 text-gold-400 border-gold-400/30' : 'text-white/30 border-white/5'"
                class="whitespace-nowrap px-4 py-2 rounded-xl text-xs font-semibold border transition-all">
            {{ $type['icon'] }} {{ $type['name'] }}
        </button>
        @endforeach
    </div>

    {{-- Results List --}}
    <div class="space-y-4 animate-fade-up delay-300">
        @foreach($results as $date => $dateResults)
        <div>
            <div class="flex items-center gap-2 mb-3">
                <div class="w-6 h-6 rounded-lg flex items-center justify-center" style="background:rgba(251,191,36,0.1)">
                    <svg class="w-3 h-3 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-white">{{ $date }}</h3>
            </div>

            <div class="space-y-2">
                @foreach($dateResults as $result)
                <div class="card-dark p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">{{ $result['icon'] }}</span>
                            <div>
                                <div class="text-sm font-bold text-white">{{ $result['type_name'] }}</div>
                                <div class="text-[10px] text-white/25">{{ $result['round_name'] }}</div>
                            </div>
                        </div>
                        <div class="text-[10px] text-white/20">{{ $result['time'] }}</div>
                    </div>

                    {{-- Result Numbers --}}
                    <div class="space-y-2">
                        @foreach($result['prizes'] as $prize)
                        <div class="flex items-center justify-between py-2 px-3 rounded-lg" style="background:rgba(255,255,255,0.02)">
                            <span class="text-xs text-white/40">{{ $prize['name'] }}</span>
                            <div class="flex items-center gap-1.5">
                                @foreach(str_split($prize['number']) as $digit)
                                <span class="w-7 h-7 rounded-lg flex items-center justify-center text-sm font-bold {{ $prize['highlight'] ? 'text-gold-400' : 'text-white/70' }}"
                                      style="background:{{ $prize['highlight'] ? 'rgba(251,191,36,0.12)' : 'rgba(255,255,255,0.04)' }}">{{ $digit }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
function resultsPage() {
    return {
        searchNumber: '',
        activeType: 'all',
    };
}
</script>
@endpush
