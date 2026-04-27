<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\VehicleType;
use App\Enums\TicketStatus;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __invoke()
    {
        // Totales de Hoy
        $activeTicketsCount = Ticket::where('status', TicketStatus::PENDING)->count();
        $totalRevenueToday = Ticket::where('status', TicketStatus::PAID)
            ->whereDate('exit_at', Carbon::today())
            ->sum('total_amount');

        // Data para Gráfica: Recaudado por Vehículo (Histórico o Mes actual, usemos histórico para ver datos)
        $revenueByVehicle = Ticket::where('status', TicketStatus::PAID)
            ->join('vehicle_types', 'tickets.vehicle_type_id', '=', 'vehicle_types.id')
            ->selectRaw('vehicle_types.name as label, SUM(tickets.total_amount) as total')
            ->groupBy('vehicle_types.name')
            ->get();

        // Data para Gráfica: Picos de Entrada por Hora
        $entriesByHour = Ticket::selectRaw('HOUR(entry_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return view('dashboard', compact(
            'activeTicketsCount',
            'totalRevenueToday',
            'revenueByVehicle',
            'entriesByHour'
        ));
    }
}
