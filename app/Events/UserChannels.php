<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserChannels implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user_channels = [];

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user_channels)
    {
        $this->user_channels = $user_channels;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('user_channels');
    }

    public function broadcastAs()
    {
        return 'channels';
    }

    public function broadcastWith()
    {
        return ['user_channels' => $this->user_channels];
    }
}
