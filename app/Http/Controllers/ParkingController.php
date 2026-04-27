<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\VehicleType;
use App\Repositories\TicketRepository;
use App\Services\PriceCalculator;
use App\Enums\TicketStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ParkingController extends Controller
{
    protected $repository;
    protected $calculator;

    public function __construct(TicketRepository $repository, PriceCalculator $calculator)
    {
        $this->repository = $repository;
        $this->calculator = $calculator;
    }

    public function index()
    {
        $tickets = $this->repository->getActiveTickets();
        $vehicleTypes = VehicleType::withCount(['tickets' => function ($query) {
            $query->where('status', TicketStatus::PENDING);
        }])->get();

        $recentPayments = Ticket::where('status', TicketStatus::PAID)
            ->with('vehicleType')
            ->orderBy('exit_at', 'desc')
            ->take(10)
            ->get();
        
        return view('parking.index', compact('tickets', 'vehicleTypes', 'recentPayments'));
    }

    public function entry(Request $request)
    {
        $request->validate([
            'plate' => 'required|string|max:10',
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'entry_at' => 'nullable|date',
        ]);

        // Check if there is already an active ticket for this plate
        $activeTicket = $this->repository->findActiveTicketByPlate(strtoupper($request->plate));
        if ($activeTicket) {
            return back()->with('error', 'El vehículo ya se encuentra en el parqueadero.');
        }

        Ticket::create([
            'plate' => strtoupper($request->plate),
            'vehicle_type_id' => $request->vehicle_type_id,
            'entry_at' => $request->filled('entry_at') ? Carbon::parse($request->entry_at) : Carbon::now(),
            'status' => TicketStatus::PENDING,
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', 'Ingreso registrado correctamente.');
    }

    public function checkoutPreview(Ticket $ticket)
    {
        if ($ticket->status !== TicketStatus::PENDING) {
            return response()->json(['error' => 'Ticket no válido o ya pagado.'], 400);
        }

        $exitAt = Carbon::now();
        // Cargar la relación para obtener la tarifa
        $rate = $ticket->vehicleType->rates()->first(); // Asumiendo una tarifa activa (se podría refinar)
        
        if (!$rate) {
            return response()->json(['error' => 'No hay tarifa configurada para este vehículo.'], 400);
        }

        $amount = $this->calculator->calculate($ticket->entry_at, $exitAt, $rate);

        return response()->json([
            'id' => $ticket->id,
            'plate' => $ticket->plate,
            'entry_at' => $ticket->entry_at->format('d/m/Y h:i A'),
            'exit_at' => $exitAt->format('d/m/Y h:i A'),
            'time' => $ticket->entry_at->diffForHumans($exitAt, true),
            'amount' => $amount,
        ]);
    }

    public function pay(Request $request, Ticket $ticket)
    {
        if ($ticket->status !== TicketStatus::PENDING) {
            return back()->with('error', 'Ticket ya pagado o cancelado.');
        }

        $exitAt = Carbon::now();
        $rate = $ticket->vehicleType->rates()->first();
        
        $amount = $this->calculator->calculate($ticket->entry_at, $exitAt, $rate);

        $ticket->update([
            'exit_at' => $exitAt,
            'total_amount' => $amount,
            'status' => TicketStatus::PAID,
            'payment_method' => $request->payment_method ?? 'Efectivo',
        ]);

        // Redirigir al recibo
        return redirect()->route('parking.receipt', $ticket->id);
    }

    public function cancel(Ticket $ticket)
    {
        if ($ticket->status !== TicketStatus::PENDING) {
            return back()->with('error', 'Solo se pueden anular tiquetes pendientes.');
        }

        $ticket->update(['status' => TicketStatus::CANCELLED]);
        return back()->with('success', 'Tiquete anulado correctamente.');
    }

    public function receipt(Ticket $ticket)
    {
        return view('parking.receipt', compact('ticket'));
    }

    public function report()
    {
        $reportData = $this->repository->getDailyReport();
        return view('parking.report', compact('reportData'));
    }
}
