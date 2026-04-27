<?php

namespace App\Repositories;

use App\Models\Ticket;
use App\Enums\TicketStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TicketRepository
{
    /**
     * Get active tickets (PENDING)
     */
    public function getActiveTickets()
    {
        return Ticket::with('vehicleType', 'user')
            ->where('status', TicketStatus::PENDING)
            ->orderBy('entry_at', 'desc')
            ->get();
    }

    /**
     * Find an active ticket by plate
     */
    public function findActiveTicketByPlate(string $plate): ?Ticket
    {
        return Ticket::where('plate', $plate)
            ->where('status', TicketStatus::PENDING)
            ->first();
    }

    /**
     * Get daily report summarizing totals 
     */
    public function getDailyReport(Carbon $date = null)
    {
        $date = $date ?? Carbon::today();

        return Ticket::select('user_id', DB::raw('SUM(total_amount) as total_recaudado'), DB::raw('COUNT(id) as total_tickets'))
            ->with('user')
            ->where('status', TicketStatus::PAID)
            ->whereDate('exit_at', $date)
            ->groupBy('user_id')
            ->get();
    }

    /**
     * Get recent paid tickets
     */
    public function getRecentPaidTickets($limit = 10)
    {
        return Ticket::with('vehicleType')
            ->where('status', TicketStatus::PAID)
            ->orderBy('exit_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
