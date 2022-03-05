<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class CheckOutProductController extends Controller
{
    public function checkoutProduct(Request $request, $id)
    {
        $request->validate([
            'ordered_at' => 'required'
        ]);

        $order = Order::where('id', $id)
            ->get()->first();

        if ($order->ordered_at === null) {
            $order->ordered_at = $request->ordered_at;
            $order->save();
            return response('Success', 200);
        }


        return response('Invalid', 400);
    }
}
