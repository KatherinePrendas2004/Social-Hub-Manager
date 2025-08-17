@extends('layouts.app')

@section('title', 'Mis Publicaciones')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Dashboard</h2>
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

    <!-- Tabs for Analytics, Pending, and History -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="flex border-b border-gray-100">
            <button class="tab-button flex-1 py-4 px-6 text-sm font-semibold text-gray-600 bg-gray-50 hover:bg-gray-100 transition-colors active-tab"
                    data-tab="analytics">Analíticas</button>
            <button class="tab-button flex-1 py-4 px-6 text-sm font-semibold text-gray-600 hover:bg-gray-100 transition-colors"
                    data-tab="pending">Pendientes</button>
            <button class="tab-button flex-1 py-4 px-6 text-sm font-semibold text-gray-600 hover:bg-gray-100 transition-colors"
                    data-tab="history">Historial</button>
        </div>

        <!-- Analytics Tab -->
        <div id="analytics-tab" class="tab-content p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i data-lucide="bar-chart-3" class="w-6 h-6 mr-2 text-purple-600"></i>
                Analíticas y Estadísticas
            </h3>
            
            <div id="analytics-loading" class="text-center py-12">
                <i data-lucide="loader-2" class="w-8 h-8 animate-spin mx-auto text-blue-500"></i>
                <p class="text-gray-500 mt-2">Cargando analíticas...</p>
            </div>
            
            <div id="analytics-content" class="hidden">
                <!-- Estadísticas básicas en el tab de analíticas -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 rounded-lg">
                                <i data-lucide="clock" class="w-6 h-6 text-blue-600"></i>
                            </div>
                            <div class="ml-4">
                                <p id="analytics-pending" class="text-2xl font-bold text-gray-900">--</p>
                                <p class="text-sm text-gray-600">Pendientes</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-100 rounded-lg">
                                <i data-lucide="check-circle" class="w-6 h-6 text-green-600"></i>
                            </div>
                            <div class="ml-4">
                                <p id="analytics-published" class="text-2xl font-bold text-gray-900">--</p>
                                <p class="text-sm text-gray-600">Publicadas</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex items-center">
                            <div class="p-3 bg-red-100 rounded-lg">
                                <i data-lucide="x-circle" class="w-6 h-6 text-red-600"></i>
                            </div>
                            <div class="ml-4">
                                <p id="analytics-failed" class="text-2xl font-bold text-gray-900">--</p>
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
                                <p id="analytics-total" class="text-2xl font-bold text-gray-900">--</p>
                                <p class="text-sm text-gray-600">Total</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Próximo horario programado -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-xl border border-blue-200 mb-6">
                    <div class="flex items-center">
                        <i data-lucide="clock" class="w-8 h-8 text-blue-600 mr-3"></i>
                        <div>
                            <h4 class="text-lg font-semibold text-blue-800">Próxima Publicación Programada</h4>
                            <p id="next-schedule" class="text-blue-600 font-medium">--</p>
                        </div>
                    </div>
                </div>

                <!-- Gráficos en grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Estadísticas mensuales -->
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i data-lucide="trending-up" class="w-5 h-5 mr-2 text-green-600"></i>
                            Publicaciones por Mes
                        </h4>
                        <div class="h-64">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>

                    <!-- Estadísticas por plataforma -->
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i data-lucide="share-2" class="w-5 h-5 mr-2 text-blue-600"></i>
                            Rendimiento por Plataforma
                        </h4>
                        <div id="platform-stats" class="space-y-3">
                            <!-- Se llena dinámicamente -->
                        </div>
                    </div>

                    <!-- Actividad semanal -->
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i data-lucide="calendar" class="w-5 h-5 mr-2 text-purple-600"></i>
                            Actividad por Día de la Semana
                        </h4>
                        <div class="h-64">
                            <canvas id="weeklyChart"></canvas>
                        </div>
                    </div>

                    <!-- Horarios activos -->
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i data-lucide="clock" class="w-5 h-5 mr-2 text-orange-600"></i>
                            Horarios Programados Activos
                        </h4>
                        <div id="active-schedules" class="space-y-2 max-h-64 overflow-y-auto">
                            <!-- Se llena dinámicamente -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Posts -->
        <div id="pending-tab" class="tab-content p-6 hidden">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Publicaciones Pendientes</h3>
            @if($pendingPosts->isEmpty())
                <div class="text-center py-6 text-gray-500">
                    No hay publicaciones pendientes en la cola.
                </div>
            @else
                <div class="space-y-4">
                    @foreach($pendingPosts as $queuedPost)
                        <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-4 rounded-xl border-l-4 border-blue-500 hover:shadow-md transition-all duration-300">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-3">
                                    <i data-lucide="clock" class="w-5 h-5 text-blue-600"></i>
                                    <span class="font-semibold text-gray-800">
                                        Programada para: {{ $queuedPost->scheduled_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                                <button class="cancel-post-btn text-red-600 hover:text-red-700 transition-colors"
                                        data-queue-id="{{ $queuedPost->id }}"
                                        onclick="confirmCancel(this)">
                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                </button>
                            </div>
                            <p class="text-gray-700 text-sm mb-2">{{ Str::limit($queuedPost->post->content, 200) }}</p>
                            @if($queuedPost->post->reddit_title)
                                <p class="text-gray-600 text-sm italic">Título Reddit: {{ Str::limit($queuedPost->post->reddit_title, 100) }}</p>
                            @endif
                            <div class="flex space-x-2 mt-2">
                                @foreach($queuedPost->post->platforms as $platform)
                                    <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">
                                        {{ ucfirst($platform) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">
                    {{ $pendingPosts->appends(request()->except('page'))->links('pagination::tailwind') }}
                </div>
            @endif
        </div>

        <!-- History Posts -->
        <div id="history-tab" class="tab-content p-6 hidden">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Historial de Publicaciones</h3>
            @if($historyPosts->isEmpty())
                <div class="text-center py-6 text-gray-500">
                    No hay publicaciones en el historial.
                </div>
            @else
                <div class="space-y-4">
                    @foreach($historyPosts as $post)
                        <div class="bg-gradient-to-r {{ $post->status === 'published' ? 'from-green-50 to-emerald-50' : ($post->status === 'cancelled' ? 'from-gray-50 to-gray-200' : 'from-red-50 to-pink-50') }} p-4 rounded-xl border-l-4 {{ $post->status === 'published' ? 'border-green-500' : ($post->status === 'cancelled' ? 'border-gray-500' : 'border-red-500') }} hover:shadow-md transition-all duration-300">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-3">
                                    <i data-lucide="{{ $post->status === 'published' ? 'check-circle' : ($post->status === 'cancelled' ? 'x' : 'x-circle') }}" class="w-5 h-5 {{ $post->status === 'published' ? 'text-green-600' : ($post->status === 'cancelled' ? 'text-gray-600' : 'text-red-600') }}"></i>
                                    <span class="font-semibold text-gray-800">
                                        {{ $post->status === 'published' ? 'Publicado' : ($post->status === 'cancelled' ? 'Cancelado' : 'Fallido') }}: {{ $post->updated_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>
                            <p class="text-gray-700 text-sm mb-2">{{ Str::limit($post->content, 200) }}</p>
                            @if($post->reddit_title)
                                <p class="text-gray-600 text-sm italic">Título Reddit: {{ Str::limit($post->reddit_title, 100) }}</p>
                            @endif
                            <div class="flex space-x-2 mt-2">
                                @foreach($post->platforms as $platform)
                                    <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-medium">
                                        {{ ucfirst($platform) }}
                                    </span>
                                @endforeach
                            </div>
                            @if($post->status === 'failed' && $post->error_message)
                                <p class="text-red-600 text-xs mt-2">Error: {{ $post->error_message }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">
                    {{ $historyPosts->appends(request()->except('page'))->links('pagination::tailwind') }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar Lucide Icons solo una vez
        if (!window.lucideIconsInitialized) {
            lucide.createIcons();
            window.lucideIconsInitialized = true;
        }

        // Tab switching
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault(); // Prevenir comportamiento predeterminado
                tabButtons.forEach(btn => btn.classList.remove('active-tab', 'bg-gray-100'));
                tabContents.forEach(content => content.classList.add('hidden'));

                this.classList.add('active-tab', 'bg-gray-100');
                const tabContent = document.getElementById(`${this.dataset.tab}-tab`);
                tabContent.classList.remove('hidden');

                // Cargar analíticas cuando se selecciona el tab
                if (this.dataset.tab === 'analytics') {
                    loadAnalytics();
                }
            });
        });

        // Cargar analíticas al iniciar
        loadAnalytics();

        // Cancel post confirmation
        window.confirmCancel = function(button) {
            if (confirm('¿Estás seguro de que quieres cancelar esta publicación? Esta acción no se puede deshacer.')) {
                const queueId = button.dataset.queueId;
                cancelPost(queueId);
            }
        };

        // Cancel post via AJAX
        async function cancelPost(queueId) {
            try {
                const response = await fetch(`/dashboard/queue/${queueId}/cancel`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showMessage(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showMessage(data.message || 'Error al cancelar la publicación', 'error');
                }
            } catch (error) {
                showMessage('Error de conexión', 'error');
            }
        }

        // Load analytics function
        async function loadAnalytics() {
            const loadingEl = document.getElementById('analytics-loading');
            const contentEl = document.getElementById('analytics-content');
            
            loadingEl.classList.remove('hidden');
            contentEl.classList.add('hidden');
            
            try {
                const response = await fetch('/dashboard/analytics', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `HTTP ${response.status}: Error al cargar las analíticas`);
                }

                const data = await response.json();
                
                loadingEl.classList.add('hidden');
                contentEl.classList.remove('hidden');
                
                renderAnalytics(data);
                
            } catch (error) {
                console.error('Error loading analytics:', error);
                loadingEl.innerHTML = `
                    <div class="text-center py-12">
                        <i data-lucide="alert-circle" class="w-8 h-8 mx-auto text-red-500"></i>
                        <p class="text-red-500 mt-2">Error al cargar las analíticas</p>
                        <p class="text-gray-500 text-sm mt-1">${error.message}</p>
                        <button onclick="loadAnalytics()" class="mt-2 text-blue-600 hover:text-blue-700 text-sm">
                            Reintentar
                        </button>
                    </div>
                `;
                lucide.createIcons();
            }
        }

        // Render analytics data
        function renderAnalytics(data) {
            // Estadísticas básicas
            if (data.basic_stats) {
                document.getElementById('analytics-pending').textContent = data.basic_stats.pending || 0;
                document.getElementById('analytics-published').textContent = data.basic_stats.published || 0;
                document.getElementById('analytics-failed').textContent = data.basic_stats.failed || 0;
                document.getElementById('analytics-total').textContent = data.basic_stats.total || 0;
            }

            // Próximo horario
            document.getElementById('next-schedule').textContent = 
                data.next_schedule || 'No hay horarios programados';

            // Estadísticas por plataforma
            renderPlatformStats(data.platform_stats || {});

            // Horarios activos
            renderActiveSchedules(data.active_schedules || []);

            // Gráfico mensual
            renderMonthlyChart(data.monthly_stats || []);

            // Gráfico semanal
            renderWeeklyChart(data.weekly_stats || []);
        }

        // Render platform statistics
        function renderPlatformStats(platformStats) {
            const container = document.getElementById('platform-stats');
            container.innerHTML = '';

            if (Object.keys(platformStats).length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-sm">No hay datos de plataformas disponibles</p>';
                return;
            }

            Object.entries(platformStats).forEach(([platform, stats]) => {
                const platformName = platform.charAt(0).toUpperCase() + platform.slice(1);
                const successRate = stats.success_rate || 0;
                
                const platformEl = document.createElement('div');
                platformEl.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg';
                platformEl.innerHTML = `
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full mr-3 ${getPlatformColor(platform)}"></div>
                        <span class="font-medium text-gray-700">${platformName}</span>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-semibold text-gray-900">${stats.published || 0}/${stats.total || 0}</div>
                        <div class="text-xs text-gray-500">${successRate}% éxito</div>
                    </div>
                `;
                container.appendChild(platformEl);
            });
        }

        // Render active schedules
        function renderActiveSchedules(schedules) {
            const container = document.getElementById('active-schedules');
            container.innerHTML = '';

            if (schedules.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-sm">No hay horarios activos configurados</p>';
                return;
            }

            schedules.forEach(schedule => {
                const scheduleEl = document.createElement('div');
                scheduleEl.className = 'flex items-center justify-between p-3 bg-orange-50 rounded-lg border border-orange-200';
                scheduleEl.innerHTML = `
                    <div class="flex items-center">
                        <i data-lucide="calendar" class="w-4 h-4 mr-2 text-orange-600"></i>
                        <span class="text-sm font-medium text-gray-700">${schedule.day}</span>
                    </div>
                    <span class="text-sm font-semibold text-orange-600">${schedule.time}</span>
                `;
                container.appendChild(scheduleEl);
            });
            
            lucide.createIcons();
        }

        // Render monthly chart
        function renderMonthlyChart(monthlyStats) {
            const ctx = document.getElementById('monthlyChart').getContext('2d');
            
            // Destruir gráfico existente si existe
            if (window.monthlyChartInstance) {
                window.monthlyChartInstance.destroy();
            }
            
            window.monthlyChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: monthlyStats.map(stat => stat.month),
                    datasets: [
                        {
                            label: 'Publicadas',
                            data: monthlyStats.map(stat => stat.published || 0),
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Fallidas',
                            data: monthlyStats.map(stat => stat.failed || 0),
                            borderColor: 'rgb(239, 68, 68)',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Render weekly chart
        function renderWeeklyChart(weeklyStats) {
            const ctx = document.getElementById('weeklyChart').getContext('2d');
            
            // Destruir gráfico existente si existe
            if (window.weeklyChartInstance) {
                window.weeklyChartInstance.destroy();
            }
            
            window.weeklyChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: weeklyStats.map(stat => stat.day),
                    datasets: [{
                        label: 'Publicaciones',
                        data: weeklyStats.map(stat => stat.count || 0),
                        backgroundColor: 'rgba(147, 51, 234, 0.8)',
                        borderColor: 'rgb(147, 51, 234)',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }

        // Get platform color
        function getPlatformColor(platform) {
            const colors = {
                twitter: 'bg-blue-500',
                linkedin: 'bg-blue-600',
                facebook: 'bg-blue-700',
                reddit: 'bg-orange-500'
            };
            return colors[platform] || 'bg-gray-500';
        }

        // Show success/error messages
        function showMessage(message, type = 'success') {
            const container = document.createElement('div');
            container.className = `fixed top-4 right-4 p-4 rounded-xl shadow-lg flex items-center space-x-3 transform translate-x-full transition-transform duration-300 z-60 ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            } text-white`;
            container.innerHTML = `
                <i data-lucide="${type === 'success' ? 'check-circle' : 'alert-circle'}" class="w-5 h-5"></i>
                <span class="font-medium">${message}</span>
                <button onclick="this.parentElement.remove()" class="ml-4 text-white/80 hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            `;

            document.body.appendChild(container);
            lucide.createIcons();

            setTimeout(() => {
                container.classList.remove('translate-x-full');
            }, 100);

            setTimeout(() => {
                container.classList.add('translate-x-full');
                setTimeout(() => container.remove(), 300);
            }, 5000);
        }
    });
</script>
@endsection

@push('styles')
    <style>
        .tab-button.active-tab {
            background: linear-gradient(to right, #3b82f6, #8b5cf6);
            color: white;
        }
        .tab-content {
            transition: all 0.3s ease;
        }
    </style>
@endpush