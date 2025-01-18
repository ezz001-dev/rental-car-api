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
}
