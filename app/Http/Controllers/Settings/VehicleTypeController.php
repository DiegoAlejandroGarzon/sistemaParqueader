<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\VehicleType;
use Illuminate\Http\Request;

class VehicleTypeController extends Controller
{
    public function index()
    {
        $parkingId = session('active_parking_id');
        if (!$parkingId) return redirect()->route('settings.parkings.index')->with('error', 'Selecciona un parqueadero.');

        $vehicleTypes = VehicleType::where('parking_id', $parkingId)->get();
        return view('settings.vehicle-types.index', compact('vehicleTypes'));
    }

    public function store(Request $request)
    {
        $parkingId = session('active_parking_id');
        
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:10',
            'capacity' => 'required|integer|min:1',
        ]);

        VehicleType::create([
            'name' => $request->name,
            'icon' => $request->icon,
            'capacity' => $request->capacity,
            'parking_id' => $parkingId,
        ]);

        return redirect()->route('settings.vehicle-types.index')->with('success', 'Tipo de vehículo creado.');
    }

    public function update(Request $request, VehicleType $vehicleType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:10',
            'capacity' => 'required|integer|min:1',
        ]);

        $vehicleType->update($request->only(['name', 'icon', 'capacity']));

        return redirect()->route('settings.vehicle-types.index')->with('success', 'Tipo de vehículo actualizado.');
    }

    public function destroy(VehicleType $vehicleType)
    {
        $vehicleType->delete();
        return redirect()->route('settings.vehicle-types.index')->with('success', 'Tipo de vehículo eliminado.');
    }
}
