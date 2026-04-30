<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo - {{ $ticket->plate }}</title>
    <style>
        /* Estilos optimizados para impresora térmica de 80mm */
        body {
            font-family: 'Tahoma', 'Verdana', 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            width: 100%;
            color: #000;
            -webkit-font-smoothing: none;
            /* Intentar obtener bordes más nítidos en impresión */
        }

        .ticket-container {
            width: 75mm;
            margin: 0 auto;
            padding: 5px;
            text-align: center;
            box-sizing: border-box;
            font-size: 15px;
            /* Aumentado ligeramente */
        }

        .header-logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-bottom: 8px;
        }

        .header-logos span {
            font-size: 40px;
            font-weight: bold;
        }

        .header-text {
            font-weight: bold;
            font-size: 1.15em;
            margin-bottom: 12px;
            line-height: 1.1;
        }

        .header-text p {
            margin: 1px 0;
        }

        .ticket-info {
            font-size: 1.5em;
            font-weight: bold;
            margin: 12px 0;
            line-height: 1.1;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 5px 0;
        }

        .barcode-container {
            margin: 15px 0;
            text-align: center;
        }

        .barcode-container svg {
            width: 85%;
            height: 50px;
        }

        .details-table {
            width: 100%;
            text-align: left;
            margin-bottom: 12px;
            font-size: 1.1em;
        }

        .details-table td {
            padding: 3px 0;
        }

        .details-table .label {
            width: 35%;
            font-weight: normal;
        }

        .details-table .value {
            text-align: right;
            font-weight: bold;
        }

        .footer {
            font-size: 1.0em;
            /* Aumentado para mayor legibilidad */
            text-align: center;
            margin-top: 15px;
            line-height: 1.2;
        }

        .footer p {
            margin: 2px 0;
        }

        .terms {
            font-size: 0.85em;
            /* Aumentado significativamente */
            text-align: justify;
            margin-top: 10px;
            line-height: 1.1;
            font-weight: normal;
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
        <!-- Logos Superiores -->
        <div class="header-logos">
            <span>🅿️</span>
            <span>🚗</span>
            <span>🏍️</span>
        </div>

        <div class="header-text">
            <p>PARQUEADERO LA 12</p>
            <p>NIT: 14231632 -7</p>
            <p>NO RESPONSABLE DE IVA</p>
            <p>CRA 2 # 11-92</p>
            <p>TELEFONO: 276 1700</p>
            <p>HORARIO:</p>
            <p>LUN A SAB: 7:00 AM a 9:00 PM</p>
            <p>DOM y FEST: 9:00 AM a 7:00 PM</p>
        </div>

        <div class="ticket-info">
            <p style="margin:0;">Recibo No: {{ number_format($ticket->id, 0, ',', '.') }}</p>
            <p style="margin:0;">Placa: {{ strtoupper($ticket->plate) }}</p>
        </div>

        <div class="barcode-container">
            @php
                $generator = new \Picqer\Barcode\BarcodeGeneratorSVG();
                echo $generator->getBarcode($ticket->id, $generator::TYPE_CODE_128, 2, 50, 'black');
            @endphp
        </div>

        <table class="details-table">
            <tr>
                <td class="label">Tarifa:</td>
                <td class="value">{{ strtoupper($ticket->vehicleType->name) }} X HORA</td>
            </tr>
            <tr>
                <td class="label">Entrada:</td>
                <td class="value">
                    {{ strtoupper($ticket->entry_at->locale('es')->isoFormat('hh:mm A YYYY-MM-DD ddd')) }}</td>
            </tr>
            <tr>
                <td class="label">Cajero:</td>
                <td class="value">{{ strtoupper($ticket->user->name ?? 'SISTEMA') }}</td>
            </tr>
            @if($ticket->notes)
            <tr>
                <td class="label">Notas:</td>
                <td class="value" style="font-size: 0.9em;">{{ strtoupper($ticket->notes) }}</td>
            </tr>
            @endif
        </table>

        <div class="footer">
            <p style="font-weight: bold;">POLIZA # 1-250006862 SEGUROS MUNDIAL</p>
            <p style="font-weight: bold;">RECLAMACIONES: TEL: 285 5600</p>
            <p style="font-weight: bold; margin-top:5px;">REGLAMENTO</p>
            <div class="terms">
                El vehículo se entregará al portador del recibo. * No aceptamos ordenes telefónicas ni escritas. *
                Retirado el vehículo, no aceptamos ningún tipo de reclamo. * No respondemos por objetos dejados en el
                vehiculo. * No respondemos por la perdida, deterioro o daños ocurridos como consecuencia de incendio,
                terremoto, asonada, revolución, u otras causas similares. * El conductor debe asegurarse que el vehículo
                esta bien asegurado. * No respondemos por daños al vehículo causados por terceros.
            </div>
            <p style="margin-top: 10px;">www.softluciones.co</p>
        </div>
    </div>

    <!-- Controles para la vista web que no se imprimen -->
    <div class="actions no-print">
        <button class="btn" onclick="window.print()">🖨️ Imprimir Recibo</button>
        <a href="{{ route('parking.index') }}" class="btn btn-back">⬅️ Volver al Inicio</a>
    </div>

    <!-- Auto imprimir al cargar -->
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>
