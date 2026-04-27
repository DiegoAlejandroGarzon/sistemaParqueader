<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard General') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- TARJETAS DE MÉTRICAS RÁPIDAS -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Vehículos Adentro -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border-l-4 border-blue-500">
                    <div class="flex items-center">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                            <span class="text-2xl">🚗</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Vehículos Adentro</p>
                            <h3 class="text-2xl font-bold dark:text-white">{{ $activeTicketsCount }}</h3>
                        </div>
                    </div>
                </div>

                <!-- Recaudo Hoy -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border-l-4 border-green-500">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                            <span class="text-2xl">💰</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Recaudo de Hoy</p>
                            <h3 class="text-2xl font-bold dark:text-white">${{ number_format($totalRevenueToday, 0) }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILA DE GRÁFICAS -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Gráfica 1: Dinero por vehículo -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm">
                    <h3
                        class="text-lg font-bold text-gray-700 dark:text-gray-200 mb-6 underline decoration-indigo-500 underline-offset-8">
                        Recaudado por Tipo de Vehículo
                    </h3>
                    <div style="height: 350px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

                <!-- Gráfica 2: Picos de Horario -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm">
                    <h3
                        class="text-lg font-bold text-gray-700 dark:text-gray-200 mb-6 underline decoration-indigo-500 underline-offset-8">
                        Tendencia de Ingresos (Peak Hours)
                    </h3>
                    <div style="height: 350px;">
                        <canvas id="peakChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- ACCIONES RÁPIDAS -->
            <div class="bg-indigo-600 rounded-2xl p-8 text-white relative overflow-hidden shadow-xl">
                <div class="relative z-10">
                    <h2 class="text-2xl font-bold mb-2">Operación Rápida</h2>
                    <p class="text-indigo-100 mb-6 max-w-lg">Accede directamente al panel de ingreso y salida para
                        gestionar los vehículos del parqueadero.</p>
                    <a href="{{ route('parking.index') }}"
                        class="inline-flex items-center px-6 py-3 bg-white text-indigo-600 font-bold rounded-lg hover:bg-indigo-50 shadow-lg transition-colors">
                        Ir a Parqueadero
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                </div>
                <div
                    class="absolute right-0 bottom-0 opacity-10 pointer-events-none transform translate-y-1/4 translate-x-1/4">
                    <span style="font-size: 200px;">🅿️</span>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Chart 1: Revenue by Vehicle
            const revData = @json($revenueByVehicle);
            new Chart(document.getElementById('revenueChart'), {
                type: 'pie',
                data: {
                    labels: revData.map(d => d.label),
                    datasets: [{
                        data: revData.map(d => d.total),
                        backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ec4899', '#3b82f6'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Chart 2: Peak Hours
            const hourData = @json($entriesByHour);
            const labelsH = Array.from({
                length: 24
            }, (_, i) => `${i}:00`);
            const valuesH = new Uint16Array(24).fill(0);
            hourData.forEach(d => {
                valuesH[d.hour] = d.count;
            });

            new Chart(document.getElementById('peakChart'), {
                type: 'bar',
                data: {
                    labels: labelsH,
                    datasets: [{
                        label: 'Ingresos',
                        data: Array.from(valuesH),
                        backgroundColor: '#6366f1',
                        borderRadius: 5,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
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
        });
    </script>
</x-app-layout>
