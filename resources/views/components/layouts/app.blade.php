<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'test-aido' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow-sm mb-6">
        <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
            <a href="/" class="text-xl font-bold text-gray-800">test-aido</a>
            <a href="/products" class="text-blue-600 hover:text-blue-800">Products</a>
        </div>
    </nav>
    <div class="max-w-7xl mx-auto">
        {{ $slot }}
    </div>
</body>
</html>
