<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChannelResource;
use App\Models\Channel;
use App\Models\Subscriber;
use Illuminate\Http\Request;

class ChannelSubscriberController extends Controller
{
    public function createChannel()
    {
        $user_id = auth()->user()->id;
        $channel_name = uniqid('channel_');
        $channel = Channel::create([
            'name' => $channel_name
        ]);



        if ($channel) {
            Subscriber::create([
                'channel_id' => $channel->id,
                'user_id' => $user_id
            ]);
            return ChannelResource::make($channel);
        }
        return response('Failed.', 400);
    }
}
