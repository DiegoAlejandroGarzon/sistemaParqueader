<?php

namespace App\Http\Controllers;

use App\Models\Parking;
use Illuminate\Http\Request;

class ActiveParkingController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'parking_id' => 'required|exists:parkings,id',
        ]);

        $parking = Parking::findOrFail($request->parking_id);
        $user = auth()->user();

        // Check if user has access
        if ($user->role === 'super-admin' || 
            ($user->role === 'admin' && $parking->admin_id === $user->id) ||
            ($user->role === 'operator' && $parking->operators()->where('user_id', $user->id)->exists())) {
            
            session(['active_parking_id' => $parking->id]);
            session(['active_parking_name' => $parking->name]);
            
            return back()->with('success', "Ahora gestionando: {$parking->name}");
        }

        return back()->with('error', 'No tienes acceso a este parqueadero.');
    }
}
