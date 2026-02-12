<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>เข้าสู่ระบบ - {{ config('app.name', 'Lotto111') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['"Noto Sans Thai"', 'system-ui', 'sans-serif'] } } }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>
        [x-cloak]{display:none!important}
        @keyframes blob{0%,100%{transform:translate(0,0) scale(1)}33%{transform:translate(30px,-50px) scale(1.1)}66%{transform:translate(-20px,20px) scale(0.9)}}
        @keyframes fadeUp{0%{opacity:0;transform:translateY(20px)}100%{opacity:1;transform:translateY(0)}}
        .animate-blob{animation:blob 7s infinite}
        .animate-fade-up{animation:fadeUp 0.5s ease-out forwards;opacity:0}
        .delay-100{animation-delay:100ms}.delay-200{animation-delay:200ms}
    </style>
</head>
<body class="min-h-screen font-sans" style="background: linear-gradient(135deg, #0f0a2e 0%, #1e1b4b 40%, #312e81 100%);">

    {{-- Animated Background --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-600/20 rounded-full filter blur-[100px] animate-blob"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-indigo-600/20 rounded-full filter blur-[100px] animate-blob" style="animation-delay:2s"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-amber-500/10 rounded-full filter blur-[120px] animate-blob" style="animation-delay:4s"></div>
    </div>

    <div class="relative min-h-screen flex items-center justify-center px-4 py-8">
        <div class="w-full max-w-md" x-data="loginApp()" x-cloak>

            {{-- Logo / Brand --}}
            <div class="text-center mb-8 animate-fade-up">
                <a href="/" class="inline-block">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </a>
                <h1 class="text-2xl font-bold text-white mb-1">เข้าสู่ระบบ</h1>
                <p class="text-white/50 text-sm">ยินดีต้อนรับกลับ</p>
            </div>

            {{-- Login Card --}}
            <div class="rounded-2xl border border-white/10 overflow-hidden animate-fade-up delay-100" style="background: rgba(255,255,255,0.05); backdrop-filter: blur(20px);">
                <div class="px-6 py-8 space-y-5">

                    {{-- Phone Input --}}
                    <div>
                        <label class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-2">เบอร์โทรศัพท์</label>
                        <div class="relative">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 flex items-center gap-2">
                                <span class="text-lg">🇹🇭</span>
                                <span class="text-white/40 text-sm">+66</span>
                                <div class="w-px h-5 bg-white/10"></div>
                            </div>
                            <input type="tel" x-model="phone" maxlength="10"
                                   placeholder="0812345678"
                                   @keydown.enter="submit()"
                                   class="w-full pl-24 pr-4 py-3.5 rounded-xl text-white text-sm placeholder-white/20 outline-none transition-all focus:ring-2 focus:ring-amber-500/30"
                                   style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);"
                                   @focus="$el.style.borderColor='rgba(251,191,36,0.4)'"
                                   @blur="$el.style.borderColor='rgba(255,255,255,0.1)'">
                        </div>
                    </div>

                    {{-- Password Input --}}
                    <div>
                        <label class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-2">รหัสผ่าน</label>
                        <div class="relative">
                            <input :type="showPass ? 'text' : 'password'" x-model="password"
                                   placeholder="กรอกรหัสผ่าน"
                                   @keydown.enter="submit()"
                                   class="w-full px-4 py-3.5 pr-12 rounded-xl text-white text-sm placeholder-white/20 outline-none transition-all focus:ring-2 focus:ring-amber-500/30"
                                   style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);"
                                   @focus="$el.style.borderColor='rgba(251,191,36,0.4)'"
                                   @blur="$el.style.borderColor='rgba(255,255,255,0.1)'">
                            <button type="button" @click="showPass = !showPass" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition-colors">
                                <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Remember & Forgot --}}
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="remember" class="w-4 h-4 rounded border-white/20 bg-white/5 text-amber-500 focus:ring-amber-500/20">
                            <span class="text-xs text-white/40">จดจำการเข้าสู่ระบบ</span>
                        </label>
                    </div>

                    {{-- Error Message --}}
                    <div x-show="error" x-transition class="px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20">
                        <p class="text-xs text-red-400" x-text="error"></p>
                    </div>

                    {{-- Submit Button --}}
                    <button @click="submit()" :disabled="loading"
                            class="w-full py-3.5 rounded-xl text-sm font-bold text-white transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                            style="background: linear-gradient(135deg, #fbbf24, #f59e0b, #d97706);">
                        <span x-show="!loading">เข้าสู่ระบบ</span>
                        <span x-show="loading" class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            กำลังเข้าสู่ระบบ...
                        </span>
                    </button>
                </div>

                {{-- Demo Login Button --}}
                @if(\App\Models\Setting::getValue('demo_mode', false))
                <div class="px-6 pb-2">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-white/10"></div></div>
                        <div class="relative flex justify-center text-xs"><span class="px-3 text-white/30" style="background: rgba(255,255,255,0.05);">หรือ</span></div>
                    </div>
                    <button @click="demoLogin()" :disabled="loading"
                            class="w-full mt-3 py-3 rounded-xl text-sm font-bold text-white transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-2"
                            style="background: linear-gradient(135deg, #f59e0b, #ef4444); border: 1px solid rgba(255,255,255,0.1);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        ทดลองใช้งาน (Demo)
                    </button>
                    <p class="text-center text-[10px] text-white/25 mt-2">เข้าใช้งานด้วยบัญชีจำลอง ข้อมูลไม่ใช่ของจริง</p>
                </div>
                @endif

                {{-- Footer Link --}}
                <div class="px-6 py-4 border-t border-white/5 text-center">
                    <span class="text-sm text-white/30">ยังไม่มีบัญชี?</span>
                    <a href="/register" class="text-sm font-semibold text-amber-400 hover:text-amber-300 ml-1 transition-colors">สมัครสมาชิก</a>
                </div>
            </div>

            {{-- Back to Home --}}
            <div class="text-center mt-6 animate-fade-up delay-200">
                <a href="/" class="text-sm text-white/30 hover:text-white/60 transition-colors inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    กลับหน้าหลัก
                </a>
            </div>
        </div>
    </div>

    <script>
    function loginApp() {
        return {
            phone: '',
            password: '',
            showPass: false,
            remember: false,
            loading: false,
            error: '',

            async demoLogin() {
                this.loading = true;
                this.error = '';
                try {
                    const res = await fetch('/demo-login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    });
                    const data = await res.json();
                    if (data.success) {
                        window.location.href = data.redirect || '/member';
                    } else {
                        this.error = data.message || 'เกิดข้อผิดพลาด';
                    }
                } catch (err) {
                    this.error = 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้';
                } finally {
                    this.loading = false;
                }
            },

            async submit() {
                if (!this.phone) { this.error = 'กรุณากรอกเบอร์โทรศัพท์'; return; }
                if (!this.password) { this.error = 'กรุณากรอกรหัสผ่าน'; return; }

                this.loading = true;
                this.error = '';

                try {
                    const res = await fetch('/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            phone: this.phone,
                            password: this.password,
                            remember: this.remember,
                        }),
                    });

                    const data = await res.json();

                    if (data.success) {
                        window.location.href = data.redirect || '/';
                    } else {
                        this.error = data.message || 'เกิดข้อผิดพลาด';
                    }
                } catch (err) {
                    this.error = 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้';
                } finally {
                    this.loading = false;
                }
            }
        }
    }
    </script>
</body>
</html>
