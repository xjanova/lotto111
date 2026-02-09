<!DOCTYPE html>
<html lang="th" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - {{ \App\Models\Setting::getValue('site_name', 'Lotto111') }}</title>

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a' },
                        sidebar: { bg:'#0f172a', hover:'#1e293b', active:'#334155', text:'#94a3b8', textActive:'#f8fafc' }
                    }
                }
            }
        }
    </script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-scrollbar::-webkit-scrollbar { width: 4px; }
        .sidebar-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 2px; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(8px); } to { opacity:1; transform:translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.3s ease-out; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
        .stat-card { transition: all 0.2s ease; }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-gray-50" x-data="{ sidebarOpen: true, mobileSidebarOpen: false }">
    <div class="flex h-full">

        {{-- Mobile Sidebar Overlay --}}
        <div x-show="mobileSidebarOpen" x-cloak
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 z-40 lg:hidden"
             @click="mobileSidebarOpen = false"></div>

        {{-- Sidebar --}}
        @include('admin.partials.sidebar')

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col min-w-0" :class="sidebarOpen ? 'lg:ml-64' : 'lg:ml-20'">
            {{-- Header --}}
            @include('admin.partials.header')

            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto p-4 md:p-6">
                {{-- Breadcrumb --}}
                @hasSection('breadcrumb')
                <nav class="mb-4 text-sm text-gray-500">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-600">แดชบอร์ด</a>
                    <span class="mx-1">/</span>
                    @yield('breadcrumb')
                </nav>
                @endif

                {{-- Flash Messages --}}
                @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-center gap-2 animate-fade-in" x-data="{ show:true }" x-show="show">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>{{ session('success') }}</span>
                    <button @click="show=false" class="ml-auto">&times;</button>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-center gap-2 animate-fade-in" x-data="{ show:true }" x-show="show">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span>{{ session('error') }}</span>
                    <button @click="show=false" class="ml-auto">&times;</button>
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

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
        // Number formatting
        window.fmtNum = (n) => new Intl.NumberFormat('th-TH').format(n);
        window.fmtMoney = (n) => new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(n);
    </script>
    @stack('scripts')
</body>
</html>
