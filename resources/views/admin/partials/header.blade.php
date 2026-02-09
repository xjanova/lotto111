<header class="h-16 bg-white/80 backdrop-blur-md border-b border-indigo-100/50 flex items-center justify-between px-4 md:px-6 lg:px-8 flex-shrink-0 sticky top-0 z-20">
    <div class="flex items-center gap-3">
        {{-- Mobile Menu Button --}}
        <button @click="mobileSidebarOpen = true" class="lg:hidden text-gray-500 hover:text-brand-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <div>
            <h1 class="text-lg font-bold text-gray-800">@yield('page-title', 'แดชบอร์ด')</h1>
            @hasSection('page-subtitle')
            <p class="text-xs text-gray-400 -mt-0.5">@yield('page-subtitle')</p>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-2 md:gap-4">
        {{-- Quick link to site --}}
        <a href="{{ url('/') }}" target="_blank"
           class="hidden md:flex items-center gap-1.5 text-sm text-gray-400 hover:text-brand-600 transition-colors px-3 py-1.5 rounded-lg hover:bg-brand-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            หน้าเว็บ
        </a>

        {{-- Notifications --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="relative text-gray-400 hover:text-brand-600 transition-colors p-2 rounded-lg hover:bg-brand-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="absolute top-1 right-1 w-2 h-2 bg-rose-500 rounded-full badge-pulse" id="notif-dot" style="display:none"></span>
            </button>
            <div x-show="open" x-cloak @click.away="open = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-indigo-100/50 py-2 z-50">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800 text-sm">การแจ้งเตือน</h3>
                </div>
                <div class="max-h-64 overflow-y-auto">
                    <div class="px-4 py-8 text-center">
                        <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        </div>
                        <p class="text-sm text-gray-400">ไม่มีการแจ้งเตือนใหม่</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- User Menu --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2 px-2 py-1.5 rounded-xl hover:bg-gray-50 transition-colors">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-sm font-bold shadow-md" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                    {{ mb_substr(auth()->user()->name ?? 'A', 0, 1) }}
                </div>
                <div class="hidden md:block text-left">
                    <span class="text-sm font-semibold text-gray-700 block leading-tight">{{ auth()->user()->name ?? 'Admin' }}</span>
                    <span class="text-[10px] text-gray-400">ผู้ดูแลระบบ</span>
                </div>
                <svg class="w-4 h-4 text-gray-400 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-cloak @click.away="open = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-indigo-100/50 py-2 z-50">
                <div class="px-4 py-2 border-b border-gray-100">
                    <p class="text-sm font-semibold text-gray-700">{{ auth()->user()->name ?? 'Admin' }}</p>
                    <p class="text-xs text-gray-400">{{ auth()->user()->phone ?? auth()->user()->email ?? '' }}</p>
                </div>
                <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-600 hover:bg-brand-50 hover:text-brand-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    ตั้งค่าระบบ
                </a>
                <a href="{{ route('admin.logs') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-600 hover:bg-brand-50 hover:text-brand-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    บันทึกระบบ
                </a>
                <div class="border-t border-gray-100 my-1"></div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 w-full text-left px-4 py-2.5 text-sm text-rose-500 hover:bg-rose-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        ออกจากระบบ
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
