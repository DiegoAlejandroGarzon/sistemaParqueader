<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Estadísticas y Reportes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Filtros Compactos -->
            <div class="p-4 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="flex items-center mb-2">
                    <span class="text-xs font-bold uppercase text-gray-400 tracking-wider">Filtros</span>
                </div>
                <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap md:flex-nowrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <x-text-input id="start_date" name="start_date" type="date" class="text-sm py-1" value="{{ $startDate }}" />
                        <span class="text-gray-400">al</span>
                        <x-text-input id="end_date" name="end_date" type="date" class="text-sm py-1" value="{{ $endDate }}" />
                    </div>
                    
                    <div class="flex-grow flex items-center gap-2">
                        <select name="parking_id" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm py-1">
                            <option value="">Todas las sedes</option>
                            @foreach($parkings as $p)
                                <option value="{{ $p->id }}" {{ $parkingId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                        
                        <button type="submit" class="p-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md transition shrink-0" title="Aplicar Filtros">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Resumen Rápido -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border-l-4 border-green-500">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Recaudo Total</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($totalRevenue, 0) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow border-l-4 border-blue-500">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Servicios Finalizados</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalTickets }}</p>
                </div>
            </div>

            <!-- Gráficas -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Gráfica de Línea: Recaudo Diario -->
                <div class="p-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-200">Recaudo Diario</h3>
                    <canvas id="dailyRevenueChart" height="200"></canvas>
                </div>

                <!-- Gráfica de Torta: Métodos de Pago -->
                <div class="p-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-200">Distribución por Pago</h3>
                    <canvas id="paymentMethodsChart" height="200"></canvas>
                </div>

                <!-- Gráfica de Barras: Ingresos por Tipo de Vehículo -->
                <div class="p-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-200">Recaudo por Vehículo</h3>
                    <canvas id="vehicleTypesChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts para Gráficas -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Data for charts
        const dailyData = @json($dailyRevenue);
        const paymentData = @json($paymentMethods);
        const vehicleData = @json($vehicleTypes);

        // Daily Revenue Chart
        new Chart(document.getElementById('dailyRevenueChart'), {
            type: 'line',
            data: {
                labels: dailyData.map(d => d.date),
                datasets: [{
                    label: 'Recaudo ($)',
                    data: dailyData.map(d => d.total),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            }
        });

        // Payment Methods Chart
        new Chart(document.getElementById('paymentMethodsChart'), {
            type: 'doughnut',
            data: {
                labels: paymentData.map(d => d.payment_method),
                datasets: [{
                    data: paymentData.map(d => d.total),
                    backgroundColor: ['#6366f1', '#f59e0b', '#ec4899', '#8b5cf6']
                }]
            }
        });

        // Vehicle Types Chart
        new Chart(document.getElementById('vehicleTypesChart'), {
            type: 'bar',
            data: {
                labels: vehicleData.map(d => d.vehicle_type ? d.vehicle_type.name : 'Otro'),
                datasets: [{
                    label: 'Recaudo por Categoría',
                    data: vehicleData.map(d => d.total),
                    backgroundColor: '#3b82f6'
                }]
            }
        });
    </script>
</x-app-layout>
