<?php

namespace App\Jobs;

use App\Events\LocationUpdated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BroadcastLocationUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $userId;
    protected $location;
    
    public function __construct($userId, $location)
    {
        $this->userId = $userId;
        $this->location = $location;
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        event(new LocationUpdated($this->userId, $this->location));
    }
}
