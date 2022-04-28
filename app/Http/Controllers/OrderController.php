<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentOrderRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Cart;
use App\Status\OrderStatus;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{

    public function getByCustomerId(Request $request)
    {
        $status = $request->input('status');

        $customer = auth()->user()->customer;

        if ($customer) {
            return OrderResource::collection(
                Order::where('customer_id', $customer->id)
                    ->where('status', $status)
                    ->whereNull('deleted_at')
                    ->orderBy('id', 'DESC')
                    ->paginate(10)
            );
        }

        return OrderResource::collection(
            Order::where('customer_id', $request->input('id'))
                ->whereNull('deleted_at')
                ->orderBy('id', 'DESC')
                ->paginate(10)
        );
    }

    public function index(Request $request)
    {
        $id = $request->input('id');

        $orders = Order::whereNull('deleted_at');

        if (!empty($id)) {
            $orders->where('id', $id);
        }

        return OrderResource::collection(
            $orders->orderBy('id', 'DESC')
                ->paginate(10)
        );
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreOrderRequest $request)
    {
        $customer_id = auth()->user()->customer->id;

        if ($customer_id) {
            $request['customer_id'] = $customer_id;
            $request['status'] = OrderStatus::PENDING;
            $order = Order::create($request->all());

            if ($order) {
                $carts = Cart::where('customer_id', $customer_id)
                    ->whereNull('order_id')
                    ->whereNull('deleted_at')
                    ->update([
                        'order_id' => $order->id
                    ]);

                if ($carts) {
                    return response('Successfully checked out', 201);
                } else {
                    $order->forceDelete();
                }
                return response($order, 201);
            }
        }
        return response('Failed.', 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return OrderResource::make(Order::where('id', $id)->first());
    }


    public function update(UpdateOrderRequest $request, $id)
    {
        $receive = $request->input('receive');
        $cancel = $request->input('cancel');

        $order = Order::where('id', $id)
            ->whereNull('deleted_at');

        if (isset($receive) && $receive === 1) {
            $order->update(['status' => OrderStatus::DELIVERED]);
            return $order && response('Successfully updated.');
        } elseif (isset($cancel) && $cancel === 1) {
            $order->update(['status' => OrderStatus::CANCELLED]);
            return $order && response('Successfully updated.');
        }
        return response('Failed.', 400);
    }



    public function payment(PaymentOrderRequest $request, $id)
    {
        $employee_id = auth()->user()->employee->id;
        $branch_id = auth()->user()->employee->branch_id;
        $amount_tendered = $request->input('amount_tendered');

        try {
            $result =  DB::transaction(function () use ($id, $branch_id, $employee_id, $amount_tendered) {
                $order = Order::where('id', $id)
                    ->where('amount_tendered', 0)
                    ->whereNull('deleted_at')
                    ->first();

                if ($order) {
                    $carts = $order->carts;
                    $sum = $carts->sum('total_price') + $order->shipping_fee;

                    if ($sum > $amount_tendered) throw new Exception('Insufficient amount');

                    // CHECKING IF THERE ARE ENOUGH STOCKS
                    for ($i = 0; $i < sizeof($carts); $i++) {
                        $cart = $carts[$i];
                        $product = $cart->product;
                        $stocks = $product->stocks->where('branch_id', $branch_id)->where('quantity', '>', 0);
                        $product_quantity = $cart->quantity;
                        $stocks_sum = $stocks->sum('quantity');

                        if ($stocks_sum < $product_quantity) throw new Exception("Insufficient stock of $product->name.");

                        for ($j = 0; $j < sizeof($stocks); $j++) {
                            $remaining = 0;
                            $stock = $stocks[$j];

                            if ($remaining > 0) {
                                if ($stock->quantity >= $remaining) {
                                    $stock->decrement('quantity', $remaining);
                                    $remaining = 0;
                                    break;
                                } else {
                                    $remaining = ($remaining - $stock->quantity);
                                    $stock->update(['quantity' => 0]);
                                }
                            }

                            if ($stock->quantity >= $product_quantity) {
                                $stock->decrement('quantity', $product_quantity);
                                $remaining = 0;
                                break;
                            } else {
                                $remaining = ($product_quantity - $stock->quantity);
                                $stock->update(['quantity' => 0]);
                            }
                        }
                    }

                    $order->status = OrderStatus::PICKUP;
                    $order->employee_id = $employee_id;
                    $order->branch_id = $branch_id;
                    $order->amount_tendered = $amount_tendered;
                    $order->save();
                    return "Successfully updated.";
                }

                throw new Exception('Already paid.');
            });

            return response($result);
        } catch (Exception $e) {
            return response($e->getMessage(), 400);
        }
    }

    public function chart(Request $request)
    {
        $request->validate([
            'year' => 'required'
        ]);

        $branch_id = auth()->user()->employee->branch_id;

        $orders = Order::select(
            'id',
            DB::raw("YEAR(created_at) as year"),
            DB::raw("MONTH(created_at) as month"),
        )
            ->withSum('carts', 'quantity')
            ->whereYear('created_at', $request->input('year'))
            ->where('branch_id', $branch_id)
            ->whereNull('deleted_at')
            ->groupBy("month", "year", "id")
            ->orderBy("month", "ASC")
            ->get();

        return $orders;
    }

    public function pendingCount()
    {
        $branch_id = auth()->user()->employee->branch_id;
        return Order::where('branch_id', $branch_id)
            ->where('status', OrderStatus::PENDING)
            ->whereMonth('created_at', date('m'))
            ->count();
    }

    public function deliveredCount()
    {
        $branch_id = auth()->user()->employee->branch_id;
        return Order::where('branch_id', $branch_id)
            ->where('status', OrderStatus::DELIVERED)
            ->whereMonth('created_at', date('m'))
            ->count();
    }
}
