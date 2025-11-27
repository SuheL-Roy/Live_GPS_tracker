<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $location;

    public function __construct($userId, $location)
    {
        $this->userId = $userId;
        $this->location = $location;
    }

    public function broadcastOn()
    {
        return new Channel('locations');
    }

    public function broadcastAs()
    {
        return 'location.updated';
    }
    
    public function broadcastWith()
    {
        return [
            'userId' => $this->userId,
            'location' => $this->location,
            'timestamp' => now()->toDateTimeString()
        ];
    }
}