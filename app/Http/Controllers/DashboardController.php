<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Models\Parking;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user();
        
        // Date filters
        $startDate = $request->input('start_date', Carbon::today()->subDays(7)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));
        
        // Parking filter
        $parkingId = $request->input('parking_id', session('active_parking_id'));
        
        // Get list of parkings for the selector based on role
        if ($user->role === 'super-admin') {
            $parkings = Parking::all();
        } elseif ($user->role === 'admin') {
            $parkings = Parking::where('admin_id', $user->id)->get();
        } else {
            $parkings = $user->parkings;
        }

        // Base Query
        $query = Ticket::where('status', TicketStatus::Paid)
            ->whereDate('exit_at', '>=', $startDate)
            ->whereDate('exit_at', '<=', $endDate);

        if ($parkingId) {
            $query->where('parking_id', $parkingId);
        } else if ($user->role !== 'super-admin') {
            $query->whereIn('parking_id', $parkings->pluck('id'));
        }

        // Statistics
        $totalRevenue = (clone $query)->sum('total_amount');
        $totalTickets = (clone $query)->count();
        
        // Daily revenue for chart
        $dailyRevenue = (clone $query)
            ->select(DB::raw('DATE(exit_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Revenue by Payment Method
        $paymentMethods = (clone $query)
            ->select('payment_method', DB::raw('SUM(total_amount) as total'))
            ->groupBy('payment_method')
            ->get();

        // Revenue by Vehicle Type
        $vehicleTypes = (clone $query)
            ->with('vehicleType')
            ->select('vehicle_type_id', DB::raw('SUM(total_amount) as total'))
            ->groupBy('vehicle_type_id')
            ->get();

        return view('dashboard', compact(
            'totalRevenue', 
            'totalTickets', 
            'dailyRevenue', 
            'paymentMethods', 
            'vehicleTypes', 
            'parkings', 
            'startDate', 
            'endDate', 
            'parkingId'
        ));
    }
}
