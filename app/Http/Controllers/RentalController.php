<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

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

        $validatedData['start_date'] = Carbon::parse($request->start_date)->toDateTimeString();
        $validatedData['end_date'] = Carbon::parse($request->end_date)->toDateTimeString();

        $startDate = Carbon::parse($validatedData['start_date']);
        $endDate = Carbon::parse($validatedData['end_date']);
        $days = $startDate->diffInDays($endDate);



        // Periksa ketersediaan mobil
        $car = Car::findOrFail($validatedData['car_id']);
        if (!$car->is_available) {
            return response()->json([
                'success' => false,
                'message' => 'Mobil tidak tersedia untuk disewa.',
            ], 400);
        }

        // Hitung total harga (harga per hari * jumlah hari)
        $totalPrice = $car->rental_price_per_day * $days;

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


    public function userRentals(Request $request)
    {
        // Dapatkan pengguna yang sedang login
        $user = $request->user();

        // dd($user);

        // Ambil data rental yang terkait dengan pengguna tersebut
        $rentals = Rental::with('car') // Include informasi mobil
            ->where('user_id', $user->id)
            ->whereNull('return_date') // Hanya yang belum dikembalikan
            ->get();

         // Tambahkan informasi jumlah hari peminjaman dan biaya total secara dinamis
        $rentals = $rentals->map(function ($rental) {
        // Hitung jumlah hari peminjaman dari start_date ke end_date
        $daysRented = \Carbon\Carbon::parse($rental->start_date)
            ->diffInDays(\Carbon\Carbon::parse($rental->end_date));

        // Tambahkan properti dinamis
        $rental->days_rented = $daysRented;
        $rental->total_cost = $daysRented * $rental->car->rental_price_per_day;

        return $rental;
        });


        // Kembalikan daftar rental sebagai JSON
        return response()->json([
            'message' => 'Daftar mobil yang sedang disewa.',
            'rentals' => $rentals,
        ], 200);
    }


}
