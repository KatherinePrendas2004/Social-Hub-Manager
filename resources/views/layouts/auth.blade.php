<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Social Hub Manager')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <!-- Logo -->
        <div class="mb-6">
            <a href="/" class="flex items-center space-x-2">
                <div class="p-3 bg-blue-600 rounded-xl">
                    <i data-lucide="share-2" class="w-8 h-8 text-white"></i>
                </div>
                <span class="text-2xl font-bold text-gray-900">Social Hub Manager</span>
            </a>
        </div>

        <!-- Main Content -->
        <div class="w-full sm:max-w-md px-6 py-8 bg-white shadow-xl rounded-2xl">
            @yield('content')
        </div>
        
        <!-- Footer -->
        <div class="mt-6 text-center text-sm text-gray-500">
            © {{ date('Y') }} Social Hub Manager. Todos los derechos reservados.
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
    </script>
    @yield('scripts')
</body>
</html>