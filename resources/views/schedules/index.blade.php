@extends('layouts.app')

@section('title', 'Horarios de Publicación')

@section('content')
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h1
                    class="text-2xl lg:text-3xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                    Horarios de Publicación
                </h1>
                <p class="text-gray-600 text-sm lg:text-base mt-1">Gestiona tus horarios de publicación automática</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3">
                <button id="newScheduleBtn"
                    class="flex items-center space-x-2 px-4 lg:px-6 py-2 lg:py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl hover:from-blue-600 hover:to-purple-700 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl text-sm lg:text-base">
                    <i data-lucide="plus" class="w-4 h-4 lg:w-5 lg:h-5"></i>
                    <span class="font-medium">Nuevo Horario</span>
                </button>

                <button id="cloneDayBtn"
                    class="flex items-center space-x-2 px-3 lg:px-4 py-2 lg:py-3 bg-green-500 text-white rounded-xl hover:bg-green-600 transition-all duration-300 shadow-sm hover:shadow-md text-sm lg:text-base">
                    <i data-lucide="copy" class="w-4 h-4 lg:w-5 lg:h-5"></i>
                    <span class="font-medium">Clonar Día</span>
                </button>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            <div class="p-4 lg:p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-blue-50">
                <h2 class="text-lg lg:text-xl font-bold text-gray-800">Calendario de Horarios</h2>
                <p class="text-gray-600 text-xs lg:text-sm mt-1">Haz clic en cualquier celda para agregar un nuevo horario
                </p>
            </div>

            <div class="p-3 lg:p-6">
                <!-- Mobile View (Stack) -->
                <div class="block lg:hidden space-y-4">
                    @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="font-bold text-gray-800">{{ App\Models\PublishSchedule::DAYS_OF_WEEK[$day] }}
                                </h3>
                                <button class="add-schedule-btn p-2 text-blue-600 hover:bg-blue-100 rounded-lg transition-colors" data-day="{{ $day }}">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                </button>
                            </div>
                            <div class="space-y-2">
                                @if (isset($scheduleData[$day]) && count($scheduleData[$day]) > 0)
                                    @foreach ($scheduleData[$day] as $schedule)
                                        <div class="time-slot bg-gradient-to-r from-blue-50 to-purple-50 border-l-4 border-blue-500 p-3 rounded-lg cursor-pointer hover:shadow-md transition-all duration-300 {{ !$schedule['is_active'] ? 'opacity-50' : '' }}"
                                            data-schedule-id="{{ $schedule['id'] }}" data-day="{{ $day }}" data-time="{{ $schedule['time'] }}">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="font-bold text-blue-700">{{ $schedule['time'] }}</span>
                                                <button class="toggle-schedule-btn text-xs {{ $schedule['is_active'] ? 'text-green-600 hover:text-green-700' : 'text-gray-400 hover:text-gray-600' }} transition-colors"
                                                    data-schedule-id="{{ $schedule['id'] }}">
                                                    <i data-lucide="{{ $schedule['is_active'] ? 'pause' : 'play' }}"
                                                        class="w-4 h-4"></i>
                                                </button>
                                            </div>
                                            @if (!$schedule['is_active'])
                                                <div class="text-xs text-gray-500 mt-1">Pausado</div>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-4 text-gray-500 text-sm">
                                        No hay horarios configurados
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Desktop View (Grid) -->
                <div class="hidden lg:grid grid-cols-7 gap-4 min-h-[500px]">
                    <!-- Day Headers -->
                    @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                        <div
                            class="bg-gradient-to-br from-indigo-500 to-purple-600 text-white text-center py-4 rounded-xl font-bold text-sm">
                            <div class="text-lg">{{ App\Models\PublishSchedule::DAYS_ABBREVIATED[$day] }}</div>
                            <div class="text-xs opacity-80 mt-1">{{ App\Models\PublishSchedule::DAYS_OF_WEEK[$day] }}</div>
                        </div>
                    @endforeach

                    <!-- Schedule Columns -->
                    @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                        <div class="space-y-3 pt-4" data-day="{{ $day }}" id="column-{{ $day }}">
                            @if (isset($scheduleData[$day]))
                                @foreach ($scheduleData[$day] as $schedule)
                                    <div class="time-slot bg-gradient-to-r from-blue-50 to-purple-50 border-l-4 border-blue-500 p-3 rounded-lg cursor-pointer hover:shadow-md transition-all duration-300 {{ !$schedule['is_active'] ? 'opacity-50' : '' }}"
                                        data-schedule-id="{{ $schedule['id'] }}" data-day="{{ $day }}" data-time="{{ $schedule['time'] }}">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="font-bold text-blue-700 text-sm">{{ $schedule['time'] }}</span>
                                            <button class="toggle-schedule-btn text-xs {{ $schedule['is_active'] ? 'text-green-600 hover:text-green-700' : 'text-gray-400 hover:text-gray-600' }} transition-colors"
                                                data-schedule-id="{{ $schedule['id'] }}">
                                                <i data-lucide="{{ $schedule['is_active'] ? 'pause' : 'play' }}"
                                                    class="w-3 h-3"></i>
                                            </button>
                                        </div>
                                        @if (!$schedule['is_active'])
                                            <div class="text-xs text-gray-500 mt-1">Pausado</div>
                                        @endif
                                    </div>
                                @endforeach
                            @endif

                            <button class="add-schedule-btn w-full p-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 transition-all duration-300 min-h-[60px] flex items-center justify-center" data-day="{{ $day }}">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div id="scheduleModal" class="fixed inset-0 z-50 hidden">
        <div class="modal-backdrop absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative transform transition-all duration-300 scale-95 opacity-0"
                id="modalContent">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 id="modalTitle" class="text-xl font-bold text-gray-800">Nuevo Horario</h3>
                    <button id="closeModalBtn" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <form id="scheduleForm" class="p-6 space-y-6">
                    @csrf
                    <input type="hidden" id="scheduleId" name="schedule_id">
                    <input type="hidden" id="formMethod" name="_method" value="POST">

                    <!-- Day Selection -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Día de la Semana</label>
                        <select id="dayOfWeek" name="day_of_week"
                            class="w-full p-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-gray-50">
                            @foreach (App\Models\PublishSchedule::DAYS_OF_WEEK as $key => $dayName)
                                <option value="{{ $key }}">{{ $dayName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Time Input -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Hora</label>
                        <input type="time" id="scheduleTime" name="time"
                            class="w-full p-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-gray-50"
                            required>
                    </div>

                    <!-- Active Toggle (only for edit mode) -->
                    <div id="activeToggleContainer" class="hidden">
                        <label class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div>
                                <span class="text-sm font-semibold text-gray-700">Estado Activo</span>
                                <span class="text-xs text-gray-500 block">Activar/desactivar este horario</span>
                            </div>
                            <input type="checkbox" id="isActive" name="is_active" value="1"
                                class="w-5 h-5 text-green-600 focus:ring-green-500 rounded">
                        </label>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex space-x-3 pt-4">
                        <button type="button" id="cancelBtn"
                            class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-medium transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" id="submitBtn"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl hover:from-blue-600 hover:to-purple-700 font-medium transition-all transform hover:scale-105 shadow-lg">
                            Crear Horario
                        </button>
                    </div>

                    <!-- Delete Button (only for edit mode) -->
                    <div id="deleteButtonContainer" class="hidden">
                        <button type="button" id="deleteBtn"
                            class="w-full px-4 py-3 bg-red-500 text-white rounded-xl hover:bg-red-600 font-medium transition-colors flex items-center justify-center space-x-2">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            <span>Eliminar Horario</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Clone Day Modal -->
    <div id="cloneModal" class="fixed inset-0 z-50 hidden">
        <div class="modal-backdrop absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm"></div>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md relative transform transition-all duration-300 scale-95 opacity-0"
                id="cloneModalContent">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-gray-800">Clonar Horarios</h3>
                    <button id="closeCloneModalBtn" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <form id="cloneForm" class="p-6 space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Día Origen</label>
                        <select id="fromDay" name="from_day"
                            class="w-full p-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors bg-gray-50">
                            @foreach (App\Models\PublishSchedule::DAYS_OF_WEEK as $key => $dayName)
                                <option value="{{ $key }}">{{ $dayName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Día Destino</label>
                        <select id="toDay" name="to_day"
                            class="w-full p-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors bg-gray-50">
                            @foreach (App\Models\PublishSchedule::DAYS_OF_WEEK as $key => $dayName)
                                <option value="{{ $key }}">{{ $dayName }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex space-x-3 pt-4">
                        <button type="button" id="cancelCloneBtn"
                            class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-medium transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" id="cloneSubmitBtn"
                            class="flex-1 px-4 py-3 bg-green-500 text-white rounded-xl hover:bg-green-600 font-medium transition-colors">
                            Clonar Horarios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div id="messageContainer" class="fixed top-4 right-4 z-60 space-y-2"></div>

    <script>
        // Initialize Lucide icons and global variables
        lucide.createIcons();
        
        let currentScheduleId = null;
        let isEditMode = false;
        const scheduleData = @json($scheduleData);
        const csrfToken = '{{ csrf_token() }}';

        // DOM ready initialization
        document.addEventListener('DOMContentLoaded', function() {
            initializeEventListeners();
            initializeHoverEffects();
            lucide.createIcons();
        });

        // Initialize all event listeners
        function initializeEventListeners() {
            // Button event listeners
            document.getElementById('newScheduleBtn').addEventListener('click', () => openCreateModal());
            document.getElementById('cloneDayBtn').addEventListener('click', openCloneModal);
            
            // Modal close buttons
            document.getElementById('closeModalBtn').addEventListener('click', closeModal);
            document.getElementById('closeCloneModalBtn').addEventListener('click', closeCloneModal);
            document.getElementById('cancelBtn').addEventListener('click', closeModal);
            document.getElementById('cancelCloneBtn').addEventListener('click', closeCloneModal);
            
            // Delete button
            document.getElementById('deleteBtn').addEventListener('click', deleteSchedule);
            
            // Form submissions
            document.getElementById('scheduleForm').addEventListener('submit', handleScheduleFormSubmit);
            document.getElementById('cloneForm').addEventListener('submit', handleCloneFormSubmit);
            
            // Add schedule buttons (using event delegation)
            document.addEventListener('click', function(e) {
                if (e.target.closest('.add-schedule-btn')) {
                    const btn = e.target.closest('.add-schedule-btn');
                    const day = btn.getAttribute('data-day');
                    openCreateModal(day);
                }
            });
            
            // Time slot click handlers (using event delegation)
            document.addEventListener('click', function(e) {
                if (e.target.closest('.time-slot') && !e.target.closest('.toggle-schedule-btn')) {
                    const slot = e.target.closest('.time-slot');
                    const id = slot.getAttribute('data-schedule-id');
                    const day = slot.getAttribute('data-day');
                    const time = slot.getAttribute('data-time');
                    openEditModal(id, day, time);
                }
            });
            
            // Toggle schedule buttons (using event delegation)
            document.addEventListener('click', function(e) {
                if (e.target.closest('.toggle-schedule-btn')) {
                    e.stopPropagation();
                    const btn = e.target.closest('.toggle-schedule-btn');
                    const scheduleId = btn.getAttribute('data-schedule-id');
                    toggleSchedule(scheduleId);
                }
            });
            
            // Modal backdrop clicks
            document.getElementById('scheduleModal').addEventListener('click', function(e) {
                if (e.target.classList.contains('modal-backdrop')) {
                    closeModal();
                }
            });
            
            document.getElementById('cloneModal').addEventListener('click', function(e) {
                if (e.target.classList.contains('modal-backdrop')) {
                    closeCloneModal();
                }
            });
            
            // Keyboard shortcuts
            document.addEventListener('keydown', handleKeyboardShortcuts);
        }

        // Initialize hover effects for time slots
        function initializeHoverEffects() {
            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });

                slot.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        }

        // Open create modal
        function openCreateModal(day = null) {
            isEditMode = false;
            currentScheduleId = null;

            document.getElementById('modalTitle').textContent = 'Nuevo Horario';
            document.getElementById('submitBtn').textContent = 'Crear Horario';
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('activeToggleContainer').classList.add('hidden');
            document.getElementById('deleteButtonContainer').classList.add('hidden');

            // Reset form
            document.getElementById('scheduleForm').reset();

            // Set day if provided
            if (day) {
                document.getElementById('dayOfWeek').value = day;
            }

            // Show modal with animation
            showModal('scheduleModal', 'modalContent');
        }

        // Open edit modal
        async function openEditModal(id, day, time) {
            try {
                const response = await fetch(`/schedules/${id}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    isEditMode = true;
                    currentScheduleId = id;

                    document.getElementById('modalTitle').textContent = 'Editar Horario';
                    document.getElementById('submitBtn').textContent = 'Actualizar Horario';
                    document.getElementById('formMethod').value = 'PUT';
                    document.getElementById('activeToggleContainer').classList.remove('hidden');
                    document.getElementById('deleteButtonContainer').classList.remove('hidden');

                    // Fill form with current data
                    document.getElementById('scheduleId').value = id;
                    document.getElementById('dayOfWeek').value = data.schedule.day_of_week;
                    document.getElementById('scheduleTime').value = data.schedule.time;
                    document.getElementById('isActive').checked = data.schedule.is_active;

                    // Show modal with animation
                    showModal('scheduleModal', 'modalContent');
                }
            } catch (error) {
                showMessage('Error al cargar los datos del horario', 'error');
            }
        }

        // Generic function to show modals
        function showModal(modalId, contentId) {
            const modal = document.getElementById(modalId);
            const modalContent = document.getElementById(contentId);

            modal.classList.remove('hidden');

            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        // Close main modal
        function closeModal() {
            const modalContent = document.getElementById('modalContent');
            modalContent.classList.add('scale-95', 'opacity-0');
            modalContent.classList.remove('scale-100', 'opacity-100');

            setTimeout(() => {
                document.getElementById('scheduleModal').classList.add('hidden');
            }, 300);
        }

        // Open clone modal
        function openCloneModal() {
            // Set different days by default
            document.getElementById('fromDay').value = 'monday';
            document.getElementById('toDay').value = 'tuesday';

            showModal('cloneModal', 'cloneModalContent');
        }

        // Close clone modal
        function closeCloneModal() {
            const modalContent = document.getElementById('cloneModalContent');
            modalContent.classList.add('scale-95', 'opacity-0');
            modalContent.classList.remove('scale-100', 'opacity-100');

            setTimeout(() => {
                document.getElementById('cloneModal').classList.add('hidden');
            }, 300);
        }

        // Handle main form submission
        async function handleScheduleFormSubmit(e) {
            e.preventDefault();

            if (!validateForm()) {
                return;
            }

            showLoading();

            const formData = new FormData(e.target);

            try {
                const url = isEditMode ? `/schedules/${currentScheduleId}` : '/schedules';

                if (isEditMode) {
                    formData.append('_method', 'PUT');
                }

                const response = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showMessage(data.message, 'success');
                    closeModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showMessage(data.message || 'Error al procesar la solicitud', 'error');
                }
            } catch (error) {
                showMessage('Error de conexión', 'error');
            }

            hideLoading();
        }

        // Handle clone form submission
        async function handleCloneFormSubmit(e) {
            e.preventDefault();

            const fromDay = document.getElementById('fromDay').value;
            const toDay = document.getElementById('toDay').value;

            if (fromDay === toDay) {
                showMessage('El día origen y destino no pueden ser el mismo', 'error');
                return;
            }

            showCloneLoading();

            const formData = new FormData(e.target);

            try {
                const response = await fetch('/schedules/clone-day', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showMessage(data.message, 'success');
                    closeCloneModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showMessage(data.message || 'Error al clonar horarios', 'error');
                }
            } catch (error) {
                showMessage('Error de conexión', 'error');
            }

            hideCloneLoading();
        }

        // Toggle schedule active/inactive
        async function toggleSchedule(scheduleId) {
            try {
                const response = await fetch(`/schedules/${scheduleId}/toggle`, {
                    method: 'PATCH',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showMessage(data.message, 'success');
                    setTimeout(() => location.reload(), 500);
                } else {
                    showMessage(data.message || 'Error al cambiar el estado', 'error');
                }
            } catch (error) {
                showMessage('Error de conexión', 'error');
            }
        }

        // Delete schedule function
        async function deleteSchedule() {
            if (!confirm('¿Estás seguro de que quieres eliminar este horario? Esta acción no se puede deshacer.')) {
                return;
            }

            try {
                const response = await fetch(`/schedules/${currentScheduleId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showMessage(data.message, 'success');
                    closeModal();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showMessage(data.message || 'Error al eliminar el horario', 'error');
                }
            } catch (error) {
                showMessage('Error de conexión', 'error');
            }
        }

        // Show success/error messages
        function showMessage(message, type = 'success') {
            const container = document.getElementById('messageContainer');
            const messageEl = document.createElement('div');

            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            const icon = type === 'success' ? 'check-circle' : 'alert-circle';

            messageEl.className =
                `${bgColor} text-white px-6 py-4 rounded-xl shadow-lg flex items-center space-x-3 transform translate-x-full transition-transform duration-300`;
            messageEl.innerHTML = `
            <i data-lucide="${icon}" class="w-5 h-5"></i>
            <span class="font-medium">${message}</span>
            <button onclick="this.parentElement.remove()" class="ml-4 text-white/80 hover:text-white transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        `;

            container.appendChild(messageEl);
            lucide.createIcons();

            // Animate in
            setTimeout(() => {
                messageEl.classList.remove('translate-x-full');
            }, 100);

            // Auto remove after 5 seconds
            setTimeout(() => {
                messageEl.classList.add('translate-x-full');
                setTimeout(() => messageEl.remove(), 300);
            }, 5000);
        }

        // Enhanced form validation
        function validateForm() {
            const day = document.getElementById('dayOfWeek').value;
            const time = document.getElementById('scheduleTime').value;

            if (!day || !time) {
                showMessage('Por favor completa todos los campos requeridos', 'error');
                return false;
            }

            // Validate time format
            const timePattern = /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/;
            if (!timePattern.test(time)) {
                showMessage('Por favor ingresa una hora válida (HH:MM)', 'error');
                return false;
            }

            return true;
        }

        // Loading states
        function showLoading() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = `
            <div class="flex items-center justify-center space-x-2">
                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                <span>Guardando...</span>
            </div>
        `;
            submitBtn.disabled = true;
        }

        function hideLoading() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = isEditMode ? 'Actualizar Horario' : 'Crear Horario';
            submitBtn.disabled = false;
        }

        function showCloneLoading() {
            const cloneBtn = document.getElementById('cloneSubmitBtn');
            cloneBtn.innerHTML = `
            <div class="flex items-center justify-center space-x-2">
                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                <span>Clonando...</span>
            </div>
        `;
            cloneBtn.disabled = true;
        }

        function hideCloneLoading() {
            const cloneBtn = document.getElementById('cloneSubmitBtn');
            cloneBtn.innerHTML = 'Clonar Horarios';
            cloneBtn.disabled = false;
        }

        // Keyboard shortcuts handler
        function handleKeyboardShortcuts(e) {
            // Close modals with Escape
            if (e.key === 'Escape') {
                if (!document.getElementById('scheduleModal').classList.contains('hidden')) {
                    closeModal();
                }
                if (!document.getElementById('cloneModal').classList.contains('hidden')) {
                    closeCloneModal();
                }
            }

            // Open create modal with Ctrl+N
            if (e.key === 'n' && e.ctrlKey) {
                e.preventDefault();
                openCreateModal();
            }

            // Open clone modal with Ctrl+D
            if (e.key === 'd' && e.ctrlKey) {
                e.preventDefault();
                openCloneModal();
            }
        }

        // Auto-refresh stats every 5 minutes
        setInterval(refreshStats, 300000);

        // Utility functions
        function formatTime(time24) {
            const [hours, minutes] = time24.split(':');
            const hour12 = hours % 12 || 12;
            const ampm = hours >= 12 ? 'PM' : 'AM';
            return `${hour12}:${minutes} ${ampm}`;
        }

        function validateTimeRange(time) {
            const [hours, minutes] = time.split(':').map(Number);
            return hours >= 0 && hours <= 23 && minutes >= 0 && minutes <= 59;
        }

        // Initialize everything
        console.log('Schedule management system initialized successfully');
    </script>
@endsection

@push('styles')
    <style>
        .time-slot {
            transition: all 0.3s ease;
        }

        .time-slot:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .modal-backdrop {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
        }

        /* Custom scrollbar for mobile */
        .scrollbar-thin {
            scrollbar-width: thin;
        }

        .scrollbar-thin::-webkit-scrollbar {
            width: 4px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: transparent;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.5);
            border-radius: 2px;
        }
    </style>
@endpush