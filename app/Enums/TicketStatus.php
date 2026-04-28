<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'OPEN';
    case Paid = 'PAID';
    case Cancelled = 'CANCELLED';

    public function label(): string
    {
        return match($this) {
            self::Open => 'Pendiente',
            self::Paid => 'Pagado',
            self::Cancelled => 'Cancelado',
        };
    }
}
