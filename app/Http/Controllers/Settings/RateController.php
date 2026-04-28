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
        $parkingId = session('active_parking_id');
        if (!$parkingId) return redirect()->route('settings.parkings.index')->with('error', 'Selecciona un parqueadero.');

        $rates = Rate::where('parking_id', $parkingId)->with('vehicleType')->get();
        $vehicleTypes = VehicleType::where('parking_id', $parkingId)->get();
        
        return view('settings.rates.index', compact('rates', 'vehicleTypes'));
    }

    public function store(Request $request)
    {
        $parkingId = session('active_parking_id');

        $request->validate([
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'price_per_hour' => 'required|numeric|min:0',
            'fraction_price' => 'required|numeric|min:0',
        ]);

        Rate::create([
            'vehicle_type_id' => $request->vehicle_type_id,
            'price_per_hour' => $request->price_per_hour,
            'fraction_price' => $request->fraction_price,
            'parking_id' => $parkingId,
        ]);

        return redirect()->route('settings.rates.index')->with('success', 'Tarifa creada.');
    }

    public function update(Request $request, Rate $rate)
    {
        $request->validate([
            'price_per_hour' => 'required|numeric|min:0',
            'fraction_price' => 'required|numeric|min:0',
        ]);

        $rate->update($request->only(['price_per_hour', 'fraction_price']));

        return redirect()->route('settings.rates.index')->with('success', 'Tarifa actualizada.');
    }

    public function destroy(Rate $rate)
    {
        $rate->delete();
        return redirect()->route('settings.rates.index')->with('success', 'Tarifa eliminada.');
    }
}
