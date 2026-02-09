<!DOCTYPE html>
<html lang="th" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $siteName ?? config('app.name', 'Lotto111') }} - แทงหวยออนไลน์</title>
    <meta name="description" content="แทงหวยออนไลน์ จ่ายเต็ม ราคาดีที่สุด หวยรัฐบาล หวยลาว หวยฮานอย จับยี่กี ฝาก-ถอนออโต้">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Noto Sans Thai"', 'Inter', 'system-ui', 'sans-serif'] },
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
        @keyframes blob{0%,100%{transform:translate(0,0) scale(1)}33%{transform:translate(30px,-50px) scale(1.1)}66%{transform:translate(-20px,20px) scale(0.9)}}
        @keyframes fadeUp{0%{opacity:0;transform:translateY(30px)}100%{opacity:1;transform:translateY(0)}}
        @keyframes fadeIn{0%{opacity:0}100%{opacity:1}}
        @keyframes slideLeft{0%{opacity:0;transform:translateX(40px)}100%{opacity:1;transform:translateX(0)}}
        @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
        @keyframes marquee{0%{transform:translateX(100%)}100%{transform:translateX(-100%)}}
        @keyframes spin-slow{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}
        @keyframes pulse-ring{0%{transform:scale(0.8);opacity:1}100%{transform:scale(2.4);opacity:0}}
        .animate-blob{animation:blob 7s infinite}
        .animate-fade-up{animation:fadeUp 0.7s ease-out forwards;opacity:0}
        .animate-fade-in{animation:fadeIn 0.5s ease-out forwards}
        .animate-slide-left{animation:slideLeft 0.6s ease-out forwards;opacity:0}
        .animate-float{animation:float 3s ease-in-out infinite}
        .animate-marquee{animation:marquee 25s linear infinite}
        .animate-spin-slow{animation:spin-slow 20s linear infinite}
        .delay-100{animation-delay:100ms}.delay-200{animation-delay:200ms}.delay-300{animation-delay:300ms}
        .delay-400{animation-delay:400ms}.delay-500{animation-delay:500ms}.delay-600{animation-delay:600ms}
        .delay-700{animation-delay:700ms}.delay-800{animation-delay:800ms}
        .gradient-text{background:linear-gradient(135deg,#fbbf24,#f59e0b,#d97706);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .card-glass{background:rgba(255,255,255,0.05);backdrop-filter:blur(16px) saturate(1.5);border:1px solid rgba(255,255,255,0.08);transition:all 0.4s cubic-bezier(0.4,0,0.2,1)}
        .card-glass:hover{background:rgba(255,255,255,0.08);border-color:rgba(255,255,255,0.15);transform:translateY(-4px);box-shadow:0 20px 50px -12px rgba(0,0,0,0.4)}
        .btn-gold{background:linear-gradient(135deg,#fbbf24,#f59e0b,#d97706);color:#1e1b4b;font-weight:700;transition:all 0.3s ease}
        .btn-gold:hover{box-shadow:0 8px 30px -5px rgba(251,191,36,0.5);transform:translateY(-2px)}
        .btn-outline{border:2px solid rgba(251,191,36,0.4);color:#fbbf24;transition:all 0.3s ease}
        .btn-outline:hover{background:rgba(251,191,36,0.1);border-color:#fbbf24}
        .pulse-ring::after{content:'';position:absolute;inset:-4px;border-radius:50%;border:2px solid rgba(16,185,129,0.5);animation:pulse-ring 1.5s ease-out infinite}
    </style>
</head>
<body class="font-sans antialiased text-white" style="background:linear-gradient(180deg,#0f0a2e 0%,#1e1b4b 30%,#1a1145 60%,#0f0a2e 100%)">

    {{-- Navbar --}}
    <nav class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" x-data="{ scrolled: false, mobileMenu: false }"
         @scroll.window="scrolled = window.scrollY > 50"
         :class="scrolled ? 'bg-brand-950/90 backdrop-blur-xl shadow-lg shadow-black/20 border-b border-white/5' : ''">
        @if(!empty($marquee))
        <div class="bg-gold-500/10 border-b border-gold-500/20 py-2 overflow-hidden" x-show="!scrolled" x-transition>
            <div class="animate-marquee whitespace-nowrap text-sm text-gold-400 font-medium px-4">
                {{ $marquee }}
            </div>
        </div>
        @endif
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-20">
                <a href="/" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 lg:w-12 lg:h-12 rounded-xl lg:rounded-2xl flex items-center justify-center text-brand-950 font-black text-lg lg:text-xl shadow-lg" style="background:linear-gradient(135deg,#fbbf24,#f59e0b);">
                        L
                    </div>
                    <div class="hidden sm:block">
                        <span class="text-lg lg:text-xl font-black text-white block leading-tight">{{ $siteName }}</span>
                        <span class="text-[10px] text-gold-400/60 font-medium uppercase tracking-widest">แทงหวยออนไลน์</span>
                    </div>
                </a>

                <div class="hidden lg:flex items-center gap-8">
                    <a href="#lottery" class="text-sm text-white/60 hover:text-gold-400 transition-colors font-medium">หวยทั้งหมด</a>
                    <a href="#results" class="text-sm text-white/60 hover:text-gold-400 transition-colors font-medium">ผลหวย</a>
                    <a href="#features" class="text-sm text-white/60 hover:text-gold-400 transition-colors font-medium">บริการ</a>
                    <a href="#contact" class="text-sm text-white/60 hover:text-gold-400 transition-colors font-medium">ติดต่อ</a>
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        <span class="hidden sm:inline text-sm text-white/60">{{ auth()->user()->name }}</span>
                        <form method="POST" action="/logout" class="inline">@csrf
                            <button type="submit" class="hidden sm:inline-flex btn-outline text-xs px-4 py-2.5 rounded-xl font-semibold">ออกจากระบบ</button>
                        </form>
                    @else
                        <a href="/login" class="hidden sm:inline-flex btn-outline text-xs px-4 py-2.5 rounded-xl font-semibold">เข้าสู่ระบบ</a>
                        <a href="/register" class="btn-gold text-xs px-5 py-2.5 rounded-xl font-bold flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            สมัครสมาชิก
                        </a>
                    @endauth
                    <button @click="mobileMenu = !mobileMenu" class="lg:hidden text-white/60 hover:text-white p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <div x-show="mobileMenu" x-cloak x-transition class="lg:hidden bg-brand-950/95 backdrop-blur-xl border-t border-white/5 px-4 pb-4">
            <a href="#lottery" @click="mobileMenu=false" class="block py-3 text-white/70 hover:text-gold-400 text-sm font-medium border-b border-white/5">หวยทั้งหมด</a>
            <a href="#results" @click="mobileMenu=false" class="block py-3 text-white/70 hover:text-gold-400 text-sm font-medium border-b border-white/5">ผลหวย</a>
            <a href="#features" @click="mobileMenu=false" class="block py-3 text-white/70 hover:text-gold-400 text-sm font-medium border-b border-white/5">บริการ</a>
            <a href="#contact" @click="mobileMenu=false" class="block py-3 text-white/70 hover:text-gold-400 text-sm font-medium">ติดต่อ</a>
            @auth
                <form method="POST" action="/logout" class="block mt-3">@csrf
                    <button type="submit" class="w-full text-center btn-outline text-xs px-4 py-2.5 rounded-xl font-semibold">ออกจากระบบ</button>
                </form>
            @else
                <a href="/login" class="block mt-3 text-center btn-outline text-xs px-4 py-2.5 rounded-xl font-semibold">เข้าสู่ระบบ</a>
                <a href="/register" class="block mt-2 text-center btn-gold text-xs px-4 py-2.5 rounded-xl font-bold">สมัครสมาชิก</a>
            @endauth
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="relative min-h-screen flex items-center overflow-hidden pt-20">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-1/4 left-1/4 w-[500px] h-[500px] rounded-full filter blur-[120px] opacity-20 animate-blob" style="background:linear-gradient(135deg,#6366f1,#a855f7)"></div>
            <div class="absolute bottom-1/4 right-1/4 w-[400px] h-[400px] rounded-full filter blur-[100px] opacity-15 animate-blob" style="animation-delay:2s;background:linear-gradient(135deg,#fbbf24,#f97316)"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full filter blur-[150px] opacity-10 animate-blob" style="animation-delay:4s;background:linear-gradient(135deg,#ec4899,#7c3aed)"></div>
            <div class="absolute inset-0 opacity-[0.03]" style="background-image:linear-gradient(rgba(255,255,255,0.1) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.1) 1px,transparent 1px);background-size:60px 60px"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <div>
                    <div class="animate-fade-up">
                        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full mb-6 text-xs font-semibold" style="background:rgba(251,191,36,0.1);border:1px solid rgba(251,191,36,0.2);color:#fbbf24">
                            <span class="w-2 h-2 bg-emerald-400 rounded-full relative pulse-ring"></span>
                            เปิดให้บริการ 24 ชม.
                        </div>
                    </div>

                    <h1 class="animate-fade-up delay-100 text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-black leading-[1.1] mb-6">
                        <span class="text-white">แทงหวย</span><br>
                        <span class="gradient-text">ออนไลน์</span><br>
                        <span class="text-white/80 text-3xl sm:text-4xl lg:text-5xl font-bold">จ่ายเต็ม ราคาดี</span>
                    </h1>

                    <p class="animate-fade-up delay-200 text-base lg:text-lg text-white/50 mb-8 max-w-lg leading-relaxed">
                        หวยรัฐบาล หวยลาว หวยฮานอย จับยี่กี และอีกมากมาย ฝาก-ถอนออโต้ รวดเร็ว ปลอดภัย 100%
                    </p>

                    <div class="animate-fade-up delay-300 flex flex-col sm:flex-row gap-3">
                        <a href="/register" class="btn-gold text-sm px-8 py-4 rounded-2xl text-center font-bold flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            เริ่มเล่นเลย
                        </a>
                        <a href="#lottery" class="btn-outline text-sm px-8 py-4 rounded-2xl text-center font-semibold">
                            ดูหวยทั้งหมด
                        </a>
                    </div>

                    <div class="animate-fade-up delay-400 mt-10 flex items-center gap-6 flex-wrap">
                        @php
                            $badges = [
                                ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'text' => 'ปลอดภัย 100%'],
                                ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'text' => 'ฝาก-ถอนออโต้'],
                                ['icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'text' => '24 ชั่วโมง'],
                            ];
                        @endphp
                        @foreach($badges as $b)
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(251,191,36,0.1)">
                                <svg class="w-4 h-4 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $b['icon'] }}"/></svg>
                            </div>
                            <span class="text-xs text-white/40 font-medium">{{ $b['text'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="hidden lg:block relative">
                    <div class="relative w-full h-[500px]">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-80 h-80 rounded-full border border-dashed border-white/5 animate-spin-slow"></div>
                        </div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-56 h-56 rounded-full border border-dashed border-gold-400/10 animate-spin-slow" style="animation-direction:reverse;animation-duration:15s"></div>
                        </div>

                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 animate-float">
                            <div class="w-32 h-32 rounded-3xl shadow-2xl shadow-gold-500/20 flex flex-col items-center justify-center" style="background:linear-gradient(135deg,#fbbf24,#f59e0b)">
                                <span class="text-4xl font-black text-brand-950">111</span>
                                <span class="text-[10px] font-bold text-brand-950/60 uppercase tracking-widest">LOTTO</span>
                            </div>
                        </div>

                        <div class="absolute top-8 right-8 card-glass rounded-2xl p-4 animate-slide-left delay-300">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,rgba(16,185,129,0.2),rgba(16,185,129,0.1))">
                                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                </div>
                                <div>
                                    <div class="text-xs text-white/40">จ่ายสูงสุด</div>
                                    <div class="text-lg font-bold text-emerald-400">x900</div>
                                </div>
                            </div>
                        </div>

                        <div class="absolute bottom-16 left-4 card-glass rounded-2xl p-4 animate-slide-left delay-500">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,rgba(99,102,241,0.2),rgba(99,102,241,0.1))">
                                    <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <div class="text-xs text-white/40">สมาชิก</div>
                                    <div class="text-lg font-bold text-white">10,000+</div>
                                </div>
                            </div>
                        </div>

                        <div class="absolute top-1/3 left-0 card-glass rounded-2xl p-4 animate-slide-left delay-700">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,rgba(251,191,36,0.2),rgba(251,191,36,0.1))">
                                    <svg class="w-5 h-5 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                                <div>
                                    <div class="text-xs text-white/40">ฝาก-ถอน</div>
                                    <div class="text-lg font-bold text-gold-400">ออโต้</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-float">
            <div class="w-6 h-10 border-2 border-white/20 rounded-full flex justify-center pt-2">
                <div class="w-1 h-2 bg-white/40 rounded-full"></div>
            </div>
        </div>
    </section>

    {{-- Lottery Types --}}
    <section id="lottery" class="relative py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 lg:mb-16">
                <h2 class="animate-fade-up text-3xl lg:text-4xl font-black text-white mb-3">หวย<span class="gradient-text">ทั้งหมด</span></h2>
                <p class="animate-fade-up delay-100 text-white/40 text-sm lg:text-base max-w-lg mx-auto">เลือกหวยที่คุณชอบ เปิดให้แทงตลอด 24 ชั่วโมง ราคาจ่ายสูงสุด</p>
            </div>

            @if($lotteryTypes->count() > 0)
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 lg:gap-5">
                @php
                    $catColors = [
                        'government' => ['from'=>'#fbbf24','to'=>'#f59e0b','ic'=>'#92400e'],
                        'yeekee' => ['from'=>'#a855f7','to'=>'#7c3aed','ic'=>'#fff'],
                        'bank' => ['from'=>'#10b981','to'=>'#059669','ic'=>'#fff'],
                        'international' => ['from'=>'#3b82f6','to'=>'#2563eb','ic'=>'#fff'],
                        'set' => ['from'=>'#ec4899','to'=>'#db2777','ic'=>'#fff'],
                    ];
                    $catIcons = [
                        'government' => 'M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9',
                        'yeekee' => 'M13 10V3L4 14h7v7l9-11h-7z',
                        'bank' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                        'international' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                        'set' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
                    ];
                @endphp
                @foreach($lotteryTypes as $idx => $type)
                @php
                    $cat = $type->category?->value ?? 'government';
                    $c = $catColors[$cat] ?? $catColors['government'];
                    $ic = $catIcons[$cat] ?? $catIcons['government'];
                    $openCount = $type->activeRounds()->count();
                @endphp
                <div class="animate-fade-up" style="animation-delay:{{ $idx * 80 }}ms">
                    <div class="card-glass rounded-2xl p-5 text-center group cursor-pointer">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg transition-transform duration-300 group-hover:scale-110"
                             style="background:linear-gradient(135deg,{{ $c['from'] }},{{ $c['to'] }})">
                            <svg class="w-6 h-6" style="color:{{ $c['ic'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $ic }}"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-white mb-1">{{ $type->name }}</h3>
                        <p class="text-[10px] text-white/30 uppercase tracking-wider mb-3">{{ $type->category?->label() ?? '-' }}</p>
                        @if($openCount > 0)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full"></span>
                            {{ $openCount }} รอบเปิด
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-medium text-white/20">ยังไม่เปิด</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-16">
                <div class="w-20 h-20 rounded-3xl flex items-center justify-center mx-auto mb-4" style="background:rgba(251,191,36,0.08)">
                    <svg class="w-10 h-10 text-gold-400/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                </div>
                <p class="text-white/30 text-sm">กำลังเตรียมหวยให้คุณ...</p>
            </div>
            @endif
        </div>
    </section>

    {{-- Open Rounds --}}
    @if($openRounds->count() > 0)
    <section class="relative py-16 lg:py-20">
        <div class="absolute inset-0 pointer-events-none" style="background:linear-gradient(180deg,transparent,rgba(99,102,241,0.03),transparent)"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h2 class="text-2xl lg:text-3xl font-black text-white">รอบที่<span class="gradient-text">เปิดอยู่</span></h2>
                    <p class="text-white/40 text-sm mt-1">รีบแทงก่อนปิดรับ</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($openRounds as $round)
                <div class="card-glass rounded-2xl p-5 group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,rgba(251,191,36,0.15),rgba(251,191,36,0.05))">
                                <svg class="w-5 h-5 text-gold-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-white">{{ $round->lotteryType?->name ?? '-' }}</h3>
                                <span class="text-[10px] text-white/30 font-mono">{{ $round->round_code }}</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full"></span> เปิด
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="text-xs text-white/40">
                            ปิดรับ: <span class="text-white/70 font-medium">{{ $round->close_at?->format('d/m H:i') ?? '-' }}</span>
                        </div>
                        <a href="#" class="text-xs font-bold text-gold-400 hover:text-gold-300 transition-colors flex items-center gap-1">
                            แทงเลย <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Latest Results --}}
    <section id="results" class="relative py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-2xl lg:text-3xl font-black text-white mb-3">ผลหวย<span class="gradient-text">ล่าสุด</span></h2>
                <p class="text-white/40 text-sm">ตรวจผลหวยย้อนหลังได้ทุกงวด</p>
            </div>

            @if($latestResults->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($latestResults as $result)
                <div class="card-glass rounded-2xl p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,rgba(168,85,247,0.15),rgba(168,85,247,0.05))">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white">{{ $result->lotteryType?->name ?? '-' }}</h3>
                            <span class="text-[10px] text-white/30">{{ $result->result_at?->format('d/m/Y H:i') ?? '-' }}</span>
                        </div>
                    </div>
                    @if($result->results)
                    <div class="flex flex-wrap gap-2">
                        @foreach(array_slice((array)$result->results, 0, 5) as $key => $val)
                        <div class="px-3 py-1.5 rounded-lg text-xs font-bold" style="background:rgba(251,191,36,0.08);color:#fbbf24;border:1px solid rgba(251,191,36,0.15)">
                            {{ is_array($val) ? implode(' ', $val) : $val }}
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-xs text-white/20">กำลังประมวลผล...</p>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <p class="text-white/30 text-sm">ยังไม่มีผลหวย</p>
            </div>
            @endif
        </div>
    </section>

    {{-- Features --}}
    <section id="features" class="relative py-20 lg:py-28">
        <div class="absolute inset-0 pointer-events-none" style="background:linear-gradient(180deg,transparent,rgba(99,102,241,0.03),transparent)"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center mb-14">
                <h2 class="text-3xl lg:text-4xl font-black text-white mb-3">ทำไมต้อง<span class="gradient-text">เรา</span></h2>
                <p class="text-white/40 text-sm lg:text-base max-w-lg mx-auto">บริการที่ดีที่สุดสำหรับคุณ</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @php
                    $features = [
                        ['title'=>'ฝาก-ถอนออโต้','desc'=>'ฝากเงินและถอนเงินอัตโนมัติผ่านระบบ SMS ไม่เกิน 30 วินาที','icon'=>'M13 10V3L4 14h7v7l9-11h-7z','color'=>'#fbbf24'],
                        ['title'=>'จ่ายเต็ม ราคาดี','desc'=>'อัตราจ่ายสูงสุดในตลาด 3 ตัวบน จ่ายสูงสุด 900 บาท','icon'=>'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z','color'=>'#10b981'],
                        ['title'=>'หวยครบทุกชนิด','desc'=>'หวยรัฐบาล หวยลาว หวยฮานอย จับยี่กี หวยหุ้น และอีกมากมาย','icon'=>'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10','color'=>'#a855f7'],
                        ['title'=>'ปลอดภัย 100%','desc'=>'ระบบรักษาความปลอดภัยระดับสูง ข้อมูลเข้ารหัสทุกรายการ','icon'=>'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z','color'=>'#3b82f6'],
                        ['title'=>'แทงขั้นต่ำ 1 บาท','desc'=>'เริ่มต้นเพียง 1 บาท เล่นง่าย เข้าถึงได้ทุกคน','icon'=>'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z','color'=>'#ec4899'],
                        ['title'=>'บริการ 24 ชม.','desc'=>'ทีมงานพร้อมดูแลคุณตลอด 24 ชั่วโมง ทุกวัน ไม่มีวันหยุด','icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z','color'=>'#f97316'],
                    ];
                @endphp
                @foreach($features as $idx => $f)
                <div class="animate-fade-up" style="animation-delay:{{ $idx * 100 }}ms">
                    <div class="card-glass rounded-2xl p-6 h-full">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4" style="background:{{ $f['color'] }}15">
                            <svg class="w-6 h-6" style="color:{{ $f['color'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $f['icon'] }}"/></svg>
                        </div>
                        <h3 class="text-base font-bold text-white mb-2">{{ $f['title'] }}</h3>
                        <p class="text-sm text-white/40 leading-relaxed">{{ $f['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="relative py-20 lg:py-28">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="relative rounded-3xl p-10 lg:p-16 overflow-hidden" style="background:linear-gradient(135deg,#4f46e5,#7c3aed,#db2777)">
                <div class="absolute top-0 left-0 w-72 h-72 bg-white/10 rounded-full filter blur-3xl animate-blob"></div>
                <div class="absolute bottom-0 right-0 w-72 h-72 bg-pink-300/10 rounded-full filter blur-3xl animate-blob" style="animation-delay:2s"></div>
                <div class="relative z-10">
                    <h2 class="text-3xl lg:text-4xl font-black text-white mb-4">พร้อมลุ้นโชคแล้วหรือยัง?</h2>
                    <p class="text-white/70 text-sm lg:text-base max-w-lg mx-auto mb-8">สมัครสมาชิกวันนี้ รับโบนัสฟรีทันที เริ่มแทงหวยได้เลย</p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="/register" class="btn-gold text-sm px-8 py-4 rounded-2xl text-center font-bold inline-flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            สมัครสมาชิกฟรี
                        </a>
                        <a href="/login" class="px-8 py-4 bg-white/10 hover:bg-white/20 text-white rounded-2xl text-sm font-semibold transition-all backdrop-blur-sm border border-white/10">เข้าสู่ระบบ</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer id="contact" class="border-t border-white/5 py-12 lg:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-10">
                <div class="md:col-span-2">
                    <a href="/" class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-brand-950 shadow-lg" style="background:linear-gradient(135deg,#fbbf24,#f59e0b)">L</div>
                        <span class="text-lg font-black text-white">{{ $siteName }}</span>
                    </a>
                    <p class="text-sm text-white/30 leading-relaxed max-w-sm">แทงหวยออนไลน์ บริการครบวงจร จ่ายเต็ม ราคาดีที่สุด ฝาก-ถอนออโต้ รวดเร็ว ปลอดภัย 100%</p>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-white/60 uppercase tracking-wider mb-4">บริการ</h4>
                    <ul class="space-y-2.5">
                        <li><a href="#lottery" class="text-sm text-white/30 hover:text-gold-400 transition-colors">หวยทั้งหมด</a></li>
                        <li><a href="#results" class="text-sm text-white/30 hover:text-gold-400 transition-colors">ผลหวย</a></li>
                        <li><a href="#features" class="text-sm text-white/30 hover:text-gold-400 transition-colors">บริการ</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-white/60 uppercase tracking-wider mb-4">ช่วยเหลือ</h4>
                    <ul class="space-y-2.5">
                        <li><a href="#" class="text-sm text-white/30 hover:text-gold-400 transition-colors">วิธีสมัคร</a></li>
                        <li><a href="#" class="text-sm text-white/30 hover:text-gold-400 transition-colors">วิธีฝากเงิน</a></li>
                        <li><a href="#" class="text-sm text-white/30 hover:text-gold-400 transition-colors">ติดต่อเรา</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/5 pt-8 text-center">
                <p class="text-xs text-white/20">&copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.</p>
            </div>
        </div>
    </footer>

</body>
</html>
