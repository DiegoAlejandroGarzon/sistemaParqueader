<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Gestión de Tarifas') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ showModal: false, editing: false, rate: { id: '', vehicle_type_id: '', price_per_hour: '', fraction_price: '' } }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                    role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Tarifas configuradas</h3>
                    <x-primary-button
                        @click="editing = false; rate = { id: '', vehicle_type_id: '', price_per_hour: '', fraction_price: '' }; showModal = true">
                        {{ __('Nueva Tarifa') }}
                    </x-primary-button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="py-3 px-6">Tipo</th>
                                <th class="py-3 px-6">Precio Hora</th>
                                <th class="py-3 px-6">Precio Fracción</th>
                                <th class="py-3 px-6 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rates as $r)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="py-4 px-6 font-medium text-gray-900 dark:text-white">
                                        {{ $r->vehicleType->icon }} {{ $r->vehicleType->name }}
                                    </td>
                                    <td class="py-4 px-6 text-lg">${{ number_format($r->price_per_hour, 0) }}</td>
                                    <td class="py-4 px-6">${{ number_format($r->fraction_price, 0) }}</td>
                                    <td class="py-4 px-6 text-right space-x-2">
                                        <button
                                            @click="editing = true; rate = { id: '{{ $r->id }}', vehicle_type_id: '{{ $r->vehicle_type_id }}', price_per_hour: '{{ $r->price_per_hour }}', fraction_price: '{{ $r->fraction_price }}' }; showModal = true"
                                            class="text-indigo-600 hover:text-indigo-900">Editar</button>
                                        <form action="{{ route('settings.rates.destroy', $r) }}" method="POST"
                                            class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900"
                                                onclick="return confirm('¿Eliminar esta tarifa?')">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display:none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showModal = false"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-bold mb-4" x-text="editing ? 'Editar Tarifa' : 'Nueva Tarifa'"></h3>
                    <form :action="editing ? `/settings/rates/${rate.id}` : '{{ route('settings.rates.store') }}'"
                        method="POST">
                        @csrf
                        <template x-if="editing">
                            <input type="hidden" name="_method" value="PATCH">
                        </template>

                        <div class="space-y-4">
                            <div>
                                <x-input-label for="vehicle_type_id" :value="__('Tipo de Vehículo')" />
                                <select name="vehicle_type_id" x-model="rate.vehicle_type_id"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    required>
                                    <option value="">Seleccione...</option>
                                    @foreach ($vehicleTypes as $vt)
                                        <option value="{{ $vt->id }}">{{ $vt->icon }} {{ $vt->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="price_per_hour" :value="__('Precio por Hora')" />
                                <x-text-input name="price_per_hour" type="number" class="mt-1 block w-full"
                                    x-model="rate.price_per_hour" required />
                            </div>
                            <div>
                                <x-input-label for="fraction_price" :value="__('Precio por Fracción')" />
                                <x-text-input name="fraction_price" type="number" class="mt-1 block w-full"
                                    x-model="rate.fraction_price" />
                                <p class="text-xs text-gray-500 mt-1">Si se deja vacío, el calculador podría asumir el
                                    precio de hora completa o reglas por defecto.</p>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <x-secondary-button @click="showModal = false">Cancelar</x-secondary-button>
                            <x-primary-button type="submit">Guardar</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
