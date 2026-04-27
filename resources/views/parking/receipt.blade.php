<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo - {{ $ticket->plate }}</title>
    <style>
        /* Estilos optimizados para impresora térmica de 58/80mm */
        body {
            font-family: 'Courier New', Courier, monospace;
            margin: 0;
            padding: 0;
            width: 100%;
            /* El ancho lo ajusta el navegador al papel, pero se puede fijar a 58mm (ej width: 220px;) */
            color: #000;
        }

        .ticket-container {
            width: 75mm; /* Ajustado para impresoras de 80mm (dejando margen de seguridad) */
            margin: 0 auto;
            padding: 5px;
            text-align: center;
            box-sizing: border-box;
        }

        .header {
            font-weight: bold;
            font-size: 1.2em;
            margin-bottom: 5px;
        }

        .subheader {
            font-size: 0.9em;
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }

        .details {
            text-align: left;
            font-size: 0.9em;
            margin-bottom: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }

        .details-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .plate-container {
            margin: 10px 0;
            font-size: 1.5em;
            font-weight: bold;
            border: 2px solid #000;
            padding: 5px;
        }

        .total-container {
            font-size: 1.2em;
            font-weight: bold;
            text-align: right;
            margin-top: 10px;
        }

        .footer {
            font-size: 0.8em;
            text-align: center;
            margin-top: 20px;
        }

        /* Ocultar elementos en la impresión que no sirven */
        @media print {
            .no-print {
                display: none !important;
            }

            @page {
                size: 80mm auto;
                margin: 0;
            }

            body {
                width: 80mm;
                margin: 0;
                padding: 0;
            }

            .ticket-container {
                width: 75mm;
                margin: 0;
                padding: 2mm;
            }
        }

        /* Utilidad para la vista en pantalla */
        .actions {
            margin-top: 30px;
            text-align: center;
        }

        .btn {
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
        }

        .btn-back {
            background-color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="ticket-container">
        <div class="header">PARQUEADERO MVP</div>
        <div class="subheader">
            Nit: 123456789-0<br>
            Dirección: Calle 123 #45-67<br>
            Fecha Impresión: {{ \Carbon\Carbon::now()->format('d/m/Y h:i:s A') }}
        </div>

        <div class="plate-container">
            {{ $ticket->plate }} <br>
            <span style="font-size: 0.5em; font-weight: normal;">{{ $ticket->vehicleType->name }}</span>
        </div>

        <div class="details">
            <div class="details-row">
                <span>Ingreso:</span>
                <span>{{ $ticket->entry_at->format('d/m/Y h:i A') }}</span>
            </div>
            <div class="details-row">
                <span>Salida:</span>
                <span>{{ $ticket->exit_at ? $ticket->exit_at->format('d/m/Y h:i A') : 'N/A' }}</span>
            </div>
            <div class="details-row">
                <span>Tiempo:</span>
                <span>{{ $ticket->exit_at ? $ticket->entry_at->diffForHumans($ticket->exit_at, true) : '-' }}</span>
            </div>
            <div class="details-row">
                <span>Atendido por:</span>
                <span>{{ $ticket->user->name ?? 'Operario' }}</span>
            </div>
        </div>

        <div class="total-container">
            TOTAL: ${{ number_format($ticket->total_amount, 2) }}
        </div>

        <div class="footer">
            ¡Gracias por preferirnos!<br>
            Vuelva pronto.
        </div>
    </div>

    <!-- Controles para la vista web que no se imprimen -->
    <div class="actions no-print">
        <button class="btn" onclick="window.print()">🖨️ Imprimir Recibo</button>
        <a href="{{ route('parking.index') }}" class="btn btn-back">⬅️ Volver al Inicio</a>
    </div>

    <!-- Auto imprimir al cargar (opcional, para agilizar) -->
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>
