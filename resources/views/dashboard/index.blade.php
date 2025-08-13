@extends('layouts.app')

@section('title', 'Dashboard - Social Hub Manager')

@section('content')
<div class="space-y-8">
    <!-- Header de Bienvenida Ultra Moderno -->
    <div class="text-center mb-12">
        <div class="relative inline-block">
            <h1 class="text-5xl font-black bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 bg-clip-text text-transparent mb-4">
                ¡Bienvenido, {{ $user->name }}!
            </h1>
            <div class="absolute -top-2 -right-2 w-4 h-4 bg-emerald-400 rounded-full animate-pulse"></div>
        </div>
        
        <div class="max-w-2xl mx-auto space-y-2">
            <p class="text-gray-600 text-lg font-medium">
                Último acceso: 
                <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent font-bold">
                    {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('d/m/Y H:i') : 'Primera vez' }}
                </span>
            </p>
            <p class="text-gray-500">
                IP: <span class="font-mono bg-gray-100 px-2 py-1 rounded-lg text-sm">{{ $user->last_login_ip ?? 'N/A' }}</span>
            </p>
        </div>
    </div>

    <!-- Stats Rápidas -->
    <div class="grid grid-cols-4 gap-6 mb-12">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Publicaciones</p>
                    <p class="text-3xl font-black">127</p>
                </div>
                <i data-lucide="edit-3" class="w-8 h-8 text-blue-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-2xl p-6 text-white shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-emerald-100 text-sm font-medium">Seguidores</p>
                    <p class="text-3xl font-black">2.4K</p>
                </div>
                <i data-lucide="users" class="w-8 h-8 text-emerald-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-2xl p-6 text-white shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Engagement</p>
                    <p class="text-3xl font-black">94%</p>
                </div>
                <i data-lucide="heart" class="w-8 h-8 text-purple-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-amber-500 to-amber-600 rounded-2xl p-6 text-white shadow-xl">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-amber-100 text-sm font-medium">Redes</p>
                    <p class="text-3xl font-black">5</p>
                </div>
                <i data-lucide="network" class="w-8 h-8 text-amber-200"></i>
            </div>
        </div>
    </div>

    <!-- Tarjetas Principales Mejoradas -->
    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Gestión de Redes -->
        <div class="group relative overflow-hidden bg-gradient-to-br from-white to-blue-50 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full -translate-y-16 translate-x-16 opacity-10 group-hover:opacity-20 transition-opacity"></div>
            
            <div class="relative p-8">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="p-4 bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl shadow-lg group-hover:scale-110 transition-transform">
                        <i data-lucide="network" class="w-8 h-8 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800">Gestión de Redes</h3>
                        <p class="text-blue-600 font-medium">5 redes conectadas</p>
                    </div>
                </div>
                
                <p class="text-gray-600 mb-8 leading-relaxed">
                    Administra todas tus cuentas sociales desde un solo lugar. Conecta, configura y optimiza tu presencia digital.
                </p>
                
                <div class="space-y-3 mb-8">
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                        <span class="text-sm text-gray-600">Instagram conectado</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                        <span class="text-sm text-gray-600">Twitter activo</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-2 h-2 bg-indigo-500 rounded-full"></div>
                        <span class="text-sm text-gray-600">Facebook sincronizado</span>
                    </div>
                </div>
                
                <a href="#" class="inline-flex items-center space-x-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-6 py-4 rounded-2xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 group-hover:scale-105">
                    <span>Gestionar Redes</span>
                    <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </a>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="group relative overflow-hidden bg-gradient-to-br from-white to-emerald-50 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-full -translate-y-16 translate-x-16 opacity-10 group-hover:opacity-20 transition-opacity"></div>
            
            <div class="relative p-8">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="p-4 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-2xl shadow-lg group-hover:scale-110 transition-transform">
                        <i data-lucide="trending-up" class="w-8 h-8 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800">Estadísticas</h3>
                        <p class="text-emerald-600 font-medium">+15% este mes</p>
                    </div>
                </div>
                
                <p class="text-gray-600 mb-8 leading-relaxed">
                    Analiza el rendimiento de tus publicaciones con métricas detalladas y reportes avanzados.
                </p>
                
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="text-center p-4 bg-white rounded-2xl shadow-sm">
                        <p class="text-2xl font-bold text-emerald-600">2.4K</p>
                        <p class="text-xs text-gray-500">Alcance</p>
                    </div>
                    <div class="text-center p-4 bg-white rounded-2xl shadow-sm">
                        <p class="text-2xl font-bold text-blue-600">94%</p>
                        <p class="text-xs text-gray-500">Engagement</p>
                    </div>
                </div>
                
                <a href="#" class="inline-flex items-center space-x-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white px-6 py-4 rounded-2xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 group-hover:scale-105">
                    <span>Ver Estadísticas</span>
                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                </a>
            </div>
        </div>

        <!-- Configuración -->
        <div class="group relative overflow-hidden bg-gradient-to-br from-white to-purple-50 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full -translate-y-16 translate-x-16 opacity-10 group-hover:opacity-20 transition-opacity"></div>
            
            <div class="relative p-8">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="p-4 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-2xl shadow-lg group-hover:scale-110 transition-transform">
                        <i data-lucide="settings" class="w-8 h-8 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800">Configuración</h3>
                        <p class="text-purple-600 font-medium">Personaliza tu experiencia</p>
                    </div>
                </div>
                
                <p class="text-gray-600 mb-8 leading-relaxed">
                    Actualiza tus datos personales, configura la seguridad y personaliza tu experiencia.
                </p>
                
                <div class="space-y-3 mb-8">
                    <div class="flex items-center justify-between p-3 bg-white rounded-xl shadow-sm">
                        <span class="text-sm text-gray-600">2FA</span>
                        <div class="w-10 h-6 bg-emerald-500 rounded-full flex items-center justify-end px-1">
                            <div class="w-4 h-4 bg-white rounded-full"></div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-white rounded-xl shadow-sm">
                        <span class="text-sm text-gray-600">Notificaciones</span>
                        <div class="w-10 h-6 bg-gray-300 rounded-full flex items-center px-1">
                            <div class="w-4 h-4 bg-white rounded-full"></div>
                        </div>
                    </div>
                </div>
                
                <a href="{{ route('two-factor.show') }}" class="inline-flex items-center space-x-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white px-6 py-4 rounded-2xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 group-hover:scale-105">
                    <span>Configuración</span>
                    <i data-lucide="arrow-right" class="w-5 h-5"></i>
                </a>
            </div>
        </div>
    </div>


    <form action="{{ route('logout') }}" method="POST" class="mt-8">
        @csrf
        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm">
            Cerrar sesión
        </button>
    </form>
</div>
@endsection