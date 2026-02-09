<!DOCTYPE html>
<html lang="th" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login - {{ config('app.name', 'Lotto Platform') }}</title>
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
                <h1 class="text-2xl font-bold text-white">Admin Panel</h1>
                <p class="text-gray-400 mt-1 text-sm">Lotto Platform</p>
            </div>

            @if($errors->any())
            <div class="mb-4 p-3 bg-red-900/50 border border-red-700 text-red-300 rounded-lg text-sm">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-4">
                    <label for="phone" class="block text-sm font-medium text-gray-300 mb-1">Phone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required autofocus
                           placeholder="0812345678"
                           class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Password</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                </div>

                <div class="flex items-center mb-6">
                    <input type="checkbox" id="remember" name="remember"
                           class="w-4 h-4 rounded bg-gray-700 border-gray-600 text-brand-500 focus:ring-brand-500">
                    <label for="remember" class="ml-2 text-sm text-gray-400">Remember me</label>
                </div>

                <button type="submit"
                        class="w-full py-3 bg-brand-600 hover:bg-brand-700 text-white font-medium rounded-lg
                               transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 focus:ring-offset-gray-800">
                    Login
                </button>
            </form>
        </div>
    </div>
</body>
</html>
