<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Gestión de Tipos de Vehículo') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ showModal: false, editing: false, type: { id: '', name: '', icon: '', capacity: 10 } }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative"
                    role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Tipos registrados</h3>
                    <x-primary-button @click="editing = false; type = { id: '', name: '', icon: '' }; showModal = true">
                        {{ __('Agregar Nuevo') }}
                    </x-primary-button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="py-3 px-6 text-center">Icono</th>
                                <th class="py-3 px-6">Nombre</th>
                                <th class="py-3 px-6 text-center">Capacidad</th>
                                <th class="py-3 px-6 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($vehicleTypes as $vt)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <td class="py-4 px-6 text-center text-2xl">{{ $vt->icon }}</td>
                                    <td class="py-4 px-6 font-medium text-gray-900 dark:text-white">{{ $vt->name }}
                                    </td>
                                    <td class="py-4 px-6 text-center font-bold">{{ $vt->capacity }} celdas</td>
                                    <td class="py-4 px-6 text-right space-x-2">
                                        <button
                                            @click="editing = true; type = { id: '{{ $vt->id }}', name: '{{ $vt->name }}', icon: '{{ $vt->icon }}', capacity: '{{ $vt->capacity }}' }; showModal = true"
                                            class="text-indigo-600 hover:text-indigo-900">Editar</button>
                                        <form action="{{ route('settings.vehicle-types.destroy', $vt) }}"
                                            method="POST" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900"
                                                onclick="return confirm('¿Eliminar este tipo?')">Eliminar</button>
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
                    <h3 class="text-lg font-bold mb-4" x-text="editing ? 'Editar Tipo' : 'Nuevo Tipo'"></h3>
                    <form
                        :action="editing ? `/settings/vehicle-types/${type.id}` : '{{ route('settings.vehicle-types.store') }}'"
                        method="POST">
                        @csrf
                        <template x-if="editing">
                            <input type="hidden" name="_method" value="PATCH">
                        </template>

                        <div class="space-y-4">
                            <div>
                                <x-input-label for="name" :value="__('Nombre')" />
                                <x-text-input name="name" type="text" class="mt-1 block w-full"
                                    x-model="type.name" required />
                            </div>
                            <div>
                                <x-input-label for="icon" :value="__('Icono (Emoji)')" />
                                <x-text-input name="icon" type="text"
                                    class="mt-1 block w-full text-2xl text-center" x-model="type.icon"
                                    placeholder="🚗" />
                            </div>
                            <div>
                                <x-input-label for="capacity" :value="__('Capacidad (Celdas)')" />
                                <x-text-input name="capacity" type="number" class="mt-1 block w-full"
                                    x-model="type.capacity" required min="0" />
                                <p class="text-xs text-gray-500 mt-1">Número de espacios disponibles para este tipo.</p>
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
