@extends('layouts.app')

@section('title', 'Conectar Redes Sociales')

@section('content')
<div class="max-w-6xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Conecta tus Redes Sociales</h2>

    @if(session('success'))
        <div class="p-3 bg-emerald-100 text-emerald-800 rounded mb-4">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-3 bg-red-100 text-red-800 rounded mb-4">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @php
            $providers = [
                'twitter' => ['name'=>'Twitter','icon'=>'twitter'],
                'linkedin' => ['name'=>'LinkedIn','icon'=>'linkedin'],
                'reddit' => ['name'=>'Reddit','icon'=>'disc'] // lucide no tiene reddit; usa 'disc' o un SVG
            ];
        @endphp

        @foreach($providers as $key => $p)
        @php
            $connected = $accounts->firstWhere('platform', $key);
        @endphp
        <div class="bg-white rounded-xl p-6 shadow-md flex flex-col items-center">
            <div class="w-16 h-16 flex items-center justify-center rounded-full bg-gradient-to-r from-gray-100 to-gray-50 mb-4">
                <i data-lucide="{{ $p['icon'] }}" class="w-8 h-8 text-blue-600"></i>
            </div>

            <h3 class="text-lg font-semibold">{{ $p['name'] }}</h3>

            @if($connected && $connected->is_active)
                <p class="text-sm text-gray-500 mt-2">Conectado como <strong>{{ $connected->platform_username ?? '—' }}</strong></p>
                <form action="{{ route('social.disconnect', $key) }}" method="POST" class="mt-4 disconnect-form">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-lg bg-red-50 text-red-600 hover:bg-red-100">Desconectar</button>
                </form>
            @else
                <a href="{{ route('social.redirect', $key) }}" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                    Conectar
                </a>
            @endif
        </div>
        @endforeach
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('.disconnect-form');

    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // evitar submit inmediato
            
            Swal.fire({
                title: '¿Seguro que quieres desconectar esta red social?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, desconectar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
@endsection
