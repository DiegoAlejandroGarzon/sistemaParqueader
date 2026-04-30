<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Gestión de Parqueadero') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="parkingDashboard()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Alertas de Éxito / Error -->
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- PANEL DE INGRESO -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Entrada Rápida</h3>
                    <form method="POST" action="{{ route('parking.entry') }}">
                        @csrf
                        <div>
                            <x-input-label for="plate" :value="__('Placa del Vehículo')" />
                            <!-- Input gigante para la placa -->
                            <x-text-input id="plate" name="plate" type="text"
                                class="mt-1 block w-full text-4xl uppercase text-center py-4" required autofocus
                                autocomplete="off" placeholder="ABC123" />
                            <x-input-error class="mt-2" :messages="$errors->get('plate')" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="vehicle_type_id" :value="__('Tipo de Vehículo')" />
                            <select id="vehicle_type_id" name="vehicle_type_id"
                                @change="checkCapacity($event.target.value)"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm text-lg py-2">
                                @foreach ($vehicleTypes as $type)
                                    <option value="{{ $type->id }}" data-capacity="{{ $type->capacity }}"
                                        data-occupied="{{ $type->tickets_count }}" {{ $loop->first ? 'selected' : '' }}>
                                        {{ $type->icon }} {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Alerta de Capacidad -->
                        <div x-show="isCapLow" x-transition
                            class="mt-4 p-3 bg-amber-50 dark:bg-amber-900/30 border-l-4 border-amber-500 text-amber-700 dark:text-amber-300 text-xs">
                            <div class="flex items-center">
                                <span class="text-xl mr-2">⚠️</span>
                                <div>
                                    <p class="font-bold">Capacidad Casi Agotada</p>
                                    <p>Quedan sólo <span x-text="availableSpaces" class="font-black"></span> espacios de
                                        <span x-text="totalSpaces"></span>.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Ocupación General -->
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($vehicleTypes as $type)
                                <div
                                    class="px-2 py-1 rounded-full text-[10px] font-bold border {{ $type->tickets_count >= $type->capacity ? 'bg-red-50 text-red-700 border-red-200' : ($type->tickets_count >= $type->capacity * 0.8 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-green-50 text-green-700 border-green-200') }}">
                                    {{ $type->name }}: {{ $type->tickets_count }}/{{ $type->capacity }}
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            <x-input-label for="entry_at" :value="__('Fecha/Hora de Ingreso (Edición manual)')" />
                            <div class="flex items-center space-x-2 mt-1">
                                <x-text-input id="entry_at" name="entry_at" type="datetime-local" class="block w-full"
                                    x-model="currentTime" @input="pauseClock()" />
                                <button type="button" @click="resetToCurrentTime()"
                                    class="p-2 bg-gray-200 dark:bg-gray-700 rounded-md hover:bg-gray-300"
                                    title="Volver a hora actual">
                                    🕒
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-blue-500 dark:text-blue-400">
                                <span class="font-bold">Hora actual:</span> <span x-text="liveClock"></span>
                            </p>
                        </div>

                        <div class="mt-6">
                            <x-primary-button class="w-full justify-center py-3 text-lg">
                                {{ __('Registrar Ingreso') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>

                <!-- LISTADO DE VEHÍCULOS ACTIVOS -->
                <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg overflow-hidden md:col-span-2">
                    <div
                        class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200">
                            Vehículos en el Parqueadero
                        </h3>
                        <div class="relative">
                            <x-text-input @input="searchTerm = $event.target.value.toUpperCase()" class="pl-10 w-64"
                                placeholder="Buscar placa..." />
                            <span class="absolute left-3 top-2.5 opacity-40">🔍</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500 font-bold">
                                <tr>
                                    <th class="px-6 py-4">Placa</th>
                                    <th class="px-6 py-4 uppercase">Tipo</th>
                                    <th class="px-6 py-4 uppercase">Ingreso</th>
                                    <th class="px-6 py-4 uppercase">Operario</th>
                                    <th class="px-6 py-4 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse($tickets as $ticket)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                        x-show="searchTerm === '' || '{{ $ticket->plate }}'.includes(searchTerm) || '{{ $ticket->id }}' === searchTerm">
                                        <td class="px-6 py-4">
                                            <span
                                                class="px-3 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-md font-mono font-bold text-lg">
                                                {{ $ticket->plate }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <span class="text-xl mr-2">{{ $ticket->vehicleType->icon }}</span>
                                                <span
                                                    class="text-gray-700 dark:text-gray-300 font-medium">{{ $ticket->vehicleType->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                            {{ $ticket->entry_at->format('d/m/Y h:i A') }}
                                            <br>
                                            <span
                                                class="text-xs opacity-60">({{ $ticket->entry_at->diffForHumans() }})</span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            {{ $ticket->user->name }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center space-x-2">
                                                <button @click="openCheckoutModal({{ $ticket->id }})"
                                                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 transition ease-in-out duration-150">
                                                    Cobrar
                                                </button>
                                                <form action="{{ route('parking.cancel', $ticket) }}" method="POST"
                                                    onsubmit="return confirm('¿Seguro que deseas ANULAR este ingreso?')"
                                                    class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="p-2 text-red-500 hover:text-red-700 transition"
                                                        title="Anular Tiquete">
                                                        🗑️
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td colspan="5" class="py-4 px-6 text-center text-gray-500">
                                            No hay vehículos en el parqueadero actualmente.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- HISTORIAL RECIENTE -->
                <div class="bg-gray-50 dark:bg-gray-900 p-6 rounded-xl border-t-2 border-green-500 shadow-sm mt-8">
                    <h3 class="font-bold text-gray-700 dark:text-gray-300 mb-4 flex items-center">
                        <span class="mr-2">🕘</span> Últimas Salidas Procesadas
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        @foreach ($recentPayments as $rp)
                            <div
                                class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 relative overflow-hidden group">
                                <div class="flex justify-between items-start mb-2">
                                    <span
                                        class="font-mono font-bold text-lg text-indigo-600">{{ $rp->plate }}</span>
                                    <span class="text-xs font-bold text-green-500">{{ $rp->payment_method }}</span>
                                </div>
                                <p class="text-sm font-bold text-gray-800 dark:text-gray-100">
                                    ${{ number_format($rp->total_amount, 0) }}</p>
                                <p class="text-[10px] text-gray-400">{{ $rp->exit_at->format('h:i A') }}</p>

                                <a href="{{ route('parking.receipt', $rp) }}" target="_blank"
                                    class="absolute inset-0 bg-indigo-500 bg-opacity-90 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-white text-xs font-bold uppercase tracking-wider">
                                    Reimprimir
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- MODAL DE COBRO (AlpineJS) -->
            <div x-show="showCheckoutModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
                role="dialog" aria-modal="true" style="display: none;">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Background overlay -->
                    <div x-show="showCheckoutModal" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                        @click="closeCheckoutModal()">
                    </div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <!-- Modal panel -->
                    <div x-show="showCheckoutModal" x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <form :action="checkoutFormAction" method="POST">
                            @csrf
                            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                        <h3 class="text-2xl leading-6 font-bold text-gray-900 dark:text-gray-100"
                                            id="modal-title">
                                            Liquidación - Placa: <span x-text="checkoutData.plate"
                                                class="text-blue-600 dark:text-blue-400 uppercase"></span>
                                        </h3>

                                        <div class="mt-6 space-y-4">
                                            <div
                                                class="flex justify-between border-b pb-2 border-gray-200 dark:border-gray-700">
                                                <span class="text-gray-500 dark:text-gray-400">Hora Ingreso:</span>
                                                <span class="font-medium text-gray-900 dark:text-gray-100"
                                                    x-text="checkoutData.entry_at"></span>
                                            </div>
                                            <div
                                                class="flex justify-between border-b pb-2 border-gray-200 dark:border-gray-700">
                                                <span class="text-gray-500 dark:text-gray-400">Hora Salida:</span>
                                                <span class="font-medium text-gray-900 dark:text-gray-100"
                                                    x-text="checkoutData.exit_at"></span>
                                            </div>
                                            <div
                                                class="flex justify-between border-b pb-2 border-gray-200 dark:border-gray-700">
                                                <span class="text-gray-500 dark:text-gray-400">Tiempo
                                                    Transcurrido:</span>
                                                <span class="font-medium text-gray-900 dark:text-gray-100"
                                                    x-text="checkoutData.time"></span>
                                            </div>
                                            <div class="flex justify-between pt-4">
                                                <span class="text-2xl font-bold text-gray-900 dark:text-gray-100">Total
                                                    a Pagar:</span>
                                                <span
                                                    class="text-3xl font-extrabold text-green-600 dark:text-green-400">$<span
                                                        x-text="checkoutData.amount"></span></span>
                                            </div>

                                            <div
                                                class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600">
                                                <label
                                                    class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 uppercase tracking-wider">
                                                    Método de Pago
                                                </label>
                                                <div class="grid grid-cols-2 gap-4">
                                                    <label
                                                        class="flex items-center justify-center p-4 border-2 rounded-2xl cursor-pointer transition-all duration-200"
                                                        :class="paymentMethod === 'Efectivo' ?
                                                            'bg-indigo-600 border-indigo-400 text-white shadow-xl scale-105 z-10' :
                                                            'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-400 opacity-60'">
                                                        <input type="radio" name="payment_method" value="Efectivo"
                                                            x-model="paymentMethod" class="hidden">
                                                        <span class="text-2xl mr-2">💵</span>
                                                        <span
                                                            class="font-black uppercase tracking-widest text-sm">Efectivo</span>
                                                    </label>
                                                    <label
                                                        class="flex items-center justify-center p-4 border-2 rounded-2xl cursor-pointer transition-all duration-200"
                                                        :class="paymentMethod === 'Transferencia' ?
                                                            'bg-indigo-600 border-indigo-400 text-white shadow-xl scale-105 z-10' :
                                                            'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-400 opacity-60'">
                                                        <input type="radio" name="payment_method"
                                                            value="Transferencia" x-model="paymentMethod"
                                                            class="hidden">
                                                        <span class="text-2xl mr-2">📱</span>
                                                        <span
                                                            class="font-black uppercase tracking-widest text-sm">Transf.</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="submit"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    Registrar Pago e Imprimir
                                </button>
                                <button type="button" @click="closeCheckoutModal()"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Script de AlpineJS embebido en Blade -->
        <script>
            function parkingDashboard() {
                return {
                    showCheckoutModal: false,
                    searchTerm: '',
                    paymentMethod: 'Efectivo',
                    currentTime: new Date().toISOString().slice(0, 16),
                    liveClock: '',
                    isPaused: false,
                    isCapLow: false,
                    availableSpaces: 0,
                    totalSpaces: 0,

                    init() {
                        this.updateClock();
                        setInterval(() => this.updateClock(), 1000);
                        // Check initial capacity
                        this.checkCapacity(document.getElementById('vehicle_type_id').value);

                        // Global focus for plate input
                        window.addEventListener('keydown', (e) => {
                            // If modal is open, don't redirect
                            if (this.showCheckoutModal) return;

                            const tags = ['INPUT', 'TEXTAREA', 'SELECT'];
                            if (tags.includes(document.activeElement.tagName)) return;

                            // Ignore modifier keys
                            if (e.ctrlKey || e.altKey || e.metaKey) return;

                            // Only redirect if it's a single character (alphanumeric, etc)
                            // and not special keys like Enter, Tab, etc.
                            if (e.key.length === 1) {
                                const plateInput = document.getElementById('plate');
                                if (plateInput) {
                                    plateInput.focus();
                                }
                            }
                        });
                    },

                    checkCapacity(typeId) {
                        const select = document.getElementById('vehicle_type_id');
                        if (!select) return;
                        const option = select.options[select.selectedIndex];
                        if (!option) return;

                        const cap = parseInt(option.getAttribute('data-capacity'));
                        const occ = parseInt(option.getAttribute('data-occupied'));

                        this.totalSpaces = cap;
                        this.availableSpaces = cap - occ;

                        // Alertar si el espacio disponible es del 20% o menos, o si quedan 2 o menos
                        this.isCapLow = (this.availableSpaces <= 2 || this.availableSpaces <= (cap * 0.2)) && cap > 0;
                    },

                    updateClock() {
                        const now = new Date();
                        this.liveClock = now.toLocaleTimeString();
                        if (!this.isPaused) {
                            // Formato YYYY-MM-DDTHH:mm para input datetime-local
                            const tzOffset = now.getTimezoneOffset() * 60000;
                            const localISOTime = (new Date(now - tzOffset)).toISOString().slice(0, 16);
                            this.currentTime = localISOTime;
                        }
                    },

                    resetToCurrentTime() {
                        this.isPaused = false;
                        this.updateClock();
                    },

                    // Al interactuar con el input manual, pausamos el reloj automático
                    pauseClock() {
                        this.isPaused = true;
                    },
                    checkoutData: {
                        id: '',
                        plate: '',
                        entry_at: '',
                        exit_at: '',
                        time: '',
                        amount: 0
                    },
                    checkoutFormAction: '',

                    openCheckoutModal(ticketId) {
                        // Hacer la petición para calcular
                        fetch(`/parking/${ticketId}/checkout`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.error) {
                                    alert(data.error);
                                    return;
                                }
                                this.checkoutData = data;
                                this.checkoutFormAction = `/parking/${ticketId}/pay`;
                                this.showCheckoutModal = true;
                            })
                            .catch(error => {
                                console.error('Error fetching checkout data:', error);
                                alert('Ocurrió un error al liquidar el tiquete.');
                            });
                    },
                    closeCheckoutModal() {
                        this.showCheckoutModal = false;
                    }
                }
            }
        </script>
</x-app-layout>
