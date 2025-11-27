<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class SyncRedisToMysql extends Command
{
    protected $signature = 'sync:redis-mysql';
    protected $description = 'Periodically sync user location data from Redis to MySQL';

    public function handle()
    {
        $locations = Redis::hgetall('user_locations');

        foreach ($locations as $userId => $jsonLocation) {
            $location = json_decode($jsonLocation, true);

            // Validate keys exist
            if (!isset($location['latitude']) || !isset($location['longitude'])) {
                continue; // skip invalid data
            }

            User::updateOrCreate(
                ['id' => $userId],
                [
                    'latitude'   => $location['latitude'],
                    'longitude'  => $location['longitude'],
                    'updated_at' => now(),
                ]
            );
        }

        $this->info('Redis → MySQL sync done successfully!');
    }
}
