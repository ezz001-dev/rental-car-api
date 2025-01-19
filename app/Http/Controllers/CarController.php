<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Car;
use Illuminate\Http\JsonResponse;


class CarController extends Controller
{
    //

    public function index() : JsonResponse
    {
        $cars = Car::all();

        // Return response as JSON
        return response()->json([
            'success' => true,
            'data' => $cars,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        // Ambil parameter pencarian dari query string
        $brand = $request->query('brand'); // Filter berdasarkan merek
        $model = $request->query('model'); // Filter berdasarkan model
        $available = $request->query('available'); // Filter berdasarkan ketersediaan

        // Query ke database dengan kondisi pencarian
        $cars = Car::query()
            ->when($brand, function ($query) use ($brand) {
                $query->where('brand', 'LIKE', "%{$brand}%");
            })
            ->when($model, function ($query) use ($model) {
                $query->where('model', 'LIKE', "%{$model}%");
            })
            ->when(!is_null($available), function ($query) use ($available) {
                $query->where('is_available', filter_var($available, FILTER_VALIDATE_BOOLEAN));
            })
            ->get();

        // Kembalikan hasil dalam format JSON
        return response()->json([
            'success' => true,
            'data' => $cars,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        // dd($request);
        // Validasi data input

        $validatedData = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'license_plate' => 'required|string|max:20|unique:cars,license_plate',
            'rental_price_per_day' => 'required|numeric|min:0',
            'is_available' => 'boolean',
        ]);

        // dd($validatedData);

        // Tambahkan data ke database
        $car = Car::create([
            'brand' => $validatedData['brand'],
            'model' => $validatedData['model'],
            'license_plate' => $validatedData['license_plate'],
            'rental_price_per_day' => $validatedData['rental_price_per_day'],
            'is_available' => $validatedData['is_available'] ?? true, // Default ke tersedia jika tidak diisi
        ]);

        // Kembalikan respons JSON
        return response()->json([
            'success' => true,
            'message' => 'Mobil berhasil ditambahkan!',
            'data' => $car,
        ]);
    }
}
