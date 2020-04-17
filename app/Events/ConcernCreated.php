<?php

namespace App\Events;

use App\Models\Concern;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConcernCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $concern;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Concern $concern)
    {
        $this->concern = $concern;
    }
}
