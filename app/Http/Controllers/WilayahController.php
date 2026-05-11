<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class WilayahController extends Controller
{
    public function provinces(): JsonResponse
    {
        $response = Http::get('https://wilayah.id/api/provinces.json');

        if ($response->failed()) {
            return response()->json(['data' => []], 500);
        }

        return response()->json($response->json());
    }

    public function regencies(string $provinceCode): JsonResponse
    {
        $response = Http::get("https://wilayah.id/api/regencies/{$provinceCode}.json");

        if ($response->failed()) {
            return response()->json(['data' => []], 500);
        }

        return response()->json($response->json());
    }
}
