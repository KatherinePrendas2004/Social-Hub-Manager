@extends('layouts.app')

@section('title', 'Mis Publicaciones')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Mis Publicaciones</h2>
            <p class="text-gray-600 mt-1">Gestiona y revisa tus publicaciones en redes sociales</p>
        </div>
        <a href="{{ route('posts.create') }}" 
           class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white rounded-xl font-medium transition-all transform hover:scale-105 shadow-lg">
            <i data-lucide="plus" class="w-5 h-5 mr-2"></i>
            Nueva Publicación
        </a>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="p-4 bg-green-100 border border-green-200 text-green-800 rounded-lg mb-6 flex items-center">
            <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-red-100 border border-red-200 text-red-800 rounded-lg mb-6 flex items-center">
            <i data-lucide="alert-circle" class="w-5 h-5 mr-2"></i>
            {{ session('error') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="p-4 bg-yellow-100 border border-yellow-200 text-yellow-800 rounded-lg mb-6 flex items-center">
            <i data-lucide="alert-triangle" class="w-5 h-5 mr-2"></i>
            {{ session('warning') }}
        </div>
    @endif

    @if(session('info'))
        <div class="p-4 bg-blue-100 border border-blue-200 text-blue-800 rounded-lg mb-6 flex items-center">
            <i data-lucide="info" class="w-5 h-5 mr-2"></i>
            {{ session('info') }}
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <i data-lucide="check-circle" class="w-6 h-6 text-green-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900">{{ Auth::user()->posts()->where('status', 'published')->count() }}</p>
                    <p class="text-sm text-gray-600">Publicadas</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i data-lucide="clock" class="w-6 h-6 text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900">{{ Auth::user()->posts()->whereIn('status', ['pending', 'publishing'])->count() }}</p>
                    <p class="text-sm text-gray-600">Pendientes</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-lg">
                    <i data-lucide="x-circle" class="w-6 h-6 text-red-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900">{{ Auth::user()->posts()->where('status', 'failed')->count() }}</p>
                    <p class="text-sm text-gray-600">Fallidas</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center">
                <div class="p-3 bg-gray-100 rounded-lg">
                    <i data-lucide="file-text" class="w-6 h-6 text-gray-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900">{{ Auth::user()->posts()->count() }}</p>
                    <p class="text-sm text-gray-600">Total</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection