<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Lotto Platform') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen flex items-center justify-center">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-gray-800 dark:text-white mb-4">
                {{ config('app.name', 'Lotto Platform') }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Modern Online Lottery Platform
            </p>
        </div>
    </div>
</body>
</html>
