@extends('layouts.app')

@section('title', 'Configuración de Seguridad - Social Hub Manager')
@section('page-title', 'Configuración de Seguridad')
@section('subtitle', 'Gestiona la autenticación de dos factores')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 max-w-4xl">
        <!-- 2FA Management -->
        <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6 flex-col sm:flex-row">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i data-lucide="shield" class="w-6 h-6 text-blue-600"></i>
                </div>
                <div class="text-center sm:text-left">
                    <h2 class="text-2xl font-bold text-gray-900">Autenticación de Dos Factores</h2>
                    <p class="text-gray-600">Protege tu cuenta con una capa adicional de seguridad</p>
                </div>
            </div>

            <!-- Status Card -->
            <div class="mb-8 p-4 sm:p-6 {{ Auth::user()->two_factor_enabled ? 'bg-green-50 border border-green-200' : 'bg-yellow-50 border border-yellow-200' }} rounded-xl">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        @if(Auth::user()->two_factor_enabled)
                            <div class="p-2 bg-green-100 rounded-lg">
                                <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-green-800">2FA Activado</h3>
                                <p class="text-green-700">Tu cuenta está protegida con autenticación de dos factores</p>
                            </div>
                        @else
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <i data-lucide="alert-triangle" class="w-5 h-5 text-yellow-600"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-yellow-800">2FA Desactivado</h3>
                                <p class="text-yellow-700">Se recomienda activar la autenticación de dos factores</p>
                            </div>
                        @endif
                    </div>
                    <button id="toggleTwoFactor" class="w-full sm:w-auto px-6 py-3 rounded-lg font-semibold transition-all duration-200 {{ Auth::user()->two_factor_enabled ? 'bg-red-500 hover:bg-red-600 text-white' : 'bg-blue-500 hover:bg-blue-600 text-white' }}">
                        {{ Auth::user()->two_factor_enabled ? 'Desactivar 2FA' : 'Activar 2FA' }}
                    </button>
                </div>
            </div>

            <!-- QR Code Section (Hidden by default) -->
            <div id="qrSection" class="hidden mb-8 p-4 sm:p-6 bg-blue-50 border border-blue-200 rounded-xl">
                <h3 class="text-lg font-semibold text-blue-900 mb-4">Configurar Google Authenticator</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium text-blue-800 mb-2">1. Escanea el código QR</h4>
                        <div id="qrCodeContainer" class="bg-white p-4 rounded-lg text-center">
                            <img id="qrCodeImage" src="" alt="QR Code" class="mx-auto mb-2 max-w-full" style="display: none;">
                            <p class="text-sm text-gray-600">El código QR aparecerá aquí</p>
                        </div>
                        <div class="mt-3 p-3 bg-blue-100 rounded-lg">
                            <p class="text-sm text-blue-700 mb-2">O ingresa manualmente este código:</p>
                            <code id="secretCode" class="bg-white px-2 py-1 rounded text-sm font-mono select-all"></code>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-medium text-blue-800 mb-2">2. Ingresa el código de verificación</h4>
                        <input type="text" id="verificationCode" placeholder="000000" maxlength="6" class="w-full px-4 py-3 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-center text-lg font-mono">
                        <button id="verifyCode" class="w-full mt-3 bg-green-500 hover:bg-green-600 text-white py-3 rounded-lg font-semibold transition-colors duration-200">
                            Verificar y Activar
                        </button>
                        <div class="mt-3 p-3 bg-gray-100 rounded-lg">
                            <p class="text-xs text-gray-600">
                                <strong>Aplicaciones recomendadas:</strong><br>
                                • Google Authenticator<br>
                                • Microsoft Authenticator<br>
                                • Authy
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            @if(Auth::user()->two_factor_enabled)
                <!-- Recovery Codes Section -->
                <div class="p-4 sm:p-6 bg-gray-50 border border-gray-200 rounded-xl">
                    <div class="flex flex-col sm:flex-row items-center justify-between mb-4 gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Códigos de Recuperación</h3>
                            <p class="text-sm text-gray-600">Usa estos códigos si pierdes acceso a tu dispositivo</p>
                        </div>
                        <button id="regenerateCodes" class="w-full sm:w-auto px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm rounded-lg transition-colors duration-200 flex items-center justify-center space-x-2">
                            <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                            <span>Regenerar</span>
                        </button>
                    </div>
                    
                    <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-start space-x-2">
                            <i data-lucide="alert-triangle" class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0"></i>
                            <div class="text-sm text-yellow-700">
                                <strong>Importante:</strong> Guarda estos códigos en un lugar seguro. Cada código solo se puede usar una vez.
                            </div>
                        </div>
                    </div>

                    <div id="recoveryCodesContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                        @if(Auth::user()->two_factor_recovery_codes)
                            @foreach(json_decode(decrypt(Auth::user()->two_factor_recovery_codes), true) as $code)
                                <code class="bg-white px-3 py-2 rounded border text-center font-mono text-sm select-all">{{ $code }}</code>
                            @endforeach
                        @endif
                    </div>
                    
                    <div class="mt-4 flex justify-end">
                        <button id="downloadCodes" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm rounded-lg transition-colors duration-200 flex items-center space-x-2">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            <span>Descargar Códigos</span>
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <!-- Help Section -->
        <div class="mt-8 bg-blue-50 rounded-2xl p-6">
            <div class="flex items-start space-x-3">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i data-lucide="help-circle" class="w-5 h-5 text-blue-600"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-blue-900 mb-2">¿Cómo funciona la autenticación de dos factores?</h3>
                    <div class="text-blue-800 text-sm space-y-2">
                        <p>• Después de ingresar tu contraseña, necesitarás un código de 6 dígitos de tu app autenticadora</p>
                        <p>• Los códigos de recuperación te permiten acceder si pierdes tu dispositivo</p>
                        <p>• Cada código de recuperación solo se puede usar una vez</p>
                        <p>• Puedes regenerar los códigos de recuperación en cualquier momento</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Modal -->
    <div id="loadingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 w-full max-w-sm mx-4">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                <p class="text-gray-700">Procesando...</p>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const csrfToken = '{{ csrf_token() }}';
        const toggleTwoFactorUrl = '{{ route('two-factor.toggle') }}';
        const regenerateCodesUrl = '{{ route('two-factor.regenerate-codes') }}';
        const is2FAEnabled = {{ Auth::user()->two_factor_enabled ? 'true' : 'false' }};

        function showLoading() {
            const modal = document.getElementById('loadingModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function hideLoading() {
            const modal = document.getElementById('loadingModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function displayRecoveryCodes(codes) {
            // IMPORTANTE: Solo intentar actualizar si el elemento existe
            const container = document.getElementById('recoveryCodesContainer');
            if (!container) {
                console.log('Recovery codes container not found - codes will show after page reload');
                return;
            }
            
            container.innerHTML = '';
            codes.forEach(code => {
                const codeElement = document.createElement('code');
                codeElement.className = 'bg-white px-3 py-2 rounded border text-center font-mono text-sm select-all';
                codeElement.textContent = code;
                container.appendChild(codeElement);
            });
        }

        function toggleTwoFactor(enable, code = null) {
            const requestData = { enable: enable };
            if (code) {
                requestData.two_factor_code = code;
            }

            fetch(toggleTwoFactorUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(requestData)
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        console.error('Server returned HTML instead of JSON:', text);
                        throw new Error('El servidor devolvió una respuesta inválida');
                    });
                }
                return response.json();
            })
            .then(data => {
                hideLoading();
                
                if (data.success) {
                    if (data.recovery_codes) {
                        // Intentar mostrar códigos, pero no fallar si no se puede
                        try {
                            displayRecoveryCodes(data.recovery_codes);
                        } catch (error) {
                            console.log('Could not display recovery codes immediately');
                        }
                        
                        Swal.fire({
                            icon: 'success',
                            title: '¡2FA Activado!',
                            html: `
                                <p class="mb-4">${data.message}</p>
                                <div class="bg-yellow-50 p-4 rounded-lg mb-4">
                                    <p class="text-sm text-yellow-800 font-semibold">
                                        ⚠️ Los códigos de recuperación se mostrarán en la página
                                    </p>
                                </div>
                            `,
                            confirmButtonColor: '#10b981',
                            confirmButtonText: 'Continuar'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: data.message,
                            confirmButtonColor: '#10b981'
                        }).then(() => {
                            location.reload();
                        });
                    }
                } else if (data.needs_verification) {
                    const qrSection = document.getElementById('qrSection');
                    const qrImage = document.getElementById('qrCodeImage');
                    const secretCode = document.getElementById('secretCode');
                    const qrText = document.getElementById('qrCodeContainer').querySelector('p');
                    
                    if (qrSection) qrSection.classList.remove('hidden');
                    if (qrImage) {
                        qrImage.src = data.qr_code_url;
                        qrImage.style.display = 'block';
                    }
                    if (secretCode) secretCode.textContent = data.secret;
                    if (qrText) qrText.style.display = 'none';
                } else {
                    let errorMessage = data.error || 'No se pudo completar la acción';
                    if (data.errors) {
                        errorMessage += '\n\nDetalles:\n';
                        for (const field in data.errors) {
                            errorMessage += `${field}: ${data.errors[field].join(', ')}\n`;
                        }
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Hubo un problema con la conexión. Intenta recargar la página.',
                    confirmButtonColor: '#ef4444'
                });
            });
        }

        // SOLO UN EVENT LISTENER para cada botón
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle 2FA Button
            const toggleButton = document.getElementById('toggleTwoFactor');
            if (toggleButton) {
                toggleButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    if (is2FAEnabled) {
                        Swal.fire({
                            title: 'Desactivar 2FA',
                            text: 'Ingresa tu código de autenticación actual:',
                            input: 'text',
                            inputPlaceholder: '000000',
                            inputAttributes: {
                                maxlength: 6,
                                autocapitalize: 'off',
                                autocorrect: 'off'
                            },
                            showCancelButton: true,
                            confirmButtonText: 'Desactivar',
                            cancelButtonText: 'Cancelar',
                            confirmButtonColor: '#ef4444',
                            cancelButtonColor: '#6b7280'
                        }).then((result) => {
                            if (result.isConfirmed && result.value) {
                                showLoading();
                                toggleTwoFactor('false', result.value);
                            }
                        });
                    } else {
                        showLoading();
                        toggleTwoFactor('true');
                    }
                });
            }

            // Verify Code Button
            const verifyButton = document.getElementById('verifyCode');
            if (verifyButton) {
                verifyButton.addEventListener('click', function() {
                    const code = document.getElementById('verificationCode').value;
                    
                    if (code.length !== 6 || !/^\d+$/.test(code)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Por favor ingresa un código de 6 dígitos numérico',
                            confirmButtonColor: '#ef4444'
                        });
                        return;
                    }
                    
                    showLoading();
                    toggleTwoFactor('true', code);
                });
            }

            // Regenerate Codes Button
            const regenerateButton = document.getElementById('regenerateCodes');
            if (regenerateButton) {
                regenerateButton.addEventListener('click', function() {
                    Swal.fire({
                        title: '¿Regenerar códigos?',
                        text: 'Los códigos actuales dejarán de funcionar. ¿Estás seguro?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Regenerar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#6b7280',
                        cancelButtonColor: '#ef4444'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            showLoading();
                            fetch(regenerateCodesUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                hideLoading();
                                if (data.success) {
                                    displayRecoveryCodes(data.recovery_codes);
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Éxito',
                                        text: data.message,
                                        confirmButtonColor: '#10b981'
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: data.error || 'No se pudieron regenerar los códigos',
                                        confirmButtonColor: '#ef4444'
                                    });
                                }
                            })
                            .catch(error => {
                                hideLoading();
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Ocurrió un error inesperado',
                                    confirmButtonColor: '#ef4444'
                                });
                            });
                        }
                    });
                });
            }

            // Download Codes Button
            const downloadButton = document.getElementById('downloadCodes');
            if (downloadButton) {
                downloadButton.addEventListener('click', function() {
                    const codes = Array.from(document.querySelectorAll('#recoveryCodesContainer code')).map(el => el.textContent);
                    const content = `Códigos de Recuperación - Social Hub Manager
Fecha: ${new Date().toLocaleDateString()}
Cuenta: {{ Auth::user()->email }}

IMPORTANTE: Guarda estos códigos en un lugar seguro.
Cada código solo se puede usar una vez.

${codes.map((code, index) => `${index + 1}. ${code}`).join('\n')}

---
Social Hub Manager - Sistema de Gestión de Redes Sociales`;
                    
                    const blob = new Blob([content], { type: 'text/plain' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `recovery-codes-${new Date().toISOString().split('T')[0]}.txt`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Descarga completada',
                        text: 'Los códigos de recuperación se han descargado exitosamente',
                        confirmButtonColor: '#10b981'
                    });
                });
            }
        });
    </script>
@endsection