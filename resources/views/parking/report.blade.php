<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Cierre de Caja - Reporte Diario') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-medium text-gray-900 dark:text-gray-100">Reporte del Día:
                        {{ \Carbon\Carbon::now()->format('d/m/Y') }}</h3>
                    <a href="{{ route('parking.index') }}"
                        class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Volver a Parqueadero
                    </a>
                </div>

                <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="py-3 px-6">Operario</th>
                                <th scope="col" class="py-3 px-6">Vehículos Atendidos</th>
                                <th scope="col" class="py-3 px-6">Total Recaudado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $granTotal = 0;
                                $granTickets = 0;
                            @endphp
                            @forelse($reportData as $data)
                                @php
                                    $granTotal += $data->total_recaudado;
                                    $granTickets += $data->total_tickets;
                                @endphp
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="py-4 px-6 font-medium text-gray-900 dark:text-white">
                                        {{ $data->user->name }}
                                        <div class="text-xs text-gray-500">{{ $data->user->email }}</div>
                                    </td>
                                    <td class="py-4 px-6">
                                        {{ $data->total_tickets }}
                                    </td>
                                    <td class="py-4 px-6 font-bold text-green-600 dark:text-green-400 text-lg">
                                        ${{ number_format($data->total_recaudado, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td colspan="3" class="py-4 px-6 text-center text-gray-500">
                                        No hay recaudos registrados para el día de hoy.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if (count($reportData) > 0)
                            <tfoot class="font-bold text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-700">
                                <tr>
                                    <td class="py-4 px-6 text-right uppercase">Total General</td>
                                    <td class="py-4 px-6">{{ $granTickets }}</td>
                                    <td class="py-4 px-6 text-xl text-green-600 dark:text-green-400">
                                        ${{ number_format($granTotal, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            <div class="mt-4 text-center">
                <button onclick="window.print()"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded text-lg no-print">
                    🖨️ Imprimir Reporte
                </button>
                <style>
                    @media print {
                        .no-print {
                            display: none !important;
                        }

                        nav {
                            display: none !important;
                        }
                    }
                </style>
            </div>
        </div>
    </div>
</x-app-layout>
