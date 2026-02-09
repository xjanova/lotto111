<header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 md:px-6 flex-shrink-0">
    <div class="flex items-center gap-3">
        {{-- Mobile Menu Button --}}
        <button @click="mobileSidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <h1 class="text-lg font-semibold text-gray-800">@yield('page-title', 'แดชบอร์ด')</h1>
    </div>

    <div class="flex items-center gap-4">
        {{-- Quick Actions --}}
        <a href="{{ url('/') }}" target="_blank"
           class="hidden md:flex items-center gap-1.5 text-sm text-gray-500 hover:text-brand-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            หน้าเว็บ
        </a>

        {{-- Notifications --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="relative text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full text-[10px] text-white flex items-center justify-center" id="notif-badge" style="display:none">0</span>
            </button>
            <div x-show="open" x-cloak @click.away="open = false"
                 class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                <div class="px-4 py-2 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800 text-sm">การแจ้งเตือน</h3>
                </div>
                <div class="max-h-64 overflow-y-auto">
                    <div class="px-4 py-6 text-center text-gray-400 text-sm">ไม่มีการแจ้งเตือนใหม่</div>
                </div>
            </div>
        </div>

        {{-- User Menu --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-brand-600 flex items-center justify-center text-white text-sm font-medium">
                    {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                </div>
                <span class="hidden md:block text-sm font-medium text-gray-700">{{ auth()->user()->name ?? 'Admin' }}</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-cloak @click.away="open = false"
                 class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                <a href="{{ route('admin.settings.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">ตั้งค่าระบบ</a>
                <a href="{{ route('admin.logs') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">บันทึกระบบ</a>
                <div class="border-t border-gray-100 my-1"></div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">ออกจากระบบ</button>
                </form>
            </div>
        </div>
    </div>
</header>
