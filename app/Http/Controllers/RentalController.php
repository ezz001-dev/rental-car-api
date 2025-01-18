<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RentalController extends Controller
{
    //
    public function rentCar(Request $request): JsonResponse
    {
        // Validasi input
        $validatedData = $request->validate([
            'car_id' => 'required|exists:cars,id',
            'user_id' => 'required|exists:users,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Periksa ketersediaan mobil
        $car = Car::findOrFail($validatedData['car_id']);
        if (!$car->available) {
            return response()->json([
                'success' => false,
                'message' => 'Mobil tidak tersedia untuk disewa.',
            ], 400);
        }

        // Periksa apakah mobil sudah dipesan pada tanggal yang diminta
        $isCarBooked = Rental::where('car_id', $car->id)
            ->where(function ($query) use ($validatedData) {
                $query->whereBetween('start_date', [$validatedData['start_date'], $validatedData['end_date']])
                      ->orWhereBetween('end_date', [$validatedData['start_date'], $validatedData['end_date']])
                      ->orWhereRaw('? BETWEEN start_date AND end_date', [$validatedData['start_date']])
                      ->orWhereRaw('? BETWEEN start_date AND end_date', [$validatedData['end_date']]);
            })
            ->exists();

        if ($isCarBooked) {
            return response()->json([
                'success' => false,
                'message' => 'Mobil telah dipesan pada tanggal yang diminta.',
            ], 400);
        }

        // Buat peminjaman baru
        $rental = Rental::create([
            'car_id' => $validatedData['car_id'],
            'user_id' => $validatedData['user_id'],
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
        ]);

        // Tandai mobil sebagai tidak tersedia
        $car->update(['available' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Peminjaman berhasil dilakukan!',
            'data' => $rental,
        ]);
    }
}
