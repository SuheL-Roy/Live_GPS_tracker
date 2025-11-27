<?php

namespace App\Http\Controllers;

use App\Events\LocationUpdated;
use App\Jobs\BroadcastLocationUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class LocationController extends Controller
{
    public function update(Request $request)
    {


        // 1️⃣ Validate request
        $data = $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $userId = auth()->id(); // Logged in user ID

        // 2️⃣ Store in Redis
        Redis::hset('user_locations', $userId, json_encode($data));


        Redis::expire('user_locations', 3600);

        // 3️⃣ Dispatch job for MySQL & optional WebSocket
        BroadcastLocationUpdate::dispatch($userId, $data);

        // 4️⃣ Return response
        return response()->json([
            'status'  => 'success',
            'message' => 'Location updated successfully',
        ]);
    }
}
