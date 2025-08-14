<aside class="fixed left-0 top-0 w-64 h-full glass-white sidebar-glow z-50 flex flex-col">
    <!-- Logo Section Compacto -->
    <div class="p-4 border-b border-gray-200/50 flex-shrink-0">
        <div class="flex items-center space-x-3">
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500 via-purple-500 to-indigo-600 rounded-xl blur-md opacity-60 group-hover:opacity-80 transition-opacity"></div>
                <div class="relative w-10 h-10 bg-gradient-to-r from-blue-500 via-purple-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg transform group-hover:scale-105 transition-transform">
                    <i data-lucide="share-2" class="w-5 h-5 text-white"></i>
                </div>
                <!-- Indicador de estado -->
                <div class="absolute -top-0.5 -right-0.5 w-3 h-3 bg-emerald-400 rounded-full border-2 border-white shadow-sm">
                    <div class="w-full h-full bg-emerald-400 rounded-full animate-ping"></div>
                </div>
            </div>
            <div>
                <h1 class="text-lg font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                    Social Hub
                </h1>
                <p class="text-xs text-gray-500 font-medium">Manager Pro</p>
            </div>
        </div>
    </div>

    <!-- Navigation con Scroll -->
    <nav class="px-3 py-4 space-y-2 flex-1 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent">
        <a href="{{ route('dashboard.index') }}" 
           class="group flex items-center space-x-3 px-4 py-3 rounded-xl bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-md transform hover:scale-[1.02] transition-all duration-300">
            <div class="p-1.5 bg-white/20 rounded-lg group-hover:bg-white/30 transition-colors">
                <i data-lucide="home" class="w-4 h-4"></i>
            </div>
            <span class="font-medium text-sm">Dashboard</span>
            <div class="ml-auto">
                <div class="w-1.5 h-1.5 bg-white/70 rounded-full"></div>
            </div>
        </a>
        
        <a href="{{ route('social.index') }}" 
           class="group flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-gradient-to-r hover:from-gray-50 hover:to-blue-50 hover:text-blue-700 transition-all duration-300 hover:shadow-sm">
            <div class="p-1.5 bg-gray-100 rounded-lg group-hover:bg-blue-100 transition-colors">
                <i data-lucide="network" class="w-4 h-4 group-hover:text-blue-600 transition-colors"></i>
            </div>
            <span class="font-medium text-sm">Redes Sociales</span>
        </a>
        
        <a href="{{ route('posts.create') }}" 
        class="group flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-gradient-to-r hover:from-gray-50 hover:to-purple-50 hover:text-purple-700 transition-all duration-300 hover:shadow-sm">
            <div class="p-1.5 bg-gray-100 rounded-lg group-hover:bg-purple-100 transition-colors">
                <i data-lucide="edit" class="w-4 h-4 group-hover:text-purple-600 transition-colors"></i>
            </div>
            <span class="font-medium text-sm">Publicaciones</span>
        </a>
        
        <a href="#" 
           class="group flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-gradient-to-r hover:from-gray-50 hover:to-indigo-50 hover:text-indigo-700 transition-all duration-300 hover:shadow-sm">
            <div class="p-1.5 bg-gray-100 rounded-lg group-hover:bg-indigo-100 transition-colors">
                <i data-lucide="calendar" class="w-4 h-4 group-hover:text-indigo-600 transition-colors"></i>
            </div>
            <span class="font-medium text-sm">Horarios</span>
        </a>
        
        <a href="#" 
           class="group flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-gradient-to-r hover:from-gray-50 hover:to-emerald-50 hover:text-emerald-700 transition-all duration-300 hover:shadow-sm">
            <div class="p-1.5 bg-gray-100 rounded-lg group-hover:bg-emerald-100 transition-colors">
                <i data-lucide="list" class="w-4 h-4 group-hover:text-emerald-600 transition-colors"></i>
            </div>
            <span class="font-medium text-sm">Cola de Publicaciones</span>
        </a>
        
        <a href="{{ route('two-factor.show') }}" 
           class="group flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-gradient-to-r hover:from-gray-50 hover:to-amber-50 hover:text-amber-700 transition-all duration-300 hover:shadow-sm">
            <div class="p-1.5 bg-gray-100 rounded-lg group-hover:bg-amber-100 transition-colors">
                <i data-lucide="shield-check" class="w-4 h-4 group-hover:text-amber-600 transition-colors"></i>
            </div>
            <span class="font-medium text-sm">Seguridad</span>
        </a>
        
    </nav>

    <!-- Usuario y Logout Fijo -->
    <div class="p-4 border-t border-gray-200/50 flex-shrink-0">
        <!-- Info del usuario compacta -->
        <div class="mb-3 p-3 bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-xs">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</span>
                </div>
                <div>
                    <p class="font-semibold text-gray-800 text-xs">{{ Auth::user()->name ?? 'Usuario' }}</p>
                    <p class="text-xs text-gray-500">En línea</p>
                </div>
            </div>
        </div>
        
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" 
                    class="group w-full flex items-center space-x-2 px-3 py-2.5 rounded-xl text-red-600 hover:bg-red-50 hover:text-red-700 transition-all duration-300 hover:shadow-sm">
                <div class="p-1.5 bg-red-50 rounded-lg group-hover:bg-red-100 transition-colors">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </div>
                <span class="font-medium text-sm">Cerrar Sesión</span>
            </button>
        </form>
    </div>
</aside>