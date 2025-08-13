@extends('layouts.auth')

@section('title', 'Registrarse - Social Hub Manager')

@section('content')
<div class="text-center mb-6">
    <h2 class="text-3xl font-bold text-gray-900">Crear Cuenta</h2>
    <p class="text-gray-600 mt-2">Únete y gestiona todas tus redes sociales</p>
</div>

<form id="registerForm" class="space-y-6">
    @csrf
    
    <!-- Name -->
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
            Nombre Completo
        </label>
        <div class="relative">
            <input type="text" 
                   id="name" 
                   name="name" 
                   required 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent pl-11"
                   placeholder="Tu nombre completo">
            <div class="absolute left-3 top-3.5">
                <i data-lucide="user" class="w-5 h-5 text-gray-400"></i>
            </div>
        </div>
    </div>

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

    <!-- Confirm Password -->
    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
            Confirmar Contraseña
        </label>
        <div class="relative">
            <input type="password" 
                   id="password_confirmation" 
                   name="password_confirmation" 
                   required 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent pl-11"
                   placeholder="••••••••">
            <div class="absolute left-3 top-3.5">
                <i data-lucide="lock" class="w-5 h-5 text-gray-400"></i>
            </div>
        </div>
    </div>

    <!-- Submit Button -->
    <button type="submit" 
            id="submitBtn"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center space-x-2">
        <span id="submitText">Crear Cuenta</span>
        <div id="submitLoader" class="hidden animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
    </button>
</form>

<!-- Login Link -->
<div class="mt-6 text-center">
    <p class="text-gray-600">
        ¿Ya tienes una cuenta? 
        <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
            Inicia sesión aquí
        </a>
    </p>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitLoader = document.getElementById('submitLoader');
    
    // Show loading state
    submitBtn.disabled = true;
    submitText.textContent = 'Creando...';
    submitLoader.classList.remove('hidden');
    
    const formData = new FormData(this);
    
    fetch('{{ route('register') }}', {
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
                title: '¡Cuenta Creada!',
                text: 'Tu cuenta ha sido creada exitosamente',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.href = data.redirect;
            });
        } else {
            let errorMessage = 'Error al crear la cuenta';
            if (data.errors) {
                errorMessage = Object.values(data.errors).flat().join(', ');
            } else if (data.message) {
                errorMessage = data.message;
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage
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
        submitText.textContent = 'Crear Cuenta';
        submitLoader.classList.add('hidden');
    });
});
</script>
@endsection