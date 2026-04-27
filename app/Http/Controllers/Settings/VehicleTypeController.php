<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\VehicleType;
use Illuminate\Http\Request;

class VehicleTypeController extends Controller
{
    public function index()
    {
        $vehicleTypes = VehicleType::all();
        return view('settings.vehicle-types.index', compact('vehicleTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:vehicle_types,name',
            'icon' => 'nullable|string|max:5',
            'capacity' => 'required|integer|min:0',
        ]);

        VehicleType::create($request->all());

        return back()->with('success', 'Tipo de vehículo creado correctamente.');
    }

    public function update(Request $request, VehicleType $vehicleType)
    {
        $request->validate([
            'name' => 'required|string|unique:vehicle_types,name,' . $vehicleType->id,
            'icon' => 'nullable|string|max:5',
            'capacity' => 'required|integer|min:0',
        ]);

        $vehicleType->update($request->all());

        return back()->with('success', 'Tipo de vehículo actualizado.');
    }

    public function destroy(VehicleType $vehicleType)
    {
        if ($vehicleType->rates()->count() > 0) {
            return back()->with('error', 'No se puede eliminar porque tiene tarifas asociadas.');
        }

        $vehicleType->delete();
        return back()->with('success', 'Tipo de vehículo eliminado.');
    }
}
