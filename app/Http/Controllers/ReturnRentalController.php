<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Car;
use App\Models\Rental;
use App\Models\ReturnRental as CarReturn;
use Carbon\Carbon;

class ReturnRentalController extends Controller
{

    // Fungsi untuk mengembalikan mobil
    public function returnCar(Request $request)
    {
        // Validasi input
        $request->validate([
            'car_plate' => 'required|string|exists:cars,license_plate',
        ]);

        // Cari rental berdasarkan mobil dan pengguna
        $car = Car::where('license_plate', $request->car_plate)->first();
        $rental = Rental::where('car_id', $car->id)
                        ->where('user_id', auth()->id())
                        ->whereNull('return_date') // Pastikan mobil belum dikembalikan
                        ->first();

        if (!$rental) {
            return response()->json(['message' => 'Mobil tidak ditemukan dalam peminjaman Anda.'], 400);
        }

        // Hitung durasi sewa dan biaya
        $startDate = Carbon::parse($rental->start_date);
        $returnDate = Carbon::now();
        $duration = $startDate->diffInDays($returnDate);

        $totalAmount = $car->rental_price_per_day * $duration;

        // Simpan data pengembalian
        $carReturn = CarReturn::create([
            'rental_id' => $rental->id,
            'return_date' => $returnDate,
            'total_amount' => $totalAmount,
        ]);

        // Update status rental
        $rental->update(['end_date' => $returnDate]);

        return response()->json([
            'message' => 'Mobil berhasil dikembalikan.',
            'data' => $carReturn,
        ], 200);
    }

    // Fungsi untuk melihat daftar pengembalian mobil
    public function userReturns()
    {
        $returns = CarReturn::whereHas('rental', function($query) {
            $query->where('user_id', auth()->id());
        })->with('rental.car')->get();

        return response()->json(['returns' => $returns], 200);
    }
}
