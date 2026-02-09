@php
    $currentRoute = request()->route()?->getName() ?? '';
    $navItems = [
        ['label' => 'แดชบอร์ด', 'route' => 'admin.dashboard', 'icon' => 'dashboard', 'match' => 'admin.dashboard'],
        ['label' => 'จัดการสมาชิก', 'route' => 'admin.members.index', 'icon' => 'users', 'match' => 'admin.members'],
        ['label' => 'หวย/รอบ/อัตราจ่าย', 'route' => 'admin.lottery.index', 'icon' => 'lottery', 'match' => 'admin.lottery'],
        ['label' => 'ผลหวย/Scraper', 'route' => 'admin.result-sources.index', 'icon' => 'scraper', 'match' => 'admin.result-sources'],
        ['label' => 'การเงิน', 'route' => 'admin.finance.deposits', 'icon' => 'finance', 'match' => 'admin.finance'],
        ['label' => 'Risk Control', 'route' => 'admin.risk.dashboard', 'icon' => 'shield', 'match' => 'admin.risk'],
        ['label' => 'SMS ฝากเงิน', 'route' => 'admin.sms-deposit.dashboard', 'icon' => 'phone', 'match' => 'admin.sms-deposit'],
        ['label' => 'ตั้งค่าระบบ', 'route' => 'admin.settings.index', 'icon' => 'settings', 'match' => 'admin.settings'],
        ['label' => 'บันทึกระบบ', 'route' => 'admin.logs', 'icon' => 'logs', 'match' => 'admin.logs'],
    ];
@endphp

{{-- Desktop Sidebar --}}
<aside class="hidden lg:flex lg:flex-col fixed inset-y-0 left-0 z-30 bg-sidebar-bg transition-all duration-300 sidebar-scrollbar overflow-y-auto"
       :class="sidebarOpen ? 'w-64' : 'w-20'">

    {{-- Logo --}}
    <div class="h-16 flex items-center px-4 border-b border-sidebar-border/30 flex-shrink-0">
        <template x-if="sidebarOpen">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                    L
                </div>
                <div>
                    <span class="text-white font-bold text-lg tracking-tight block leading-tight">{{ \App\Models\Setting::getValue('site_name', 'Lotto111') }}</span>
                    <span class="text-indigo-400 text-[10px] font-medium uppercase tracking-widest">Admin Panel</span>
                </div>
            </a>
        </template>
        <template x-if="!sidebarOpen">
            <a href="{{ route('admin.dashboard') }}" class="mx-auto">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                    L
                </div>
            </a>
        </template>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 py-5 px-3 space-y-1">
        <template x-if="sidebarOpen">
            <div class="px-3 mb-3">
                <span class="text-[10px] font-semibold uppercase tracking-widest text-indigo-400/60">เมนูหลัก</span>
            </div>
        </template>
        @foreach($navItems as $item)
            @php $isActive = str_starts_with($currentRoute, $item['match']); @endphp
            <a href="{{ route($item['route']) }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 relative group
                      {{ $isActive
                          ? 'text-white shadow-md'
                          : 'text-sidebar-text hover:text-sidebar-textActive hover:bg-sidebar-hover/50' }}"
               :class="sidebarOpen ? '' : 'justify-center'"
               @if($isActive) style="background: linear-gradient(135deg, rgba(99,102,241,0.8), rgba(168,85,247,0.6));" @endif
               title="{{ $item['label'] }}">
                @if($isActive)
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-white rounded-r-full" x-show="sidebarOpen"></div>
                @endif
                <div class="flex-shrink-0 {{ $isActive ? 'text-white' : '' }}">
                    @include('admin.partials.icons.' . $item['icon'])
                </div>
                <span x-show="sidebarOpen" x-cloak class="truncate">{{ $item['label'] }}</span>
                @if($isActive)
                <div class="absolute right-2 w-1.5 h-1.5 bg-white rounded-full badge-pulse" x-show="sidebarOpen"></div>
                @endif
            </a>
        @endforeach
    </nav>

    {{-- Collapse Toggle --}}
    <div class="p-3 border-t border-sidebar-border/30 flex-shrink-0">
        <button @click="sidebarOpen = !sidebarOpen"
                class="w-full flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl text-sidebar-text hover:bg-sidebar-hover/50 hover:text-sidebar-textActive transition-all duration-200 text-sm">
            <svg class="w-5 h-5 transition-transform duration-300" :class="sidebarOpen ? '' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
            <span x-show="sidebarOpen" x-cloak>ย่อเมนู</span>
        </button>
    </div>
</aside>

{{-- Mobile Sidebar --}}
<aside x-show="mobileSidebarOpen" x-cloak
       x-transition:enter="transition ease-in-out duration-200 transform"
       x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
       x-transition:leave="transition ease-in-out duration-200 transform"
       x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
       class="fixed inset-y-0 left-0 z-50 w-72 bg-sidebar-bg lg:hidden sidebar-scrollbar overflow-y-auto">

    <div class="h-16 flex items-center justify-between px-4 border-b border-sidebar-border/30">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-bold shadow-lg" style="background: linear-gradient(135deg, #6366f1, #a855f7);">L</div>
            <div>
                <span class="text-white font-bold text-lg block leading-tight">{{ \App\Models\Setting::getValue('site_name', 'Lotto111') }}</span>
                <span class="text-indigo-400 text-[10px] font-medium uppercase tracking-widest">Admin Panel</span>
            </div>
        </a>
        <button @click="mobileSidebarOpen = false" class="text-indigo-400 hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <nav class="py-5 px-3 space-y-1">
        <div class="px-3 mb-3">
            <span class="text-[10px] font-semibold uppercase tracking-widest text-indigo-400/60">เมนูหลัก</span>
        </div>
        @foreach($navItems as $item)
            @php $isActive = str_starts_with($currentRoute, $item['match']); @endphp
            <a href="{{ route($item['route']) }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 relative
                      {{ $isActive
                          ? 'text-white shadow-md'
                          : 'text-sidebar-text hover:bg-sidebar-hover/50 hover:text-sidebar-textActive' }}"
               @if($isActive) style="background: linear-gradient(135deg, rgba(99,102,241,0.8), rgba(168,85,247,0.6));" @endif>
                @if($isActive)
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-white rounded-r-full"></div>
                @endif
                @include('admin.partials.icons.' . $item['icon'])
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
</aside>
