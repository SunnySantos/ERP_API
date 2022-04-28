<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChannelResource;
use App\Models\Channel;
use App\Models\Subscriber;

class ChannelSubscriberController extends Controller
{
    public function createChannel()
    {
        $channel = Channel::create([
            'name' => uniqid('channel_')
        ]);

        if ($channel) {
            Subscriber::create([
                'channel_id' => $channel->id,
                'user_id' => auth()->id()
            ]);
            return ChannelResource::make($channel);
        }
        return response('Failed.', 400);
    }
}
