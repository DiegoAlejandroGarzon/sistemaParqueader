<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Rate;
use App\Models\VehicleType;
use Illuminate\Http\Request;

class RateController extends Controller
{
    public function index()
    {
        $rates = Rate::with('vehicleType')->get();
        $vehicleTypes = VehicleType::all();
        return view('settings.rates.index', compact('rates', 'vehicleTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'price_per_hour' => 'required|numeric|min:0',
            'fraction_price' => 'nullable|numeric|min:0',
        ]);

        Rate::create($request->all());

        return back()->with('success', 'Tarifa configurada correctamente.');
    }

    public function update(Request $request, Rate $rate)
    {
        $request->validate([
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'price_per_hour' => 'required|numeric|min:0',
            'fraction_price' => 'nullable|numeric|min:0',
        ]);

        $rate->update($request->all());

        return back()->with('success', 'Tarifa actualizada.');
    }

    public function destroy(Rate $rate)
    {
        $rate->delete();
        return back()->with('success', 'Tarifa eliminada.');
    }
}
