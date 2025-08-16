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
                                   class="hidden peer"
                                   {{ in_array('reddit', old('platforms', [])) ? 'checked' : '' }}>
                            <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center mr-3 peer-checked:bg-orange-500 peer-checked:text-white transition-colors">
                                <i data-lucide="disc" class="w-5 h-5 text-orange-600 peer-checked:text-white"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">Reddit</p>
                                <p class="text-sm text-green-600">✓ Conectado</p>
                                <p class="text-xs text-gray-500 mt-1">Se publicará en tu primer subreddit disponible</p>
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

                <!-- Platform Information -->
                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h4 class="text-sm font-semibold text-blue-900 mb-2 flex items-center">
                        <i data-lucide="info" class="w-4 h-4 mr-2"></i>
                        Información sobre las plataformas
                    </h4>
                    <div class="space-y-2 text-xs text-blue-800">
                        <p><strong>Twitter:</strong> Tu contenido se publicará como tweet.</p>
                        <p><strong>LinkedIn:</strong> Se publicará en tu perfil profesional.</p>
                        <p><strong>Reddit:</strong> Se creará un post de texto en uno de tus subreddits. El título se generará automáticamente basado en tu contenido.</p>
                    </div>
                </div>
            </div>

            <!-- Publication Type -->
            <div class="p-6">
                <label class="block text-sm font-semibold text-gray-900 mb-4">Tipo de publicación</label>
                <div class="space-y-3">
                    <!-- Instant -->
                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-green-300 hover:bg-green-50 transition-all has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                        <input type="radio" name="type" value="instant" class="hidden peer" checked>
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

                    <!-- Queued (Disabled) -->
                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-xl opacity-50 cursor-not-allowed">
                        <input type="radio" name="type" value="queued" class="hidden peer" disabled>
                        <div class="w-5 h-5 border-2 border-gray-300 rounded-full mr-4">
                            <div class="w-2.5 h-2.5 bg-white rounded-full opacity-0"></div>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center">
                                <i data-lucide="clock" class="w-5 h-5 text-gray-400 mr-2"></i>
                                <span class="font-medium text-gray-600">Enviar a cola</span>
                                <span class="ml-2 px-2 py-1 bg-gray-200 text-gray-600 text-xs rounded-full">Próximamente</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Se publicará en el siguiente horario programado</p>
                        </div>
                    </label>

                    <!-- Scheduled (Disabled) -->
                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-xl opacity-50 cursor-not-allowed">
                        <input type="radio" name="type" value="scheduled" class="hidden peer" disabled>
                        <div class="w-5 h-5 border-2 border-gray-300 rounded-full mr-4">
                            <div class="w-2.5 h-2.5 bg-white rounded-full opacity-0"></div>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center">
                                <i data-lucide="calendar" class="w-5 h-5 text-gray-400 mr-2"></i>
                                <span class="font-medium text-gray-600">Programar</span>
                                <span class="ml-2 px-2 py-1 bg-gray-200 text-gray-600 text-xs rounded-full">Próximamente</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Selecciona una fecha y hora específica</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between">
            <button type="button" 
                    onclick="history.back()" 
                    class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-medium transition-colors">
                Cancelar
            </button>
            <button type="submit" 
                    class="px-8 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white rounded-xl font-medium transition-all transform hover:scale-105 shadow-lg">
                <i data-lucide="send" class="w-5 h-5 mr-2 inline"></i>
                Publicar Ahora
            </button>
        </div>
    </form>
</div>

<script>
// Character counter
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.querySelector('textarea[name="content"]');
    const charCount = document.getElementById('char-count');
    
    function updateCharCount() {
        const count = textarea.value.length;
        charCount.textContent = count;
        
        if (count > 10000) {
            charCount.className = 'text-red-500';
        } else if (count > 9000) {
            charCount.className = 'text-yellow-500';
        } else {
            charCount.className = 'text-gray-400';
        }
    }
    
    textarea.addEventListener('input', updateCharCount);
    updateCharCount();
});
</script>
@endsection