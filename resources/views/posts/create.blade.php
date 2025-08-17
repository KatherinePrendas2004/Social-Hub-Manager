@extends('layouts.app')

@section('title', 'Nueva Publicación')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">Nueva Publicación</h2>
                <p class="text-gray-600 mt-1">Comparte contenido en tus redes sociales</p>
            </div>
            <a href="{{ route('posts.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Volver
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="p-4 bg-red-100 border border-red-200 text-red-800 rounded-lg mb-6">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Content Section -->
            <div class="p-6 border-b border-gray-100">
                <label class="block text-sm font-semibold text-gray-900 mb-3">Contenido de la publicación</label>
                <div class="relative">
                    <textarea name="content" 
                              rows="6" 
                              class="w-full p-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none @error('content') border-red-300 @enderror"
                              placeholder="¿Qué quieres compartir hoy?">{{ old('content') }}</textarea>
                    <div class="absolute bottom-3 right-3 text-xs text-gray-400">
                        <span id="char-count">0</span>/10000
                    </div>
                </div>
                @error('content')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Title Section for Reddit -->
            <div id="reddit-title-section" class="p-6 border-b border-gray-100 hidden">
                <label class="block text-sm font-semibold text-gray-900 mb-3">Título para Reddit</label>
                <input type="text" 
                       name="reddit_title" 
                       class="w-full p-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('reddit_title') border-red-300 @enderror"
                       placeholder="Ingresa el título para tu publicación en Reddit"
                       value="{{ old('reddit_title') }}">
                @error('reddit_title')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Platform Selection -->
            <div class="p-6 border-b border-gray-100">
                <label class="block text-sm font-semibold text-gray-900 mb-4">Selecciona las redes sociales</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Twitter -->
                    @if(in_array('twitter', $connectedAccounts))
                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-300 hover:bg-blue-50 transition-all duration-200 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                            <input type="checkbox" 
                                   name="platforms[]" 
                                   value="twitter" 
                                   class="hidden peer"
                                   {{ in_array('twitter', old('platforms', [])) ? 'checked' : '' }}>
                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3 peer-checked:bg-blue-500 peer-checked:text-white transition-colors">
                                <i data-lucide="twitter" class="w-5 h-5 text-blue-600 peer-checked:text-white"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">Twitter</p>
                                <p class="text-sm text-green-600">✓ Conectado</p>
                                <p class="text-xs text-gray-500 mt-1">Se publicará como tweet</p>
                            </div>
                            <div class="ml-auto">
                                <div class="w-5 h-5 border-2 border-gray-300 rounded peer-checked:border-blue-500 peer-checked:bg-blue-500 flex items-center justify-center">
                                    <i data-lucide="check" class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100"></i>
                                </div>
                            </div>
                        </label>
                    @else
                        <div class="flex items-center p-4 border-2 border-gray-100 rounded-xl bg-gray-50 opacity-50">
                            <div class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center mr-3">
                                <i data-lucide="twitter" class="w-5 h-5 text-gray-400"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-600">Twitter</p>
                                <p class="text-sm text-gray-500">No conectado</p>
                            </div>
                        </div>
                    @endif

                    <!-- LinkedIn -->
                    @if(in_array('linkedin', $connectedAccounts))
                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-indigo-300 hover:bg-indigo-50 transition-all duration-200 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50">
                            <input type="checkbox" 
                                   name="platforms[]" 
                                   value="linkedin" 
                                   class="hidden peer"
                                   {{ in_array('linkedin', old('platforms', [])) ? 'checked' : '' }}>
                            <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center mr-3 peer-checked:bg-indigo-500 peer-checked:text-white transition-colors">
                                <i data-lucide="linkedin" class="w-5 h-5 text-indigo-600 peer-checked:text-white"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">LinkedIn</p>
                                <p class="text-sm text-green-600">✓ Conectado</p>
                                <p class="text-xs text-gray-500 mt-1">Se publicará en tu perfil profesional</p>
                            </div>
                            <div class="ml-auto">
                                <div class="w-5 h-5 border-2 border-gray-300 rounded peer-checked:border-indigo-500 peer-checked:bg-indigo-500 flex items-center justify-center">
                                    <i data-lucide="check" class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100"></i>
                                </div>
                            </div>
                        </label>
                    @else
                        <div class="flex items-center p-4 border-2 border-gray-100 rounded-xl bg-gray-50 opacity-50">
                            <div class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center mr-3">
                                <i data-lucide="linkedin" class="w-5 h-5 text-gray-400"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-600">LinkedIn</p>
                                <p class="text-sm text-gray-500">No conectado</p>
                            </div>
                        </div>
                    @endif

                    <!-- Reddit -->
                    @if(in_array('reddit', $connectedAccounts))
                        <label class="flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-orange-300 hover:bg-orange-50 transition-all duration-200 has-[:checked]:border-orange-500 has-[:checked]:bg-orange-50">
                            <input type="checkbox" 
                                   name="platforms[]" 
                                   value="reddit" 
                                   class="hidden peer reddit-checkbox"
                                   {{ in_array('reddit', old('platforms', [])) ? 'checked' : '' }}>
                            <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center mr-3 peer-checked:bg-orange-500 peer-checked:text-white transition-colors">
                                <i data-lucide="disc" class="w-5 h-5 text-orange-600 peer-checked:text-white"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">Reddit</p>
                                <p class="text-sm text-green-600">✓ Conectado</p>
                                <p class="text-xs text-gray-500 mt-1">Se publicará en tu perfil de Reddit</p>
                            </div>
                            <div class="ml-auto">
                                <div class="w-5 h-5 border-2 border-gray-300 rounded peer-checked:border-orange-500 peer-checked:bg-orange-500 flex items-center justify-center">
                                    <i data-lucide="check" class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100"></i>
                                </div>
                            </div>
                        </label>
                    @else
                        <div class="flex items-center p-4 border-2 border-gray-100 rounded-xl bg-gray-50 opacity-50">
                            <div class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center mr-3">
                                <i data-lucide="disc" class="w-5 h-5 text-gray-400"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-600">Reddit</p>
                                <p class="text-sm text-gray-500">No conectado</p>
                            </div>
                        </div>
                    @endif
                </div>
                
                @error('platforms')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Publication Type -->
            <div class="p-6">
                <label class="block text-sm font-semibold text-gray-900 mb-4">Tipo de publicación</label>
                <div class="space-y-3">
                    <!-- Instant -->
                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-green-300 hover:bg-green-50 transition-all has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                        <input type="radio" name="type" value="instant" class="hidden peer publication-type" checked>
                        <div class="w-5 h-5 border-2 border-gray-300 rounded-full mr-4 peer-checked:border-green-500 peer-checked:bg-green-500 flex items-center justify-center">
                            <div class="w-2.5 h-2.5 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center">
                                <i data-lucide="zap" class="w-5 h-5 text-green-600 mr-2"></i>
                                <span class="font-medium text-gray-900">Publicar ahora</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">La publicación se enviará inmediatamente a las redes seleccionadas</p>
                        </div>
                    </label>

                    <!-- Queued -->
                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-blue-300 hover:bg-blue-50 transition-all has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                        <input type="radio" name="type" value="queued" class="hidden peer publication-type">
                        <div class="w-5 h-5 border-2 border-gray-300 rounded-full mr-4 peer-checked:border-blue-500 peer-checked:bg-blue-500 flex items-center justify-center">
                            <div class="w-2.5 h-2.5 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center">
                                <i data-lucide="clock" class="w-5 h-5 text-blue-600 mr-2"></i>
                                <span class="font-medium text-gray-900">Enviar a cola</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">Se publicará en el siguiente horario programado</p>
                        </div>
                    </label>

                    <!-- Scheduled -->
                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-indigo-300 hover:bg-indigo-50 transition-all has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50">
                        <input type="radio" name="type" value="scheduled" class="hidden peer publication-type">
                        <div class="w-5 h-5 border-2 border-gray-300 rounded-full mr-4 peer-checked:border-indigo-500 peer-checked:bg-indigo-500 flex items-center justify-center">
                            <div class="w-2.5 h-2.5 bg-white rounded-full opacity-0 peer-checked:opacity-100"></div>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center">
                                <i data-lucide="calendar" class="w-5 h-5 text-indigo-600 mr-2"></i>
                                <span class="font-medium text-gray-900">Programar</span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">Programa la publicación para una fecha y hora específicas</p>
                        </div>
                    </label>
                </div>
                @error('type')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Scheduled Date/Time Section (Hidden by default) -->
            <div id="scheduled-section" class="p-6 border-b border-gray-100 hidden">
                <label class="block text-sm font-semibold text-gray-900 mb-3">Fecha y Hora de Publicación</label>
                <input type="datetime-local" 
                       name="scheduled_at" 
                       class="w-full p-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent @error('scheduled_at') border-red-300 @enderror"
                       value="{{ old('scheduled_at') }}" 
                       min="{{ now()->format('Y-m-d\TH:i') }}" /> <!-- Mínimo: ahora para evitar pasado -->
                @error('scheduled_at')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <button type="submit" 
                    class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl hover:from-blue-600 hover:to-purple-700 transition-all transform hover:scale-105 shadow-lg">
                Crear Publicación
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Lucide icons
        lucide.createIcons();

        // Character count for content textarea
        const contentTextarea = document.querySelector('textarea[name="content"]');
        const charCount = document.getElementById('char-count');
        
        contentTextarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });

        // Show/hide Reddit title section based on Reddit checkbox
        const redditCheckbox = document.querySelector('.reddit-checkbox');
        const redditTitleSection = document.getElementById('reddit-title-section');
        
        if (redditCheckbox) {
            redditCheckbox.addEventListener('change', function() {
                redditTitleSection.classList.toggle('hidden', !this.checked);
            });

            // Initialize Reddit title section visibility
            if (redditCheckbox.checked) {
                redditTitleSection.classList.remove('hidden');
            }
        }

        // Show/hide scheduled date/time section based on type
        const publicationTypes = document.querySelectorAll('.publication-type');
        const scheduledSection = document.getElementById('scheduled-section');

        publicationTypes.forEach(type => {
            type.addEventListener('change', function() {
                scheduledSection.classList.toggle('hidden', this.value !== 'scheduled');
            });
        });

        // Initialize visibility if old('type') is scheduled
        if ("{{ old('type') }}" === 'scheduled') {
            scheduledSection.classList.remove('hidden');
        }
    });
</script>
@endsection