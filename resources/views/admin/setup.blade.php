<!DOCTYPE html>
<html lang="th" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Setup - {{ config('app.name', 'Lotto Platform') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 500:'#3b82f6', 600:'#2563eb', 700:'#1d4ed8' }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full bg-gray-900 flex items-center justify-center">
    <div class="w-full max-w-md px-6">
        <div class="bg-gray-800 rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-white">ตั้งค่าแอดมินหลัก</h1>
                <p class="text-gray-400 mt-1 text-sm">สร้างบัญชีซูเปอร์แอดมินเพื่อเริ่มใช้งานระบบ</p>
            </div>

            @if($errors->any())
            <div class="mb-4 p-3 bg-red-900/50 border border-red-700 text-red-300 rounded-lg text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.setup.store') }}">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-1">ชื่อ</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                           placeholder="ชื่อแอดมิน"
                           class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label for="phone" class="block text-sm font-medium text-gray-300 mb-1">เบอร์โทรศัพท์</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required
                           placeholder="0812345678"
                           class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-1">อีเมล <span class="text-gray-500">(ไม่บังคับ)</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           placeholder="admin@example.com"
                           class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-1">รหัสผ่าน</label>
                    <input type="password" id="password" name="password" required
                           placeholder="อย่างน้อย 8 ตัวอักษร"
                           class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>

                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-1">ยืนยันรหัสผ่าน</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           placeholder="กรอกรหัสผ่านอีกครั้ง"
                           class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>

                <button type="submit"
                        class="w-full py-3 bg-brand-600 hover:bg-brand-700 text-white font-medium rounded-lg
                               transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 focus:ring-offset-gray-800">
                    สร้างบัญชีแอดมิน
                </button>
            </form>
        </div>

        <p class="text-center text-gray-500 text-xs mt-4">
            หน้านี้จะแสดงเฉพาะเมื่อยังไม่มีแอดมินในระบบเท่านั้น
        </p>
    </div>
</body>
</html>
