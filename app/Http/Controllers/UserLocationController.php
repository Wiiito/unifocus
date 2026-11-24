<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserLocationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

/**
 * Recebe a posição capturada pelo resources/js/location-tracker.js.
 */
class UserLocationController extends Controller
{
    public function store(StoreUserLocationRequest $request): JsonResponse
    {
        $location = $request->user()->locations()->create([
            ...$request->validated(),
            'user_agent' => $request->userAgent() !== null ? Str::limit($request->userAgent(), 255, '') : null,
            'recorded_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'data' => [
                'id' => $location->id,
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'accuracy' => $location->accuracy,
                'source' => $location->source,
                'recorded_at' => $location->recorded_at->toIso8601String(),
            ],
        ], 201);
    }
}
