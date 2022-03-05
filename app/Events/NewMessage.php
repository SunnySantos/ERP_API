<?php

namespace App\Events;

use App\Models\Channel as ModelsChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class NewMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message, $channel_name;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($message, $channel_name)
    {

        $this->message = $message;
        $this->channel_name = $channel_name;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel($this->channel_name);
    }

    public function broadcastAs()
    {
        return 'msg';
    }

    public function broadcastWith()
    {
        return ['messages' => ModelsChannel::join('messages', 'channels.id', '=', 'messages.channel_id')
            ->select(
                'messages.message',
                'messages.image',
                'messages.user_id'
            )
            ->where('channels.name', $this->channel_name)
            ->get()];
    }
}
