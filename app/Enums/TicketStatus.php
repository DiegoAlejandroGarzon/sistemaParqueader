<?php

namespace App\Enums;

enum TicketStatus: string
{
    case PENDING = 'PENDING';
    case PAID = 'PAID';
    case CANCELLED = 'CANCELLED';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pendiente',
            self::PAID => 'Pagado',
            self::CANCELLED => 'Cancelado',
        };
    }
}
