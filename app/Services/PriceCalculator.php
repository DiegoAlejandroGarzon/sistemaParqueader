<?php

namespace App\Services;

use App\Models\Rate;
use Carbon\Carbon;

class PriceCalculator
{
    /**
     * Calculate the total price based on entry time, exit time and the vehicle type rate.
     */
    public function calculate(Carbon $entryAt, Carbon $exitAt, Rate $rate): float
    {
        $diffInMinutes = $entryAt->diffInMinutes($exitAt);

        // Options:
        // 1. Give some grace minutes (e.g. 10 minutes)
        if ($diffInMinutes <= 5) {
            return 0; // 5 minutos de gracia
        }

        // 2. Logic: if fraction_price is set, we might use it. 
        // For simplicity: calculate total hours and remaining fractions.
        // Let's implement a standard rule: 
        // Full hour price applied per hour. The remaining minutes apply the fraction price per fraction (e.g. every 15 or 30 min? Let's say fraction = 1 hour fraction if no other specification, but a simpler one is charging per minute based on fraction or charging whole hours and if it passes 10 mins charging the fraction)
        
        // Let's make a solid simple MVP rule:
        // 1 fraction is less than 1 hour.
        
        $hours = floor($diffInMinutes / 60);
        $remainingMinutes = $diffInMinutes % 60;

        $total = $hours * $rate->price_per_hour;

        // If there are remaining minutes, charge a fraction
        if ($remainingMinutes > 0) {
            if ($rate->fraction_price) {
                // Example: We charge one fraction for any remaining time
                $total += $rate->fraction_price;
            } else {
                // If no fraction price, charge a full hour
                $total += $rate->price_per_hour;
            }
        }

        return (float) $total;
    }
}
