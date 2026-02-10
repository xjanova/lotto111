<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>สมัครสมาชิก - {{ config('app.name', 'Lotto111') }}</title>
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
        .delay-100{animation-delay:100ms}.delay-200{animation-delay:200ms}.delay-300{animation-delay:300ms}
        .gradient-text{background:linear-gradient(135deg,#fbbf24,#f59e0b,#d97706);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
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
        <div class="w-full max-w-md" x-data="registerApp()" x-cloak>

            {{-- Logo / Brand --}}
            <div class="text-center mb-8 animate-fade-up">
                <a href="/" class="inline-block">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl flex items-center justify-center" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                        <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </a>
                <h1 class="text-2xl font-bold text-white mb-1">สมัครสมาชิก</h1>
                <p class="text-white/50 text-sm">เริ่มต้นเล่นหวยออนไลน์กับเรา</p>
            </div>

            {{-- Referral Badge --}}
            @if($referrer)
            <div class="mb-6 animate-fade-up delay-100">
                <div class="flex items-center gap-3 px-4 py-3 rounded-xl border border-amber-500/20" style="background: rgba(251,191,36,0.08);">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-amber-500/20">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs text-amber-400/70">แนะนำโดย</div>
                        <div class="text-sm font-semibold text-amber-300">{{ $referrer->name }}</div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Registration Card --}}
            <div class="rounded-2xl border border-white/10 overflow-hidden animate-fade-up delay-200" style="background: rgba(255,255,255,0.05); backdrop-filter: blur(20px);">

                {{-- Step Indicator --}}
                <div class="px-6 pt-6 pb-4">
                    <div class="flex items-center justify-between">
                        <template x-for="(s, i) in ['ยืนยันเบอร์', 'ข้อมูลสมาชิก']" :key="i">
                            <div class="flex items-center" :class="i < 1 ? 'flex-1' : ''">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300"
                                         :class="step > i+1 ? 'bg-emerald-500 text-white' : step === i+1 ? 'text-white' : 'bg-white/10 text-white/30'"
                                         :style="step === i+1 ? 'background: linear-gradient(135deg, #fbbf24, #f59e0b)' : ''">
                                        <template x-if="step > i+1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        </template>
                                        <template x-if="step <= i+1">
                                            <span x-text="i+1"></span>
                                        </template>
                                    </div>
                                    <span class="text-xs font-medium" :class="step >= i+1 ? 'text-white/80' : 'text-white/30'" x-text="s"></span>
                                </div>
                                <template x-if="i < 1">
                                    <div class="flex-1 mx-3 h-0.5 rounded-full" :class="step > 1 ? 'bg-emerald-500' : 'bg-white/10'"></div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Step 1: Phone Verification --}}
                <div x-show="step === 1" class="px-6 pb-6 space-y-4">
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
                                   class="w-full pl-24 pr-4 py-3.5 rounded-xl text-white text-sm placeholder-white/20 outline-none transition-all focus:ring-2 focus:ring-amber-500/30"
                                   style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);"
                                   @focus="$el.style.borderColor='rgba(251,191,36,0.4)'"
                                   @blur="$el.style.borderColor='rgba(255,255,255,0.1)'"
                                   :disabled="otpSent && otpVerified">
                        </div>
                        <p x-show="phoneError" x-text="phoneError" class="mt-1.5 text-xs text-red-400"></p>
                    </div>

                    {{-- OTP Section --}}
                    <template x-if="otpSent && !otpVerified">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-2">รหัส OTP (6 หลัก)</label>
                                <div class="flex gap-2">
                                    <template x-for="(d, i) in 6" :key="i">
                                        <input type="text" maxlength="1" inputmode="numeric"
                                               :id="'otp-' + i"
                                               class="w-full h-12 text-center text-lg font-bold text-white rounded-xl outline-none transition-all focus:ring-2 focus:ring-amber-500/30"
                                               style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);"
                                               @input="handleOtpInput($event, i)"
                                               @keydown.backspace="handleOtpBackspace($event, i)"
                                               @paste="handleOtpPaste($event)">
                                    </template>
                                </div>
                                <p x-show="otpError" x-text="otpError" class="mt-1.5 text-xs text-red-400"></p>
                            </div>

                            <div class="flex items-center justify-between">
                                <button @click="resendOtp()" :disabled="resendCooldown > 0"
                                        class="text-xs text-amber-400 hover:text-amber-300 disabled:text-white/20 disabled:cursor-not-allowed transition-colors">
                                    <span x-show="resendCooldown > 0">ส่งอีกครั้งใน <span x-text="resendCooldown"></span>s</span>
                                    <span x-show="resendCooldown <= 0">ส่ง OTP อีกครั้ง</span>
                                </button>
                                <button @click="otpSent=false; otpError=''" class="text-xs text-white/30 hover:text-white/50 transition-colors">เปลี่ยนเบอร์</button>
                            </div>

                            <button @click="verifyOtp()" :disabled="otpLoading"
                                    class="w-full py-3.5 rounded-xl text-sm font-bold text-white transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                                    style="background: linear-gradient(135deg, #fbbf24, #f59e0b, #d97706);">
                                <span x-show="!otpLoading">ยืนยัน OTP</span>
                                <span x-show="otpLoading" class="flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    กำลังยืนยัน...
                                </span>
                            </button>
                        </div>
                    </template>

                    {{-- Verified Badge --}}
                    <template x-if="otpVerified">
                        <div class="flex items-center gap-2 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-sm text-emerald-300">ยืนยันเบอร์สำเร็จ</span>
                        </div>
                    </template>

                    {{-- Send OTP Button --}}
                    <template x-if="!otpSent && !otpVerified">
                        <button @click="sendOtp()" :disabled="sendingOtp || !isValidPhone()"
                                class="w-full py-3.5 rounded-xl text-sm font-bold text-white transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                                style="background: linear-gradient(135deg, #fbbf24, #f59e0b, #d97706);">
                            <span x-show="!sendingOtp">ส่งรหัส OTP</span>
                            <span x-show="sendingOtp" class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                กำลังส่ง...
                            </span>
                        </button>
                    </template>

                    {{-- Next Step Button (shown after verification) --}}
                    <template x-if="otpVerified">
                        <button @click="step = 2"
                                class="w-full py-3.5 rounded-xl text-sm font-bold text-white transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]"
                                style="background: linear-gradient(135deg, #fbbf24, #f59e0b, #d97706);">
                            ถัดไป
                            <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </template>
                </div>

                {{-- Step 2: User Info --}}
                <div x-show="step === 2" x-transition class="px-6 pb-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-2">ชื่อ-นามสกุล</label>
                        <input type="text" x-model="name"
                               placeholder="กรอกชื่อ-นามสกุล"
                               class="w-full px-4 py-3.5 rounded-xl text-white text-sm placeholder-white/20 outline-none transition-all focus:ring-2 focus:ring-amber-500/30"
                               style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);"
                               @focus="$el.style.borderColor='rgba(251,191,36,0.4)'"
                               @blur="$el.style.borderColor='rgba(255,255,255,0.1)'">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-2">รหัสผ่าน</label>
                        <div class="relative">
                            <input :type="showPass ? 'text' : 'password'" x-model="password"
                                   placeholder="อย่างน้อย 6 ตัวอักษร"
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

                    <div>
                        <label class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-2">ยืนยันรหัสผ่าน</label>
                        <input :type="showPass ? 'text' : 'password'" x-model="passwordConfirm"
                               placeholder="กรอกรหัสผ่านอีกครั้ง"
                               class="w-full px-4 py-3.5 rounded-xl text-white text-sm placeholder-white/20 outline-none transition-all focus:ring-2 focus:ring-amber-500/30"
                               style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);"
                               @focus="$el.style.borderColor='rgba(251,191,36,0.4)'"
                               @blur="$el.style.borderColor='rgba(255,255,255,0.1)'">
                    </div>

                    @if(!$ref)
                    <div>
                        <label class="block text-xs font-semibold text-white/50 uppercase tracking-wider mb-2">รหัสแนะนำ (ถ้ามี)</label>
                        <input type="text" x-model="referralCode"
                               placeholder="กรอกรหัสแนะนำ"
                               class="w-full px-4 py-3.5 rounded-xl text-white text-sm placeholder-white/20 outline-none transition-all focus:ring-2 focus:ring-amber-500/30"
                               style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);"
                               @focus="$el.style.borderColor='rgba(251,191,36,0.4)'"
                               @blur="$el.style.borderColor='rgba(255,255,255,0.1)'">
                    </div>
                    @endif

                    {{-- Error Message --}}
                    <div x-show="registerError" class="px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/20">
                        <p class="text-xs text-red-400" x-text="registerError"></p>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex gap-3">
                        <button @click="step = 1" class="px-6 py-3.5 rounded-xl text-sm font-medium text-white/50 hover:text-white/80 transition-colors" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);">
                            ย้อนกลับ
                        </button>
                        <button @click="submitRegister()" :disabled="registerLoading"
                                class="flex-1 py-3.5 rounded-xl text-sm font-bold text-white transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                                style="background: linear-gradient(135deg, #fbbf24, #f59e0b, #d97706);">
                            <span x-show="!registerLoading">สมัครสมาชิก</span>
                            <span x-show="registerLoading" class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                กำลังสมัคร...
                            </span>
                        </button>
                    </div>
                </div>

                {{-- Footer Link --}}
                <div class="px-6 py-4 border-t border-white/5 text-center">
                    <span class="text-sm text-white/30">มีบัญชีอยู่แล้ว?</span>
                    <a href="/login" class="text-sm font-semibold text-amber-400 hover:text-amber-300 ml-1 transition-colors">เข้าสู่ระบบ</a>
                </div>
            </div>

            {{-- Back to Home --}}
            <div class="text-center mt-6 animate-fade-up delay-300">
                <a href="/" class="text-sm text-white/30 hover:text-white/60 transition-colors inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    กลับหน้าหลัก
                </a>
            </div>
        </div>
    </div>

    {{-- Firebase SDK --}}
    <script src="https://www.gstatic.com/firebasejs/10.14.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.14.1/firebase-auth-compat.js"></script>

    <script>
    // Firebase config - set via admin settings or .env
    const firebaseConfig = {
        apiKey: "{{ config('services.firebase.api_key', '') }}",
        authDomain: "{{ config('services.firebase.auth_domain', '') }}",
        projectId: "{{ config('services.firebase.project_id', '') }}",
        storageBucket: "{{ config('services.firebase.storage_bucket', '') }}",
        messagingSenderId: "{{ config('services.firebase.messaging_sender_id', '') }}",
        appId: "{{ config('services.firebase.app_id', '') }}",
    };

    firebase.initializeApp(firebaseConfig);
    const auth = firebase.auth();
    auth.languageCode = 'th';

    function registerApp() {
        return {
            step: 1,
            phone: '',
            name: '',
            password: '',
            passwordConfirm: '',
            referralCode: @json($ref ?? ''),
            showPass: false,

            // OTP state
            otpSent: false,
            otpVerified: false,
            otpCode: '',
            otpError: '',
            otpLoading: false,
            phoneError: '',
            sendingOtp: false,
            resendCooldown: 0,
            resendTimer: null,
            confirmationResult: null,
            firebaseUid: '',

            // Register state
            registerLoading: false,
            registerError: '',

            isValidPhone() {
                return /^0[0-9]{9}$/.test(this.phone);
            },

            formatPhoneE164() {
                // Convert 0812345678 to +66812345678
                return '+66' + this.phone.substring(1);
            },

            async sendOtp() {
                if (!this.isValidPhone()) {
                    this.phoneError = 'กรุณากรอกเบอร์โทร 10 หลัก (ตัวอย่าง: 0812345678)';
                    return;
                }
                this.phoneError = '';
                this.sendingOtp = true;

                try {
                    // Setup invisible reCAPTCHA
                    if (!window.recaptchaVerifier) {
                        window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
                            size: 'invisible',
                            callback: () => {}
                        });
                    }

                    const phoneE164 = this.formatPhoneE164();
                    this.confirmationResult = await auth.signInWithPhoneNumber(phoneE164, window.recaptchaVerifier);
                    this.otpSent = true;
                    this.startResendCooldown();
                } catch (err) {
                    console.error('Firebase OTP error:', err);
                    if (err.code === 'auth/invalid-phone-number') {
                        this.phoneError = 'เบอร์โทรศัพท์ไม่ถูกต้อง';
                    } else if (err.code === 'auth/too-many-requests') {
                        this.phoneError = 'ส่ง OTP เกินจำนวน กรุณารอสักครู่';
                    } else {
                        this.phoneError = 'ไม่สามารถส่ง OTP ได้ กรุณาลองใหม่';
                    }
                    // Reset reCAPTCHA on error
                    if (window.recaptchaVerifier) {
                        window.recaptchaVerifier.clear();
                        window.recaptchaVerifier = null;
                    }
                } finally {
                    this.sendingOtp = false;
                }
            },

            async resendOtp() {
                if (this.resendCooldown > 0) return;
                // Reset reCAPTCHA for resend
                if (window.recaptchaVerifier) {
                    window.recaptchaVerifier.clear();
                    window.recaptchaVerifier = null;
                }
                this.otpSent = false;
                this.otpError = '';
                await this.sendOtp();
            },

            startResendCooldown() {
                this.resendCooldown = 60;
                if (this.resendTimer) clearInterval(this.resendTimer);
                this.resendTimer = setInterval(() => {
                    this.resendCooldown--;
                    if (this.resendCooldown <= 0) clearInterval(this.resendTimer);
                }, 1000);
            },

            getOtpValue() {
                let code = '';
                for (let i = 0; i < 6; i++) {
                    const el = document.getElementById('otp-' + i);
                    code += el ? el.value : '';
                }
                return code;
            },

            handleOtpInput(e, i) {
                const val = e.target.value.replace(/\D/g, '');
                e.target.value = val.substring(0, 1);
                if (val && i < 5) {
                    document.getElementById('otp-' + (i + 1))?.focus();
                }
            },

            handleOtpBackspace(e, i) {
                if (!e.target.value && i > 0) {
                    document.getElementById('otp-' + (i - 1))?.focus();
                }
            },

            handleOtpPaste(e) {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').substring(0, 6);
                for (let i = 0; i < 6; i++) {
                    const el = document.getElementById('otp-' + i);
                    if (el) el.value = paste[i] || '';
                }
                if (paste.length > 0) {
                    const lastIdx = Math.min(paste.length, 6) - 1;
                    document.getElementById('otp-' + lastIdx)?.focus();
                }
            },

            async verifyOtp() {
                const code = this.getOtpValue();
                if (code.length !== 6) {
                    this.otpError = 'กรุณากรอก OTP 6 หลัก';
                    return;
                }

                this.otpLoading = true;
                this.otpError = '';

                try {
                    const result = await this.confirmationResult.confirm(code);
                    this.firebaseUid = result.user.uid;
                    this.otpVerified = true;
                } catch (err) {
                    console.error('OTP verify error:', err);
                    if (err.code === 'auth/invalid-verification-code') {
                        this.otpError = 'รหัส OTP ไม่ถูกต้อง';
                    } else if (err.code === 'auth/code-expired') {
                        this.otpError = 'OTP หมดอายุ กรุณาส่งใหม่';
                    } else {
                        this.otpError = 'ยืนยัน OTP ไม่สำเร็จ กรุณาลองใหม่';
                    }
                } finally {
                    this.otpLoading = false;
                }
            },

            async submitRegister() {
                this.registerError = '';

                if (!this.name.trim()) { this.registerError = 'กรุณากรอกชื่อ-นามสกุล'; return; }
                if (this.password.length < 6) { this.registerError = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร'; return; }
                if (this.password !== this.passwordConfirm) { this.registerError = 'รหัสผ่านไม่ตรงกัน'; return; }

                this.registerLoading = true;

                try {
                    const res = await fetch('/register', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            name: this.name,
                            phone: this.phone,
                            password: this.password,
                            password_confirmation: this.passwordConfirm,
                            firebase_uid: this.firebaseUid,
                            referral_code: this.referralCode,
                        }),
                    });

                    const data = await res.json();

                    if (data.success) {
                        window.location.href = data.redirect || '/';
                    } else {
                        this.registerError = data.message || 'เกิดข้อผิดพลาด';
                    }
                } catch (err) {
                    this.registerError = 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้';
                } finally {
                    this.registerLoading = false;
                }
            },
        }
    }
    </script>

    {{-- Invisible reCAPTCHA container --}}
    <div id="recaptcha-container"></div>
</body>
</html>
