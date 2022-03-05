<?php

namespace App\Http\Controllers;

use App\Events\NewMessage;
use App\Models\Channel;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Message;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{

    public function getMessages(Request $request)
    {
        $request->validate([
            'channel_name' => 'required|string|exists:channels,name',
        ]);

        $channel_name = $request->input('channel_name');
        event(new NewMessage('', $channel_name));
    }

    public function newChannelMessage(Request $request)
    {
        $request->validate([
            'channel_name' => 'nullable|string',
            'message' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $user_id = auth()->user()->id;
        $channel_name = $request->input('channel_name');
        $image = $request->file('image');
        $message = $request->input('message');
        $channel = $this->getChannel($channel_name);

        if ($message || $image) {
            if ($channel) {
                $channel_name = $channel->name;
                $employeeCRM = Employee::select('user_id')
                    ->where('department_id', 2)
                    ->first();
                $this->storeSubscriber($employeeCRM->user_id, $channel);
                $this->storeSubscriber($user_id, $channel);

                Message::create([
                    'channel_id' => $channel->id,
                    'user_id' => $user_id,
                    'message' => $message,
                    'image' => $this->storeImage($image)
                ]);
                event(new NewMessage($message, $channel_name));
                return $channel_name;
            }
        }
    }

    public function userChannels()
    {
        $user_id = auth()->user()->id;
        $channels = Channel::join('subscribers', 'channels.id', '=', 'subscribers.channel_id')
            ->join('messages', 'channels.id', '=', 'messages.channel_id')
            ->select(
                'channels.id',
                'channels.name',
            )
            ->where('subscribers.user_id', $user_id)
            ->distinct()
            ->orderBy('messages.created_at', 'DESC')
            ->get();

        foreach ($channels as $key => $channel) {
            $profile = null;
            $firstname = null;
            $lastname = null;
            $message = $this->getRecentMessage($channel->id);

            $customer = $this->getCustomer($message->user_id);
            $employee = $this->getEmployee($message->user_id);

            if ($customer) {
                $profile = $customer->profile;
                $firstname = $customer->firstname;
                $lastname = $customer->lastname;
            }

            if ($employee) {
                $profile = $employee->profile;
                $firstname = $employee->firstname;
                $lastname = $employee->lastname;
            }

            if ($message) {
                $date = date('Y-m-d', strtotime($message->created_at));
                $date_time = $date;

                if ($date == date('Y-m-d')) {
                    $date_time = date("h:i:s A", strtotime($message->created_at));
                }

                if ($message) {
                    $channels[$key]['profile'] = $profile;
                    $channels[$key]['firstname'] = $firstname;
                    $channels[$key]['lastname'] = $lastname;
                    $channels[$key]['message'] = $message->message;
                    $channels[$key]['date_time'] = $date_time;
                }
            }
        }

        return $channels;
    }

    private function getChannel($channel_name)
    {
        if ($channel_name == '') {
            $channel_name = uniqid('channel_');
            return Channel::create([
                'name' => $channel_name
            ]);
        } else {
            return Channel::where('name', $channel_name)
                ->first();
        }
    }

    private function storeSubscriber($user_id, $channel)
    {
        $subscriber = Subscriber::where('channel_id', $channel->id)
            ->where('user_id', $user_id)
            ->first();
        if (!$subscriber) {
            Subscriber::create([
                'channel_id' => $channel->id,
                'user_id' => $user_id
            ]);
        }
    }

    private function storeImage($image)
    {
        if ($image) {
            $name = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $imgName = time() . '_' . $name . '.' . $image->extension();
            $image->move(public_path('storage/chat_img'), $imgName);
            return $imgName;
        }
        return null;
    }

    private function getRecentMessage($channel_id)
    {
        // $message = Message::where('channel_id', $channel->id)
        //     ->select(
        //         'message',
        //         'created_at'
        //     )
        //     ->orderBy('created_at', 'DESC')
        //     ->first();

        return Channel::find($channel_id)
            ->messages()
            ->select(
                'user_id',
                'message',
                'created_at'
            )
            ->orderBy('created_at', 'DESC')
            ->first();
    }

    private function getCustomer($user_id)
    {
        return Customer::select(
            DB::raw("CONCAT('customer_img/',profile) as profile"),
            'firstname',
            'lastname'
        )
            ->where('user_id', $user_id)
            ->first();
    }

    private function getEmployee($user_id)
    {
        return Employee::select(
            DB::raw("CONCAT('employee_img/',photo) as profile"),
            'firstname',
            'lastname'
        )
            ->where('user_id', $user_id)
            ->first();
    }
}
