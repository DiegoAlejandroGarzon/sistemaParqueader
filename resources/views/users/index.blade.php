<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Gestión de Usuarios') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Formulario de Creación -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <header>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Crear Nuevo Usuario</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ auth()->user()->role === 'super-admin' ? 'Crea administradores u operadores.' : 'Crea operadores para tus sedes.' }}
                    </p>
                </header>

                <form method="post" action="{{ route('users.store') }}" class="mt-6 space-y-6" x-data="{ role: 'operator' }">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="name" value="Nombre" />
                            <x-text-input name="name" type="text" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <x-input-label for="email" value="Email" />
                            <x-text-input name="email" type="email" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <x-input-label for="password" value="Contraseña" />
                            <x-text-input name="password" type="password" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <x-input-label for="password_confirmation" value="Confirmar Contraseña" />
                            <x-text-input name="password_confirmation" type="password" class="mt-1 block w-full" required />
                        </div>
                        <div>
                            <x-input-label for="role" value="Rol" />
                            <select name="role" x-model="role" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                @if(auth()->user()->role === 'super-admin')
                                    <option value="admin">Administrador</option>
                                @endif
                                <option value="operator">Operador</option>
                            </select>
                        </div>
                        @if(auth()->user()->role === 'super-admin')
                        <div x-show="role === 'admin'">
                            <x-input-label for="max_parkings" value="Límite de Sedes" />
                            <x-text-input name="max_parkings" type="number" class="mt-1 block w-full" value="1" />
                        </div>
                        @endif
                    </div>
                    <x-primary-button>Guardar Usuario</x-primary-button>
                </form>
            </div>

            <!-- Listado de Usuarios -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <header class="mb-6"><h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Usuarios Existentes</h2></header>
                <div class="overflow-x-auto text-sm">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700 uppercase text-[10px] font-bold">
                            <tr>
                                <th class="px-6 py-3">Nombre / Email</th>
                                <th class="px-6 py-3">Rol</th>
                                <th class="px-6 py-3">Creado por</th>
                                <th class="px-6 py-3">Sedes</th>
                                <th class="px-6 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($users as $user)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-[10px] font-bold rounded {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ strtoupper($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs">{{ $user->creator->name ?? 'Sistema' }}</td>
                                <td class="px-6 py-4 text-xs">
                                    @if($user->role === 'admin')
                                        Max: {{ $user->max_parkings ?? '∞' }}
                                    @else
                                        Asignado a: {{ $user->parkings->count() }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right flex justify-end gap-2">
                                    <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'edit-user-{{ $user->id }}')" class="text-indigo-600 hover:underline">Editar</button>
                                    <form method="post" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('¿Seguro?')">
                                        @csrf @method('delete')
                                        <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal de Edición -->
                            <x-modal name="edit-user-{{ $user->id }}" focusable>
                                <form method="post" action="{{ route('users.update', $user) }}" class="p-6">
                                    @csrf @method('patch')
                                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 text-left">Editar Usuario: {{ $user->name }}</h2>
                                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
                                        <div>
                                            <x-input-label value="Nombre" />
                                            <x-text-input name="name" type="text" class="mt-1 block w-full" :value="$user->name" required />
                                        </div>
                                        <div>
                                            <x-input-label value="Email" />
                                            <x-text-input name="email" type="email" class="mt-1 block w-full" :value="$user->email" required />
                                        </div>
                                        <div>
                                            <x-input-label value="Nueva Contraseña (Opcional)" />
                                            <x-text-input name="password" type="password" class="mt-1 block w-full" />
                                        </div>
                                        <div>
                                            <x-input-label value="Confirmar Contraseña" />
                                            <x-text-input name="password_confirmation" type="password" class="mt-1 block w-full" />
                                        </div>
                                        @if(auth()->user()->role === 'super-admin' && $user->role === 'admin')
                                        <div>
                                            <x-input-label value="Límite de Sedes" />
                                            <x-text-input name="max_parkings" type="number" class="mt-1 block w-full" :value="$user->max_parkings" />
                                        </div>
                                        @endif
                                    </div>
                                    <div class="mt-6 flex justify-end">
                                        <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                                        <x-primary-button class="ms-3">Actualizar Datos</x-primary-button>
                                    </div>
                                </form>
                            </x-modal>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
