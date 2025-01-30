<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MapController extends Controller
{
    protected $googleMapsKey;

    public function __construct()
    {
        $this->googleMapsKey = config('services.google.maps_key');
    }

    public function getLocationDetails(Request $request)
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $request->location,
            'key' => $this->googleMapsKey
        ]);

        return response()->json($response->json());
    }

    public function getNearbyPlaces(Request $request)
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/place/nearbysearch/json', [
            'location' => "{$request->lat},{$request->lng}",
            'radius' => $request->radius ?? 5000,
            'type' => $request->type ?? 'tourist_attraction',
            'key' => $this->googleMapsKey
        ]);

        return response()->json($response->json());
    }
} 