<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'หน้าหลัก') - {{ config('app.name', 'Lotto111') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Noto Sans Thai"', 'system-ui', 'sans-serif'] },
                    colors: {
                        brand: { 50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#312e81',950:'#1e1b4b' },
                        gold: { 400:'#fbbf24',500:'#f59e0b',600:'#d97706' }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>
        [x-cloak]{display:none!important}
        @keyframes fadeUp{0%{opacity:0;transform:translateY(20px)}100%{opacity:1;transform:translateY(0)}}
        @keyframes blob{0%,100%{transform:translate(0,0) scale(1)}33%{transform:translate(30px,-50px) scale(1.1)}66%{transform:translate(-20px,20px) scale(0.9)}}
        .animate-fade-up{animation:fadeUp 0.5s ease-out forwards;opacity:0}
        .animate-blob{animation:blob 7s infinite}
        .delay-100{animation-delay:100ms}.delay-200{animation-delay:200ms}.delay-300{animation-delay:300ms}
        .delay-400{animation-delay:400ms}.delay-500{animation-delay:500ms}
        .gradient-text{background:linear-gradient(135deg,#fbbf24,#f59e0b,#d97706);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .card-dark{background:rgba(255,255,255,0.04);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,0.06);border-radius:1rem;transition:all 0.3s}
        .card-dark:hover{border-color:rgba(255,255,255,0.12);background:rgba(255,255,255,0.06)}
        .btn-gold{background:linear-gradient(135deg,#fbbf24,#f59e0b,#d97706);color:#1e1b4b;font-weight:700;transition:all 0.3s}
        .btn-gold:hover{filter:brightness(1.1);transform:translateY(-1px)}
        .nav-link{color:rgba(255,255,255,0.4);transition:all 0.2s;position:relative}
        .nav-link:hover,.nav-link.active{color:#fbbf24}
        .nav-link.active::after{content:'';position:absolute;bottom:-2px;left:50%;transform:translateX(-50%);width:20px;height:2px;background:#fbbf24;border-radius:2px}
        .scrollbar-hide{-ms-overflow-style:none;scrollbar-width:none}.scrollbar-hide::-webkit-scrollbar{display:none}
    </style>
    @stack('styles')
</head>
<body class="font-sans antialiased text-white min-h-screen" style="background:linear-gradient(180deg,#0f0a2e 0%,#1e1b4b 50%,#0f0a2e 100%)">

    {{-- Fixed Background Blobs --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-600/15 rounded-full filter blur-[100px] animate-blob"></div>
        <div class="absolute bottom-20 -left-40 w-80 h-80 bg-indigo-600/15 rounded-full filter blur-[100px] animate-blob" style="animation-delay:3s"></div>
    </div>

    {{-- Top Navbar --}}
    <nav class="sticky top-0 z-50 border-b border-white/5" style="background:rgba(15,10,46,0.85);backdrop-filter:blur(20px)">
        <div class="max-w-5xl mx-auto px-4">
            <div class="flex items-center justify-between h-14">
                {{-- Logo --}}
                <a href="/" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-black" style="background:linear-gradient(135deg,#fbbf24,#f59e0b);">L</div>
                    <span class="text-sm font-bold text-white hidden sm:inline">{{ config('app.name', 'Lotto111') }}</span>
                </a>

                {{-- Desktop Nav --}}
                <div class="hidden md:flex items-center gap-5">
                    <a href="{{ route('member.dashboard') }}" class="nav-link text-sm font-medium {{ request()->routeIs('member.dashboard') ? 'active' : '' }}">หน้าหลัก</a>
                    <a href="{{ route('member.lottery') }}" class="nav-link text-sm font-medium {{ request()->routeIs('member.lottery*') ? 'active' : '' }}">แทงหวย</a>
                    <a href="{{ route('member.tickets') }}" class="nav-link text-sm font-medium {{ request()->routeIs('member.tickets') ? 'active' : '' }}">โพยของฉัน</a>
                    <a href="{{ route('member.results') }}" class="nav-link text-sm font-medium {{ request()->routeIs('member.results') ? 'active' : '' }}">ผลหวย</a>
                    <a href="{{ route('member.deposit') }}" class="nav-link text-sm font-medium {{ request()->routeIs('member.deposit') ? 'active' : '' }}">เติมเงิน</a>
                    <a href="{{ route('member.withdrawal') }}" class="nav-link text-sm font-medium {{ request()->routeIs('member.withdrawal') ? 'active' : '' }}">ถอนเงิน</a>
                    <a href="{{ route('member.referral') }}" class="nav-link text-sm font-medium {{ request()->routeIs('member.referral') ? 'active' : '' }}">แนะนำ</a>
                </div>

                {{-- Balance + User --}}
                <div class="flex items-center gap-3">
                    <div class="px-3 py-1.5 rounded-lg text-xs font-bold" style="background:rgba(251,191,36,0.1);border:1px solid rgba(251,191,36,0.2);">
                        <span class="text-gold-400">฿</span>
                        <span class="text-white">{{ number_format(auth()->user()->balance, 2) }}</span>
                    </div>
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open=!open" class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold" style="background:linear-gradient(135deg,#4f46e5,#7c3aed);">
                            {{ mb_substr(auth()->user()->name, 0, 1) }}
                        </button>
                        <div x-show="open" @click.away="open=false" x-cloak x-transition
                             class="absolute right-0 mt-2 w-48 rounded-xl py-2 border border-white/10 shadow-2xl"
                             style="background:rgba(30,27,75,0.95);backdrop-filter:blur(20px)">
                            <div class="px-4 py-2 border-b border-white/5">
                                <div class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</div>
                                <div class="text-xs text-white/30">{{ auth()->user()->phone }}</div>
                            </div>
                            <a href="{{ route('member.profile') }}" class="block px-4 py-2 text-sm text-white/50 hover:text-white hover:bg-white/5 transition-colors">โปรไฟล์</a>
                            <a href="{{ route('member.transactions') }}" class="block px-4 py-2 text-sm text-white/50 hover:text-white hover:bg-white/5 transition-colors">ประวัติรายการ</a>
                            <a href="{{ route('member.tickets') }}" class="block px-4 py-2 text-sm text-white/50 hover:text-white hover:bg-white/5 transition-colors">โพยของฉัน</a>
                            <form method="POST" action="/logout">@csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-400 hover:text-red-300 hover:bg-white/5 transition-colors">ออกจากระบบ</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="relative z-10 max-w-5xl mx-auto px-4 py-6">
        @yield('content')
    </main>

    {{-- Mobile Bottom Nav --}}
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-50 border-t border-white/5 safe-area-bottom" style="background:rgba(15,10,46,0.95);backdrop-filter:blur(20px)">
        <div class="flex items-center justify-around py-2">
            <a href="{{ route('member.dashboard') }}" class="flex flex-col items-center gap-0.5 py-1 px-2 {{ request()->routeIs('member.dashboard') ? 'text-gold-400' : 'text-white/30' }} hover:text-gold-400 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span class="text-[10px]">หน้าหลัก</span>
            </a>
            <a href="{{ route('member.lottery') }}" class="flex flex-col items-center gap-0.5 py-1 px-2 {{ request()->routeIs('member.lottery*') ? 'text-gold-400' : 'text-white/30' }} hover:text-gold-400 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span class="text-[10px]">แทงหวย</span>
            </a>
            <a href="{{ route('member.results') }}" class="flex flex-col items-center gap-0.5 py-1 px-2 {{ request()->routeIs('member.results') ? 'text-gold-400' : 'text-white/30' }} hover:text-gold-400 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-[10px]">ผลหวย</span>
            </a>
            <a href="{{ route('member.notifications') }}" class="flex flex-col items-center gap-0.5 py-1 px-2 relative {{ request()->routeIs('member.notifications') ? 'text-gold-400' : 'text-white/30' }} hover:text-gold-400 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="absolute -top-0.5 right-0.5 w-2 h-2 bg-red-500 rounded-full"></span>
                <span class="text-[10px]">แจ้งเตือน</span>
            </a>
            <a href="{{ route('member.profile') }}" class="flex flex-col items-center gap-0.5 py-1 px-2 {{ request()->routeIs('member.profile') ? 'text-gold-400' : 'text-white/30' }} hover:text-gold-400 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span class="text-[10px]">โปรไฟล์</span>
            </a>
        </div>
    </nav>

    {{-- Bottom padding for mobile nav --}}
    <div class="md:hidden h-16"></div>

    <script>
        window.fetchApi = (url, options = {}) => {
            const defaults = {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            };
            return fetch(url, { ...defaults, ...options, headers: { ...defaults.headers, ...(options.headers || {}) } })
                .then(r => r.json());
        };
        window.fmtMoney = n => new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2 }).format(n || 0);
    </script>
    @stack('scripts')
</body>
</html>
