<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Models\Parking;
use App\Models\Rate;
use App\Models\Ticket;
use App\Models\VehicleType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParkingController extends Controller
{
    private function getActiveParking()
    {
        $parkingId = session('active_parking_id');
        if (!$parkingId) {
            return null;
        }
        return Parking::find($parkingId);
    }

    public function index()
    {
        $parking = $this->getActiveParking();
        $user = auth()->user();
        
        if (!$parking) {
            // Auto-selección si solo tiene acceso a un parqueadero
            $availableParkings = collect();
            if ($user->role === 'super-admin') {
                $availableParkings = Parking::all();
            } elseif ($user->role === 'admin') {
                $availableParkings = Parking::where('admin_id', $user->id)->get();
            } else {
                $availableParkings = $user->parkings;
            }

            if ($availableParkings->count() === 1) {
                $p = $availableParkings->first();
                session(['active_parking_id' => $p->id, 'active_parking_name' => $p->name]);
                return redirect()->route('parking.index');
            }

            return redirect()->route('settings.parkings.index')->with('error', 'Por favor selecciona una sede para trabajar.');
        }

        $vehicleTypes = VehicleType::where('parking_id', $parking->id)
            ->withCount(['tickets' => function($query) {
                $query->where('status', TicketStatus::Open);
            }])
            ->get();

        $tickets = Ticket::where('parking_id', $parking->id)
            ->where('status', TicketStatus::Open)
            ->with(['vehicleType', 'user'])
            ->latest()
            ->get();

        $recentPayments = Ticket::where('parking_id', $parking->id)
            ->where('status', TicketStatus::Paid)
            ->whereDate('exit_at', Carbon::today())
            ->latest('exit_at')
            ->take(5)
            ->get();

        return view('parking.index', compact('vehicleTypes', 'tickets', 'recentPayments'));
    }

    public function entry(Request $request)
    {
        $parking = $this->getActiveParking();
        if (!$parking) return back()->with('error', 'No hay parqueadero activo.');

        $request->validate([
            'plate' => 'required|string|max:10',
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'entry_at' => 'nullable|date',
        ]);

        // Check capacity
        $type = VehicleType::findOrFail($request->vehicle_type_id);
        $activeCount = Ticket::where('parking_id', $parking->id)
            ->where('vehicle_type_id', $type->id)
            ->where('status', TicketStatus::Open)
            ->count();

        if ($activeCount >= $type->capacity) {
            return back()->with('error', 'Capacidad máxima alcanzada para este tipo de vehículo.');
        }

        $ticket = Ticket::create([
            'plate' => strtoupper($request->plate),
            'vehicle_type_id' => $request->vehicle_type_id,
            'entry_at' => $request->entry_at ? Carbon::parse($request->entry_at) : now(),
            'status' => TicketStatus::Open,
            'user_id' => auth()->id(),
            'parking_id' => $parking->id,
        ]);

        return redirect()->route('parking.receipt', $ticket)->with('success', 'Vehículo ingresado.');
    }

    public function checkoutPreview(Ticket $ticket)
    {
        if ($ticket->status !== TicketStatus::Open) {
            return response()->json(['error' => 'Este tiquete ya fue procesado.'], 400);
        }

        $exitAt = now();
        $durationInMinutes = $ticket->entry_at->diffInMinutes($exitAt);
        
        $rate = Rate::where('parking_id', $ticket->parking_id)
            ->where('vehicle_type_id', $ticket->vehicle_type_id)
            ->first();

        if (!$rate) {
            return response()->json(['error' => 'No hay tarifas configuradas para este vehículo en este parqueadero.'], 400);
        }

        $total = $this->calculateTotal($durationInMinutes, $rate);

        return response()->json([
            'id' => $ticket->id,
            'plate' => $ticket->plate,
            'entry_at' => $ticket->entry_at->format('d/m/Y h:i A'),
            'exit_at' => $exitAt->format('d/m/Y h:i A'),
            'time' => $ticket->entry_at->diffForHumans($exitAt, true),
            'amount' => number_format($total, 0),
        ]);
    }

    private function calculateTotal($minutes, $rate)
    {
        if ($minutes <= 0) return 0;
        
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        $total = $hours * $rate->price_per_hour;
        
        if ($remainingMinutes > 0) {
            $total += $rate->fraction_price;
        }

        return $total;
    }

    public function pay(Request $request, Ticket $ticket)
    {
        $exitAt = now();
        $durationInMinutes = $ticket->entry_at->diffInMinutes($exitAt);
        $rate = Rate::where('parking_id', $ticket->parking_id)
            ->where('vehicle_type_id', $ticket->vehicle_type_id)
            ->first();

        $total = $this->calculateTotal($durationInMinutes, $rate);

        $ticket->update([
            'exit_at' => $exitAt,
            'total_amount' => $total,
            'payment_method' => $request->payment_method ?? 'Efectivo',
            'status' => TicketStatus::Paid,
        ]);

        return redirect()->route('parking.receipt', $ticket)->with('success', 'Pago registrado exitosamente.');
    }

    public function cancel(Ticket $ticket)
    {
        $ticket->update(['status' => TicketStatus::Cancelled]);
        return back()->with('success', 'Tiquete anulado.');
    }

    public function receipt(Ticket $ticket)
    {
        return view('parking.receipt', compact('ticket'));
    }

    public function report()
    {
        $parking = $this->getActiveParking();
        if (!$parking) return redirect()->route('settings.parkings.index');

        $reportData = Ticket::where('parking_id', $parking->id)
            ->where('status', TicketStatus::Paid)
            ->whereDate('exit_at', Carbon::today())
            ->with('user')
            ->select('user_id', DB::raw('count(*) as total_tickets'), DB::raw('sum(total_amount) as total_recaudado'))
            ->groupBy('user_id')
            ->get();

        return view('parking.report', compact('reportData'));
    }
}
