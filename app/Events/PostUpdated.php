<?php

namespace App\Events;

use App\Models\Posts;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostUpdated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    private Posts $post;

    /**
     * PostUpdated constructor.
     */
    public function __construct(Posts $posts)
    {
        $this->post = $posts;
    }

    public function getPost()
    {
        return $this->post;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
