@extends('layouts.auth')

@section('title', 'Iniciar Sesión - Social Hub Manager')

@section('content')
<div class="text-center mb-6">
    <h2 class="text-3xl font-bold text-gray-900">Iniciar Sesión</h2>
    <p class="text-gray-600 mt-2">Accede a tu panel de gestión de redes sociales</p>
</div>

@if(session('message'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-green-700 text-sm">{{ session('message') }}</p>
    </div>
@endif

<form id="loginForm" class="space-y-6">
    @csrf
    
    <!-- Email -->
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
            Correo Electrónico
        </label>
        <div class="relative">
            <input type="email" 
                   id="email" 
                   name="email" 
                   required 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent pl-11"
                   placeholder="tu@email.com">
            <div class="absolute left-3 top-3.5">
                <i data-lucide="mail" class="w-5 h-5 text-gray-400"></i>
            </div>
        </div>
    </div>

    <!-- Password -->
    <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
            Contraseña
        </label>
        <div class="relative">
            <input type="password" 
                   id="password" 
                   name="password" 
                   required 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent pl-11"
                   placeholder="••••••••">
            <div class="absolute left-3 top-3.5">
                <i data-lucide="lock" class="w-5 h-5 text-gray-400"></i>
            </div>
        </div>
    </div>

    <!-- Two Factor Code (Hidden initially) -->
    <div id="twoFactorSection" class="hidden">
        <label for="two_factor_code" class="block text-sm font-medium text-gray-700 mb-2">
            Código de Autenticación (6 dígitos o código de recuperación de 8 caracteres)
        </label>
        <div class="relative">
            <input type="text" 
                   id="two_factor_code" 
                   name="two_factor_code" 
                   maxlength="8"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center text-lg font-mono pl-11"
                   placeholder="000000">
            <div class="absolute left-3 top-3.5">
                <i data-lucide="shield-check" class="w-5 h-5 text-gray-400"></i>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-1">Ingresa el código de tu app autenticadora o un código de recuperación</p>
    </div>

    <!-- Submit Button -->
    <button type="submit" 
            id="submitBtn"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center space-x-2">
        <span id="submitText">Iniciar Sesión</span>
        <div id="submitLoader" class="hidden animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
    </button>
</form>

<!-- Register Link -->
<div class="mt-6 text-center">
    <p class="text-gray-600">
        ¿No tienes una cuenta? 
        <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
            Regístrate aquí
        </a>
    </p>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitLoader = document.getElementById('submitLoader');
    
    // Show loading state
    submitBtn.disabled = true;
    submitText.textContent = 'Iniciando...';
    submitLoader.classList.remove('hidden');
    
    const formData = new FormData(this);
    
    fetch('{{ route('login') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Bienvenido!',
                text: 'Inicio de sesión exitoso',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.href = data.redirect;
            });
        } else if (data.requires_2fa) {
            // Show 2FA section
            document.getElementById('twoFactorSection').classList.remove('hidden');
            submitText.textContent = 'Verificar Código';
            Swal.fire({
                icon: 'info',
                title: 'Autenticación Requerida',
                text: data.message,
                confirmButtonText: 'Entendido'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Ocurrió un error inesperado'
        });
    })
    .finally(() => {
        // Reset loading state
        submitBtn.disabled = false;
        if (!document.getElementById('twoFactorSection').classList.contains('hidden')) {
            submitText.textContent = 'Verificar Código';
        } else {
            submitText.textContent = 'Iniciar Sesión';
        }
        submitLoader.classList.add('hidden');
    });
});
</script>
@endsection