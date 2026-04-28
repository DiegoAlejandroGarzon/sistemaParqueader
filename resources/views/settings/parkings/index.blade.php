<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Mis Parqueaderos') }}
            </h2>
            @if(auth()->user()->role === 'super-admin' || !auth()->user()->max_parkings || auth()->user()->ownedParkings()->count() < auth()->user()->max_parkings)
                <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'create-parking')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm">
                    + Nuevo Parqueadero
                </button>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($parkings as $parking)
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border-t-4 border-indigo-500">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $parking->name }}</h3>
                                <p class="text-xs text-gray-500 mb-4">NIT: {{ $parking->nit ?? 'N/A' }}</p>
                            </div>
                            <form action="{{ route('active-parking.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="parking_id" value="{{ $parking->id }}">
                                <button type="submit" class="p-2 {{ session('active_parking_id') == $parking->id ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }} rounded-full hover:bg-green-200 transition" title="Seleccionar este parqueadero">
                                    {{ session('active_parking_id') == $parking->id ? '✅ Activo' : '🔘 Activar' }}
                                </button>
                            </form>
                        </div>

                        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                            <p>📍 {{ $parking->address ?? 'Sin dirección' }}</p>
                            <p>📞 {{ $parking->phone ?? 'Sin teléfono' }}</p>
                            <p>🕒 {{ $parking->schedule ?? 'Sin horario' }}</p>
                        </div>

                        <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <h4 class="text-xs font-bold uppercase text-gray-400 mb-2">Operadores Asignados</h4>
                            <div class="flex flex-wrap gap-1 mb-4">
                                @forelse($parking->operators as $op)
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-[10px] text-gray-600 dark:text-gray-300">
                                        {{ $op->name }}
                                    </span>
                                @empty
                                    <span class="text-[10px] italic text-gray-400">Sin operadores</span>
                                @endforelse
                            </div>
                            
                            <div class="flex gap-2">
                                <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'assign-{{ $parking->id }}')" class="text-xs text-indigo-600 font-bold hover:underline">Asignar</button>
                                <form action="{{ route('settings.parkings.destroy', $parking) }}" method="POST" onsubmit="return confirm('¿Eliminar este parqueadero borrará todos sus tickets y tarifas?')">
                                    @csrf @method('delete')
                                    <button type="submit" class="text-xs text-red-600 font-bold hover:underline">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Asignar Operadores -->
                    <x-modal name="assign-{{ $parking->id }}" focusable>
                        <form method="post" action="{{ route('settings.parkings.assign-operators', $parking) }}" class="p-6">
                            @csrf
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Asignar Operadores a {{ $parking->name }}</h2>
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                @php
                                    $allOps = auth()->user()->role === 'super-admin' 
                                        ? \App\Models\User::where('role', 'operator')->get()
                                        : \App\Models\User::where('created_by', auth()->id())->where('role', 'operator')->get();
                                @endphp
                                @foreach($allOps as $op)
                                    <label class="flex items-center space-x-2 p-2 border rounded hover:bg-gray-50 cursor-pointer">
                                        <input type="checkbox" name="operators[]" value="{{ $op->id }}" {{ $parking->operators->contains($op->id) ? 'checked' : '' }}>
                                        <span class="text-sm">{{ $op->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <div class="mt-6 flex justify-end">
                                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                                <x-primary-button class="ms-3">Guardar Cambios</x-primary-button>
                            </div>
                        </form>
                    </x-modal>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Modal Crear Parqueadero -->
    <x-modal name="create-parking" focusable>
        <form method="post" action="{{ route('settings.parkings.store') }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Nuevo Parqueadero</h2>
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="name" value="Nombre" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required />
                </div>
                <div>
                    <x-input-label for="nit" value="NIT" />
                    <x-text-input id="nit" name="nit" type="text" class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label for="address" value="Dirección" />
                    <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" />
                </div>
                <div>
                    <x-input-label for="phone" value="Teléfono" />
                    <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" />
                </div>
                <div class="col-span-full">
                    <x-input-label for="schedule" value="Horario" />
                    <x-text-input id="schedule" name="schedule" type="text" class="mt-1 block w-full" placeholder="Ej: Lunes a Viernes 7am - 9pm" />
                </div>
                @if(auth()->user()->role === 'super-admin')
                <div>
                    <x-input-label for="admin_id" value="Administrador Responsable" />
                    <select name="admin_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md">
                        @foreach(\App\Models\User::where('role', 'admin')->get() as $adm)
                            <option value="{{ $adm->id }}">{{ $adm->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                <x-primary-button class="ms-3">Crear Parqueadero</x-primary-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
