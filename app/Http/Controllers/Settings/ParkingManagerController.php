<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Parking;
use App\Models\User;
use Illuminate\Http\Request;

class ParkingManagerController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->role === 'super-admin') {
            $parkings = Parking::with('admin')->latest()->get();
        } else {
            $parkings = Parking::where('admin_id', $user->id)->latest()->get();
        }

        return view('settings.parkings.index', compact('parkings'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        if ($user->role !== 'admin' && $user->role !== 'super-admin') {
            abort(403);
        }

        // Check limit for admins
        if ($user->role === 'admin' && $user->max_parkings) {
            $currentCount = Parking::where('admin_id', $user->id)->count();
            if ($currentCount >= $user->max_parkings) {
                return back()->with('error', "Has alcanzado el límite de {$user->max_parkings} parqueaderos permitidos.");
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'nit' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'schedule' => 'nullable|string|max:255',
            'admin_id' => 'nullable|exists:users,id',
        ]);

        Parking::create([
            'name' => $request->name,
            'nit' => $request->nit,
            'address' => $request->address,
            'phone' => $request->phone,
            'schedule' => $request->schedule,
            'admin_id' => $user->role === 'super-admin' ? $request->admin_id : $user->id,
        ]);

        return redirect()->route('settings.parkings.index')->with('success', 'Parqueadero creado exitosamente.');
    }

    public function update(Request $request, Parking $parking)
    {
        $user = auth()->user();
        
        if ($user->role !== 'super-admin' && $parking->admin_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'nit' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'schedule' => 'nullable|string|max:255',
        ]);

        $parking->update($request->only(['name', 'nit', 'address', 'phone', 'schedule']));

        return redirect()->route('settings.parkings.index')->with('success', 'Parqueadero actualizado.');
    }

    public function destroy(Parking $parking)
    {
        $user = auth()->user();
        
        if ($user->role !== 'super-admin' && $parking->admin_id !== $user->id) {
            abort(403);
        }

        $parking->delete();
        return redirect()->route('settings.parkings.index')->with('success', 'Parqueadero eliminado.');
    }

    public function assignOperators(Request $request, Parking $parking)
    {
        $user = auth()->user();
        if ($user->role !== 'super-admin' && $parking->admin_id !== $user->id) {
            abort(403);
        }

        $parking->operators()->sync($request->operators);
        return back()->with('success', 'Operadores asignados correctamente.');
    }
}
