<!DOCTYPE html>
<html lang="th" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login - {{ config('app.name', 'Lotto Platform') }}</title>

    {{-- Google Fonts: Noto Sans Thai --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Noto Sans Thai"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>

    {{-- Alpine.js CDN --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Noto Sans Thai', sans-serif;
        }

        /* ---------- animated gradient blobs ---------- */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: .45;
            animation: float 8s ease-in-out infinite;
        }
        .blob-1 {
            width: 420px; height: 420px;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            top: -80px; left: -100px;
            animation-delay: 0s;
        }
        .blob-2 {
            width: 350px; height: 350px;
            background: linear-gradient(135deg, #7c3aed 0%, #ec4899 100%);
            bottom: -60px; right: -80px;
            animation-delay: -3s;
        }
        .blob-3 {
            width: 260px; height: 260px;
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
            top: 50%; left: 60%;
            transform: translate(-50%, -50%);
            animation-delay: -5s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            33%      { transform: translateY(-30px) scale(1.05); }
            66%      { transform: translateY(20px) scale(.95); }
        }

        /* ---------- glassmorphism card ---------- */
        .glass-card {
            background: rgba(30, 27, 75, 0.55);
            backdrop-filter: blur(24px) saturate(1.6);
            -webkit-backdrop-filter: blur(24px) saturate(1.6);
            border: 1px solid rgba(99, 102, 241, 0.18);
            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.35),
                inset 0 1px 0 rgba(255, 255, 255, 0.06);
        }

        /* ---------- gradient border glow on focus ---------- */
        .input-glow:focus {
            box-shadow:
                0 0 0 2px rgba(99, 102, 241, 0.5),
                0 0 20px rgba(99, 102, 241, 0.15);
        }

        /* ---------- gradient button shine sweep ---------- */
        .btn-gradient {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a855f7 100%);
            background-size: 200% 200%;
            animation: gradient-shift 4s ease infinite;
            position: relative;
            overflow: hidden;
        }
        .btn-gradient::after {
            content: '';
            position: absolute;
            top: 0; left: -100%; width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.12), transparent);
            transition: left .5s ease;
        }
        .btn-gradient:hover::after {
            left: 100%;
        }
        @keyframes gradient-shift {
            0%, 100% { background-position: 0% 50%; }
            50%      { background-position: 100% 50%; }
        }

        /* ---------- custom checkbox ---------- */
        .custom-checkbox {
            appearance: none;
            -webkit-appearance: none;
            width: 18px; height: 18px;
            border: 2px solid rgba(99, 102, 241, 0.4);
            border-radius: 5px;
            background: rgba(30, 27, 75, 0.6);
            cursor: pointer;
            position: relative;
            transition: all .2s ease;
        }
        .custom-checkbox:checked {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-color: #818cf8;
        }
        .custom-checkbox:checked::after {
            content: '';
            position: absolute;
            left: 4px; top: 1px;
            width: 6px; height: 10px;
            border: solid #fff;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        .custom-checkbox:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.4);
        }

        /* ---------- fade-in entrance ---------- */
        .fade-up {
            animation: fade-up .7s cubic-bezier(.22,1,.36,1) forwards;
            opacity: 0;
        }
        @keyframes fade-up {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body class="h-full overflow-hidden" style="background: #1e1b4b;">
    {{-- Background layer with blobs --}}
    <div class="fixed inset-0 overflow-hidden" style="background: linear-gradient(145deg, #1e1b4b 0%, #0f0a2e 50%, #1a1145 100%);">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
        {{-- Subtle grid overlay --}}
        <div class="absolute inset-0 opacity-[0.03]"
             style="background-image: linear-gradient(rgba(255,255,255,.1) 1px, transparent 1px),
                                      linear-gradient(90deg, rgba(255,255,255,.1) 1px, transparent 1px);
                    background-size: 60px 60px;">
        </div>
    </div>

    {{-- Main content --}}
    <div class="relative z-10 flex min-h-full items-center justify-center px-4 py-12" x-data="{ showPassword: false }">
        <div class="w-full max-w-md fade-up">

            {{-- Glass Card --}}
            <div class="glass-card rounded-3xl p-8 sm:p-10">

                {{-- Logo / Icon --}}
                <div class="flex justify-center mb-6">
                    <div class="relative">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center"
                             style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
                                    box-shadow: 0 8px 24px rgba(99, 102, 241, 0.35);">
                            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                        </div>
                        {{-- Glow ring --}}
                        <div class="absolute -inset-1 rounded-2xl opacity-30 blur-sm"
                             style="background: linear-gradient(135deg, #6366f1, #a855f7);"></div>
                    </div>
                </div>

                {{-- Heading --}}
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold text-white tracking-tight">Admin Panel</h1>
                    <p class="mt-1.5 text-sm font-light" style="color: rgba(165, 163, 210, 0.8);">
                        เข้าสู่ระบบจัดการ Lotto Platform
                    </p>
                </div>

                {{-- Error display --}}
                @if($errors->any())
                <div class="mb-6 p-4 rounded-xl text-sm flex items-start gap-3"
                     style="background: rgba(239, 68, 68, 0.1);
                            border: 1px solid rgba(239, 68, 68, 0.25);">
                    <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <span class="text-red-300 leading-relaxed">{{ $errors->first() }}</span>
                </div>
                @endif

                {{-- Login Form --}}
                <form method="POST" action="{{ url('/admin/login') }}" class="space-y-5">
                    @csrf

                    {{-- Username field --}}
                    <div>
                        <label for="username" class="block text-sm font-medium mb-2" style="color: rgba(199, 197, 240, 0.9);">
                            ชื่อผู้ใช้ (ID)
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                <svg class="w-5 h-5" style="color: rgba(129, 140, 248, 0.5);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>
                            <input type="text"
                                   id="username"
                                   name="username"
                                   value="{{ old('username') }}"
                                   required
                                   autofocus
                                   placeholder="admin"
                                   class="input-glow w-full rounded-xl py-3.5 pl-12 pr-4 text-white placeholder-indigo-300/30
                                          border transition-all duration-200 focus:outline-none"
                                   style="background: rgba(30, 27, 75, 0.6);
                                          border-color: rgba(99, 102, 241, 0.2);">
                        </div>
                    </div>

                    {{-- Password field --}}
                    <div>
                        <label for="password" class="block text-sm font-medium mb-2" style="color: rgba(199, 197, 240, 0.9);">
                            รหัสผ่าน
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                <svg class="w-5 h-5" style="color: rgba(129, 140, 248, 0.5);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                            </div>
                            <input :type="showPassword ? 'text' : 'password'"
                                   id="password"
                                   name="password"
                                   required
                                   placeholder="••••••••"
                                   class="input-glow w-full rounded-xl py-3.5 pl-12 pr-12 text-white placeholder-indigo-300/30
                                          border transition-all duration-200 focus:outline-none"
                                   style="background: rgba(30, 27, 75, 0.6);
                                          border-color: rgba(99, 102, 241, 0.2);">
                            {{-- Toggle password visibility --}}
                            <button type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 flex items-center pr-4 focus:outline-none">
                                <svg x-show="!showPassword" class="w-5 h-5 transition-colors hover:text-indigo-300" style="color: rgba(129, 140, 248, 0.5);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg x-show="showPassword" x-cloak class="w-5 h-5 transition-colors hover:text-indigo-300" style="color: rgba(129, 140, 248, 0.5);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Remember me --}}
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2.5 cursor-pointer select-none group">
                            <input type="checkbox" id="remember" name="remember" class="custom-checkbox">
                            <span class="text-sm transition-colors group-hover:text-indigo-300" style="color: rgba(165, 163, 210, 0.7);">
                                จดจำการเข้าสู่ระบบ
                            </span>
                        </label>
                    </div>

                    {{-- Submit button --}}
                    <div class="pt-2">
                        <button type="submit"
                                class="btn-gradient w-full py-3.5 rounded-xl text-white font-semibold text-sm tracking-wide
                                       transition-all duration-300 hover:shadow-lg hover:shadow-indigo-500/25 hover:scale-[1.02]
                                       active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:ring-offset-2"
                                style="focus-ring-offset-color: #1e1b4b;">
                            เข้าสู่ระบบ
                        </button>
                    </div>
                </form>

                {{-- Footer --}}
                <div class="mt-8 text-center">
                    <p class="text-xs" style="color: rgba(129, 127, 180, 0.5);">
                        &copy; {{ date('Y') }} {{ config('app.name', 'Lotto Platform') }} &mdash; Admin Panel
                    </p>
                </div>
            </div>

        </div>
    </div>
</body>
</html>
