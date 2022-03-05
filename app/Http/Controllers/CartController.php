<?php

namespace App\Http\Controllers;

use App\Http\Resources\CartResource;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Customer;
use Illuminate\Http\Request;

class CartController extends Controller
{

    public function count()
    {
        $customer_id = auth()->user()->customer->id;

        return Cart::where('customer_id', $customer_id)
            ->whereNull('order_id')
            ->whereNull('deleted_at')
            ->count();
    }



    public function index()
    {
        $customer_id = auth()->user()->customer->id;

        return CartResource::collection(
            Cart::where('customer_id', $customer_id)
                ->whereNull('order_id')
                ->whereNull('deleted_at')
                ->get()
        );
    }

    public function show($id)
    {
        return CartResource::make(
            Cart::where('id', $id)
                ->whereNull('order_id')
                ->whereNull('deleted_at')
                ->first()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'nullable|numeric|exists:products,id',
            'dedication' => 'nullable|string|max:80',
            'quantity' => 'required|numeric|min:1'
        ]);

        $customer_id = auth()->user()->customer->id;

        if ($customer_id) {
            $product_id = $request->product_id;
            $dedication = $request->dedication;
            $quantity = $request->quantity;
            $product = Product::where('id', $product_id)
                ->select('price')
                ->whereNull('deleted_at')
                ->first();

            if ($product) {
                $total_price = $quantity * $product->price;

                $cart = Cart::where('customer_id', $customer_id)
                    ->where('product_id', $product_id)
                    ->whereNull('order_id')
                    ->where('dedication', $dedication)
                    ->whereNull('deleted_at')
                    ->first();

                if ($cart) {
                    $cart->quantity += $quantity;
                    $cart->total_price += $total_price;
                    $cart->save();
                    return response('Successfully added.', 200);
                }

                $request['customer_id'] = $customer_id;
                $request['total_price'] = $total_price;

                Cart::create($request->all());
                return response('Successfully added.', 201);
            }
        }
        return response('Failed.', 400);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'product_id' => 'nullable|numeric|exists:carts,product_id',
            'dedication' => 'nullable|string',
            'quantity' => 'required|numeric'
        ]);

        $product = Product::where('id', $request->product_id)
            ->select('price')
            ->whereNull('deleted_at')
            ->first();

        if ($product) {
            $total_price = $request->quantity * $product->price;

            $cart = Cart::where('id', $id)
                ->where('product_id', $request->product_id)
                ->whereNull('order_id')
                ->update([
                    'dedication' => $request->dedication,
                    'quantity' => $request->quantity,
                    'total_price' => $total_price
                ]);

            if ($cart) {
                return response('Successfully updated.', 200);
            }
        }
        return response('Failed.', 400);
    }

    public function destroy($id)
    {
        $cart = Cart::find($id);

        if ($cart) {
            $cart->forceDelete();
            return response('Successfully deleted.', 200);
        }
        return response('Failed.', 400);
    }
}
