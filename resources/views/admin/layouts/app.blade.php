<!DOCTYPE html>
<html lang="th" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - {{ \App\Models\Setting::getValue('site_name', 'Lotto111') }}</title>

    <!-- Google Fonts: Noto Sans Thai -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Noto Sans Thai"', 'Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                    colors: {
                        brand: { 50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#312e81',950:'#1e1b4b' },
                        sidebar: { bg:'#1e1b4b', hover:'#312e81', active:'#4338ca', text:'#a5b4fc', textActive:'#e0e7ff', border:'#3730a3' }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.4s ease-out',
                        'fade-up': 'fadeUp 0.5s ease-out',
                        'slide-in': 'slideIn 0.3s ease-out',
                        'blob': 'blob 7s infinite',
                        'pulse-glow': 'pulseGlow 2s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                        fadeUp: { '0%': { opacity: '0', transform: 'translateY(12px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                        slideIn: { '0%': { opacity: '0', transform: 'translateX(-12px)' }, '100%': { opacity: '1', transform: 'translateX(0)' } },
                        blob: { '0%, 100%': { transform: 'translate(0,0) scale(1)' }, '33%': { transform: 'translate(30px,-50px) scale(1.1)' }, '66%': { transform: 'translate(-20px,20px) scale(0.9)' } },
                        pulseGlow: { '0%, 100%': { opacity: '1' }, '50%': { opacity: '0.7' } },
                    }
                }
            }
        }
    </script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }

        /* Premium scrollbar */
        .sidebar-scrollbar::-webkit-scrollbar { width: 5px; }
        .sidebar-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #6366f1, #a855f7);
            border-radius: 10px;
        }

        /* Stat card hover */
        .stat-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -12px rgba(99, 102, 241, 0.15);
        }

        /* Gradient text helper */
        .gradient-text {
            background: linear-gradient(135deg, #6366f1, #a855f7, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Table row hover */
        .table-row-hover:hover {
            background: linear-gradient(90deg, rgba(99,102,241,0.04), rgba(168,85,247,0.04));
        }

        /* Premium button */
        .btn-premium {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            transition: all 0.3s ease;
        }
        .btn-premium:hover {
            background: linear-gradient(135deg, #818cf8, #6366f1);
            box-shadow: 0 8px 25px -5px rgba(99, 102, 241, 0.4);
            transform: translateY(-1px);
        }

        /* Card base */
        .card-premium {
            background: white;
            border: 1px solid #e0e7ff;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(99, 102, 241, 0.06);
            transition: all 0.3s ease;
        }
        .card-premium:hover {
            border-color: #c7d2fe;
            box-shadow: 0 8px 30px -8px rgba(99, 102, 241, 0.12);
        }

        /* Animation delays */
        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
        .delay-300 { animation-delay: 300ms; }
        .delay-400 { animation-delay: 400ms; }
        .delay-500 { animation-delay: 500ms; }
        .delay-600 { animation-delay: 600ms; }
        .delay-700 { animation-delay: 700ms; }
        .delay-800 { animation-delay: 800ms; }

        /* Badge pulse */
        .badge-pulse {
            animation: pulseGlow 2s ease-in-out infinite;
        }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-gradient-to-br from-slate-50 via-indigo-50/30 to-purple-50/20 font-sans" x-data="{ sidebarOpen: true, mobileSidebarOpen: false }">
    <div class="flex h-full">

        {{-- Mobile Sidebar Overlay --}}
        <div x-show="mobileSidebarOpen" x-cloak
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden"
             @click="mobileSidebarOpen = false"></div>

        {{-- Sidebar --}}
        @include('admin.partials.sidebar')

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col min-w-0" :class="sidebarOpen ? 'lg:ml-64' : 'lg:ml-20'">
            {{-- Header --}}
            @include('admin.partials.header')

            {{-- Demo Mode Banner --}}
            @if(\App\Models\Setting::getValue('demo_mode', false))
            <div class="mx-4 md:mx-6 lg:mx-8 mt-3">
                <div class="relative overflow-hidden rounded-xl px-4 py-2.5" style="background: linear-gradient(135deg, #f59e0b, #f97316, #ef4444);">
                    <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=&quot;40&quot; height=&quot;40&quot; viewBox=&quot;0 0 40 40&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;%23fff&quot; fill-opacity=&quot;0.06&quot;%3E%3Cpath d=&quot;M0 0h20v20H0zM20 20h20v20H20z&quot;/%3E%3C/g%3E%3C/svg%3E')]"></div>
                    <div class="relative flex items-center justify-center gap-3">
                        <span class="flex items-center gap-1.5 bg-white/20 backdrop-blur-sm px-3 py-1 rounded-lg">
                            <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                            <span class="text-xs font-bold text-white tracking-wider uppercase">DEMO</span>
                        </span>
                        <span class="text-sm font-medium text-white/90">กำลังใช้งานโหมดสาธิต — ข้อมูลทั้งหมดเป็นข้อมูลจำลอง</span>
                        <a href="{{ route('admin.settings.index') }}?tab=demo" class="ml-2 text-xs bg-white/20 hover:bg-white/30 text-white px-3 py-1 rounded-lg transition-all">
                            ตั้งค่า
                        </a>
                    </div>
                </div>
            </div>
            @endif

            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
                {{-- Breadcrumb --}}
                @hasSection('breadcrumb')
                <nav class="mb-5 flex items-center gap-2 text-sm text-gray-500">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-600 transition-colors flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        แดชบอร์ด
                    </a>
                    <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    @yield('breadcrumb')
                </nav>
                @endif

                {{-- Flash Messages --}}
                @if(session('success'))
                <div class="mb-5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center gap-3 animate-fade-up shadow-sm" x-data="{ show:true }" x-show="show">
                    <div class="flex-shrink-0 w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    </div>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                    <button @click="show=false" class="ml-auto text-emerald-400 hover:text-emerald-600 transition-colors">&times;</button>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-5 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3 animate-fade-up shadow-sm" x-data="{ show:true }" x-show="show">
                    <div class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    </div>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                    <button @click="show=false" class="ml-auto text-red-400 hover:text-red-600 transition-colors">&times;</button>
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    {{-- Toast Notifications --}}
    <div id="toast-container" class="fixed top-4 right-4 z-[60] space-y-3"></div>

    {{-- Global CSRF for fetch --}}
    <script>
        window.csrfToken = '{{ csrf_token() }}';
        window.fetchApi = (url, options = {}) => {
            return fetch(url, {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    ...options.headers,
                },
                ...options,
            }).then(r => r.json());
        };
        window.fmtNum = (n) => new Intl.NumberFormat('th-TH').format(n || 0);
        window.fmtMoney = (n) => new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n || 0);

        window.showToast = (message, type = 'info') => {
            const container = document.getElementById('toast-container');
            const colors = {
                success: 'bg-emerald-500',
                error: 'bg-red-500',
                info: 'bg-brand-500',
                warning: 'bg-amber-500',
            };
            const toast = document.createElement('div');
            toast.className = `${colors[type] || colors.info} text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium animate-fade-up flex items-center gap-2`;
            toast.innerHTML = `<span>${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-8px)';
                toast.style.transition = 'all 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        };
    </script>
    @stack('scripts')
</body>
</html>
