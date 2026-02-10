<!DOCTYPE html>
<html lang="th" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Setup - {{ config('app.name', 'Lotto Platform') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Noto Sans Thai"', 'Inter', 'system-ui', 'sans-serif'] },
                    colors: {
                        brand: { 50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#312e81',950:'#1e1b4b' }
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes float { 0%,100%{transform:translate(0,0) scale(1)} 33%{transform:translate(30px,-50px) scale(1.1)} 66%{transform:translate(-20px,20px) scale(0.9)} }
        @keyframes fadeUp { 0%{opacity:0;transform:translateY(20px)} 100%{opacity:1;transform:translateY(0)} }
        .animate-float { animation: float 7s infinite; }
        .animate-fade-up { animation: fadeUp 0.6s ease-out forwards; }
        .input-glow:focus { box-shadow: 0 0 0 3px rgba(99,102,241,0.15), 0 0 20px rgba(99,102,241,0.1); }
    </style>
</head>
<body class="h-full font-sans flex items-center justify-center p-4" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f0a2e 50%, #1a1145 100%);">
    {{-- Background Blobs --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 rounded-full filter blur-3xl opacity-20 animate-float" style="background: linear-gradient(135deg, #6366f1, #a855f7);"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 rounded-full filter blur-3xl opacity-15 animate-float" style="animation-delay:2s; background: linear-gradient(135deg, #7c3aed, #ec4899);"></div>
    </div>

    <div class="relative z-10 w-full max-w-lg animate-fade-up">
        <div class="rounded-3xl p-8 md:p-10 border border-indigo-500/20 shadow-2xl" style="background: rgba(30,27,75,0.55); backdrop-filter: blur(24px) saturate(1.6);">
            {{-- Logo --}}
            <div class="text-center mb-8">
                <div class="mx-auto w-16 h-16 rounded-2xl flex items-center justify-center shadow-lg mb-4" style="background: linear-gradient(135deg, #6366f1, #a855f7);">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-white">ตั้งค่าแอดมินหลัก</h1>
                <p class="text-indigo-300/60 mt-1.5 text-sm">สร้างบัญชีซูเปอร์แอดมินเพื่อเริ่มใช้งานระบบ</p>
            </div>

            @if($errors->any())
            <div class="mb-6 p-4 rounded-xl border border-red-500/30 flex items-start gap-3" style="background: rgba(239,68,68,0.1);">
                <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <ul class="text-red-300 text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.setup.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-indigo-200 mb-1.5">ชื่อ</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="ชื่อแอดมิน"
                           class="w-full px-4 py-3 rounded-xl text-white placeholder-indigo-400/40 border border-indigo-500/30 text-sm input-glow transition-all focus:border-indigo-400"
                           style="background: rgba(99,102,241,0.08);">
                </div>

                <div>
                    <label class="block text-sm font-medium text-indigo-200 mb-1.5">ชื่อผู้ใช้ (ID)</label>
                    <input type="text" name="username" value="{{ old('username') }}" required placeholder="admin"
                           class="w-full px-4 py-3 rounded-xl text-white placeholder-indigo-400/40 border border-indigo-500/30 text-sm input-glow transition-all focus:border-indigo-400"
                           style="background: rgba(99,102,241,0.08);">
                </div>

                <div>
                    <label class="block text-sm font-medium text-indigo-200 mb-1.5">เบอร์โทรศัพท์ <span class="text-indigo-400/40">(ไม่บังคับ)</span></label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="0812345678"
                           class="w-full px-4 py-3 rounded-xl text-white placeholder-indigo-400/40 border border-indigo-500/30 text-sm input-glow transition-all focus:border-indigo-400"
                           style="background: rgba(99,102,241,0.08);">
                </div>

                <div>
                    <label class="block text-sm font-medium text-indigo-200 mb-1.5">อีเมล <span class="text-indigo-400/40">(ไม่บังคับ)</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="admin@example.com"
                           class="w-full px-4 py-3 rounded-xl text-white placeholder-indigo-400/40 border border-indigo-500/30 text-sm input-glow transition-all focus:border-indigo-400"
                           style="background: rgba(99,102,241,0.08);">
                </div>

                <div>
                    <label class="block text-sm font-medium text-indigo-200 mb-1.5">รหัสผ่าน</label>
                    <input type="password" name="password" required placeholder="อย่างน้อย 8 ตัวอักษร"
                           class="w-full px-4 py-3 rounded-xl text-white placeholder-indigo-400/40 border border-indigo-500/30 text-sm input-glow transition-all focus:border-indigo-400"
                           style="background: rgba(99,102,241,0.08);">
                </div>

                <div>
                    <label class="block text-sm font-medium text-indigo-200 mb-1.5">ยืนยันรหัสผ่าน</label>
                    <input type="password" name="password_confirmation" required placeholder="กรอกรหัสผ่านอีกครั้ง"
                           class="w-full px-4 py-3 rounded-xl text-white placeholder-indigo-400/40 border border-indigo-500/30 text-sm input-glow transition-all focus:border-indigo-400"
                           style="background: rgba(99,102,241,0.08);">
                </div>

                <button type="submit"
                        class="w-full py-3.5 text-white font-semibold rounded-xl text-sm transition-all hover:shadow-lg hover:shadow-indigo-500/25 hover:-translate-y-0.5 active:translate-y-0 mt-6"
                        style="background: linear-gradient(135deg, #6366f1, #4f46e5);">
                    สร้างบัญชีแอดมิน
                </button>
            </form>
        </div>

        <p class="text-center text-indigo-400/40 text-xs mt-5">
            หน้านี้จะแสดงเฉพาะเมื่อยังไม่มีแอดมินในระบบเท่านั้น
        </p>
    </div>
</body>
</html>
