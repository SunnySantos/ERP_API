<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Cart;
use App\Models\Employee;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    public function index(Request $request)
    {

        $status = $request->input('status');
        $id = $request->input('id');
        $customer_id = auth()->user()->customer->id;

        $orders = Order::orderBy('id', 'DESC')
            ->whereNull('deleted_at');

        if (isset($status) && !is_null($customer_id)) {
            $orders->where('customer_id', $customer_id)
                ->where('status', $status);
        }

        if (isset($id)) {
            if ($id !== "null") {
                $orders->where('id', $id);
            }
        }

        return OrderResource::collection(
            $orders->paginate(10)
        );
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'location' => 'required|string',
        ]);

        $customer_id = auth()->user()->customer->id;

        if ($customer_id) {
            $request['customer_id'] = $customer_id;
            $request['status'] = "PENDING";
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


    public function update(Request $request, $id)
    {
        $request->validate([
            'receive' => 'nullable|boolean',
            'cancel' => 'nullable|boolean'
        ]);

        $receive = $request->input('receive');
        $cancel = $request->input('cancel');

        $order = Order::where('id', $id)
            ->whereNull('deleted_at');

        if (isset($receive) && $receive === 1) {
            $order->update(['status' => 'DELIVERED']);
            return $order && response('Successfully updated.', 200);
        } elseif (isset($cancel) && $cancel === 1) {
            $order->update(['status' => 'CANCELLED']);
            return $order && response('Successfully updated.', 200);
        }
        return response('Failed.', 400);
    }



    public function payment(Request $request, $id)
    {
        $request->validate([
            'amount_tendered' => 'required|numeric|min:1'
        ]);

        $employee_id = auth()->user()->employee->id;
        $branch_id = auth()->user()->employee->branch_id;
        $amount_tendered = $request->input('amount_tendered');

        $order = Order::where('id', $id)
            ->where('amount_tendered', 0)
            ->whereNull('deleted_at')
            ->first();

        if (!is_null($order)) {
            $carts = $order->carts;
            $sum = $carts->sum('total_price') + $order->shipping_fee;

            if ($sum > $amount_tendered) return response('Insufficient amount', 400);

            // CHECKING IF THERE ARE ENOUGH STOCKS
            for ($i = 0; $i < sizeof($carts); $i++) {
                $cart = $carts[$i];
                $product = $cart->product;
                $stocks = $product->stocks->where('branch_id', $branch_id);
                $product_quantity = $cart->quantity;
                $stocks_sum = $stocks->sum('stocks');

                if ($stocks_sum < $product_quantity) {
                    return response("Insufficient stock of $product->name.", 400);
                }
            }

            $order->update(['status' => "PICKUP"]);

            // DEDUCTING STOCKS
            for ($i = 0; $i < sizeof($carts); $i++) {
                $cart = $carts[$i];
                $product = $cart->product;
                $stocks = $product->stocks->where('branch_id', $branch_id)->where('stocks', '>', 0);
                $product_quantity = $cart->quantity;

                for ($j = 0; $j < sizeof($stocks); $j++) {
                    $remaining = 0;
                    $stock = $stocks[$j];

                    $stock_quantity = $stock->stocks;

                    if ($remaining > 0) {
                        if ($stock_quantity >= $remaining) {
                            $stock->update(['stocks' => $stock_quantity - $remaining]);
                            $remaining = 0;
                            break;
                        } else {
                            $remaining = ($remaining - $stock_quantity);
                            $stock->update(['stocks' => 0]);
                        }
                    }

                    if ($stock_quantity >= $product_quantity) {
                        $stock->update(['stocks' => $stock_quantity - $product_quantity]);
                        $remaining = 0;
                        break;
                    } else {
                        $remaining = ($product_quantity - $stock_quantity);
                        $stock->update(['stocks' => 0]);
                    }
                }
            }

            $order->employee_id = $employee_id;
            $order->branch_id = $branch_id;
            $order->amount_tendered = $amount_tendered;
            $order->save();
            return response("Successfully updated.");
        }

        return response('Already paid.', 400);
    }


    public function getBranchId($employee_id)
    {
        $employee = Employee::where('id', $employee_id)->first();
        if ($employee) {
            return $employee->branch_id;
        }
        return null;
    }

    // public function received($id)
    // {
    //     $order = Order::where('id', $id)
    //         ->whereNull('deleted_at')
    //         ->update([
    //             'status' => 'DELIVERED'
    //         ]);

    //     if ($order) {
    //         return response('Successfully updated.', 200);
    //     }
    //     return response('Failed.', 400);
    // }

    // public function cancel($id)
    // {
    //     $order = Order::where('id', $id)
    //         ->whereNull('deleted_at')
    //         ->update([
    //             'status' => 'CANCELLED'
    //         ]);

    //     if ($order) {
    //         return response('Successfully updated.', 200);
    //     }
    //     return response('Failed.', 400);
    // }



    // public function showOrdersByCustomerId(Request $request)
    // {
    //     $status = $request->input('status');
    //     $user = auth()->user();
    //     $customer_id = null;

    //     if ($user) {
    //         $customer = Customer::where('user_id', $user->id)->first();
    //         if ($customer) {
    //             $customer_id = $customer->id;
    //         }
    //     }

    //     $orders = Order::where('customer_id', $customer_id)
    //         ->where('status', $status)
    //         ->whereNull('deleted_at')
    //         ->orderBy('id', 'DESC')
    //         ->paginate(10);

    //     return OrderResource::collection($orders);
    // }

    // public function showProcessedOrder()
    // {
    //     $orders = tap(
    //         Order::select(
    //             'id',
    //             'ordered',
    //             'customer_id',
    //             'status',
    //         )
    //             ->whereNull('deleted_at')
    //             ->orderBy('id', 'DESC')
    //             ->paginate(10),
    //         function ($paginateInstance) {
    //             return $paginateInstance->getCollection()->transform(function ($value) {
    //                 $cart_products = $this->getCartProducts($value->id);
    //                 $grand_total = $this->getGrandTotal($value->id);
    //                 $customer = $this->getCustomer($value->customer_id);
    //                 $value->cart_products = $cart_products;
    //                 $value->grand_total = $grand_total;
    //                 $value->customer = $customer;
    //                 return $value;
    //             });
    //         }
    //     );

    //     return response($orders, 200);
    // }

    // public function showProcessedOrderById($id)
    // {
    //     $orders = tap(
    //         Order::where('id', $id)
    //             ->whereNull('deleted_at')
    //             ->orderBy('id', 'DESC')
    //             ->paginate(10),
    //         function ($paginateInstance) {
    //             return $paginateInstance->getCollection()->transform(function ($value) {
    //                 $cart_products = $this->getCartProducts($value->id);
    //                 $grand_total = $this->getGrandTotal($value->id);
    //                 $customer = $this->getCustomer($value->customer_id);
    //                 $value->cart_products = $cart_products;
    //                 $value->grand_total = $grand_total;
    //                 $value->customer = $customer;
    //                 return $value;
    //             });
    //         }
    //     );

    //     return response($orders, 200);
    // }

    // public function pendingOrderCount()
    // {
    //     $pendingCount = Order::select('id')
    //         ->whereNotNull('ordered')
    //         ->whereNull('processed')
    //         ->whereNull('delivered')
    //         ->whereNull('cancelled')
    //         ->whereNull('deleted_at')
    //         ->count();

    //     $processedCount = Order::select('id')
    //         ->whereNotNull('ordered')
    //         ->whereNotNull('processed')
    //         ->whereNull('delivered')
    //         ->whereNull('cancelled')
    //         ->whereNull('deleted_at')
    //         ->count();

    //     $deliveredCount = Order::select('id')
    //         ->whereNotNull('ordered')
    //         ->whereNotNull('delivered')
    //         ->whereNull('cancelled')
    //         ->whereNull('deleted_at')
    //         ->count();

    //     $cancelledCount = Order::select('id')
    //         ->whereNotNull('ordered')
    //         ->whereNotNull('cancelled')
    //         ->whereNull('deleted_at')
    //         ->count();

    //     return response(
    //         [
    //             "pending" => $pendingCount,
    //             "processed" => $processedCount,
    //             "delivered" => $deliveredCount,
    //             "cancelled" => $cancelledCount
    //         ],
    //         200
    //     );
    // }

    // public function showPendingOrder()
    // {
    //     $orders = tap(
    //         Order::whereNull('deleted_at')
    //             ->orderBy('id', 'DESC')
    //             ->paginate(10),
    //         function ($paginateInstance) {
    //             return $paginateInstance->getCollection()->transform(function ($value) {
    //                 $cart_products = $this->getCartProducts($value->id);
    //                 $grand_total = $this->getGrandTotal($value->id);
    //                 $customer = $this->getCustomer($value->customer_id);
    //                 $value->cart_products = $cart_products;
    //                 $value->grand_total = $grand_total;
    //                 $value->customer = $customer;
    //                 return $value;
    //             });
    //         }
    //     );

    //     return response($orders, 200);
    // }

    // public function showPendingOrderById($id)
    // {
    //     $orders = tap(
    //         Order::select(
    //             'id',
    //             'ordered',
    //             'customer_id',
    //             'delivered',
    //             'processed',
    //             'cancelled'
    //         )
    //             ->where('id', $id)
    //             ->whereNotNull('ordered')
    //             ->whereNull('processed')
    //             ->whereNull('delivered')
    //             ->whereNull('cancelled')
    //             ->whereNull('deleted_at')
    //             ->orderBy('id', 'DESC')
    //             ->paginate(10),
    //         function ($paginateInstance) {
    //             return $paginateInstance->getCollection()->transform(function ($value) {
    //                 $cart_products = $this->getCartProducts($value->id);
    //                 $grand_total = $this->getGrandTotal($value->id);
    //                 $customer = $this->getCustomer($value->customer_id);
    //                 $value->cart_products = $cart_products;
    //                 $value->grand_total = $grand_total;
    //                 $value->customer = $customer;
    //                 return $value;
    //             });
    //         }
    //     );

    //     return response($orders, 200);
    // }

    // public function showPendingOrderByCustomerId($id)
    // {
    //     $orders = tap(
    //         Order::select(
    //             'id',
    //             'ordered',
    //             'delivered',
    //             'processed',
    //             'cancelled'
    //         )
    //             ->where('customer_id', $id)
    //             ->whereNotNull('ordered')
    //             ->whereNull('delivered')
    //             ->whereNull('cancelled')
    //             ->whereNull('deleted_at')
    //             ->orderBy('id', 'DESC')
    //             ->paginate(10),
    //         function ($paginateInstance) {
    //             return $paginateInstance->getCollection()->transform(function ($value) {
    //                 $cart_products = $this->getCartProducts($value->id);
    //                 $grand_total = $this->getGrandTotal($value->id);
    //                 $value->cart_products = $cart_products;
    //                 $value->grand_total = $grand_total;
    //                 return $value;
    //             });
    //         }
    //     );

    //     return response($orders, 200);
    // }

    // public function showDeliveredOrder()
    // {
    //     $orders = tap(
    //         Order::select(
    //             'id',
    //             'ordered',
    //             'customer_id',
    //             'delivered',
    //             'processed',
    //             'cancelled'
    //         )
    //             ->whereNotNull('ordered')
    //             ->whereNotNull('delivered')
    //             ->whereNull('cancelled')
    //             ->whereNull('deleted_at')
    //             ->orderBy('id', 'DESC')
    //             ->paginate(10),
    //         function ($paginateInstance) {
    //             return $paginateInstance->getCollection()->transform(function ($value) {
    //                 $cart_products = $this->getCartProducts($value->id);
    //                 $grand_total = $this->getGrandTotal($value->id);
    //                 $customer = $this->getCustomer($value->customer_id);
    //                 $value->cart_products = $cart_products;
    //                 $value->grand_total = $grand_total;
    //                 $value->customer = $customer;
    //                 return $value;
    //             });
    //         }
    //     );

    //     return response($orders, 200);
    // }

    // public function showDeliveredOrderById($id)
    // {
    //     $orders = tap(
    //         Order::select(
    //             'id',
    //             'ordered',
    //             'customer_id',
    //             'delivered',
    //             'processed',
    //             'cancelled'
    //         )
    //             ->where('id', $id)
    //             ->whereNotNull('ordered')
    //             ->whereNotNull('delivered')
    //             ->whereNull('cancelled')
    //             ->whereNull('deleted_at')
    //             ->orderBy('id', 'DESC')
    //             ->paginate(10),
    //         function ($paginateInstance) {
    //             return $paginateInstance->getCollection()->transform(function ($value) {
    //                 $cart_products = $this->getCartProducts($value->id);
    //                 $grand_total = $this->getGrandTotal($value->id);
    //                 $customer = $this->getCustomer($value->customer_id);
    //                 $value->cart_products = $cart_products;
    //                 $value->grand_total = $grand_total;
    //                 $value->customer = $customer;
    //                 return $value;
    //             });
    //         }
    //     );

    //     return response($orders, 200);
    // }

    // public function showDeliveredOrderByCustomerId($id)
    // {
    //     $orders = tap(
    //         Order::select(
    //             'id',
    //             'ordered',
    //             'delivered',
    //             'processed',
    //             'cancelled'
    //         )
    //             ->where('customer_id', $id)
    //             ->whereNotNull('ordered')
    //             ->whereNotNull('delivered')
    //             ->whereNull('cancelled')
    //             ->whereNull('deleted_at')
    //             ->orderBy('id', 'DESC')
    //             ->paginate(10),
    //         function ($paginateInstance) {
    //             return $paginateInstance->getCollection()->transform(function ($value) {
    //                 $cart_products = $this->getCartProducts($value->id);
    //                 $grand_total = $this->getGrandTotal($value->id);
    //                 $value->cart_products = $cart_products;
    //                 $value->grand_total = $grand_total;
    //                 return $value;
    //             });
    //         }
    //     );

    //     return response($orders, 200);
    // }

    // public function showCancelledOrder()
    // {
    //     $orders = tap(
    //         Order::select(
    //             'id',
    //             'ordered',
    //             'customer_id',
    //             'delivered',
    //             'processed',
    //             'cancelled'
    //         )
    //             ->whereNotNull('ordered')
    //             ->whereNotNull('cancelled')
    //             ->whereNull('deleted_at')
    //             ->orderBy('id', 'DESC')
    //             ->paginate(10),
    //         function ($paginateInstance) {
    //             return $paginateInstance->getCollection()->transform(function ($value) {
    //                 $cart_products = $this->getCartProducts($value->id);
    //                 $grand_total = $this->getGrandTotal($value->id);
    //                 $customer = $this->getCustomer($value->customer_id);
    //                 $value->cart_products = $cart_products;
    //                 $value->grand_total = $grand_total;
    //                 $value->customer = $customer;
    //                 return $value;
    //             });
    //         }
    //     );

    //     return response($orders, 200);
    // }

    // public function showCancelledOrderById($id)
    // {
    //     $orders = tap(
    //         Order::select(
    //             'id',
    //             'ordered',
    //             'customer_id',
    //             'delivered',
    //             'processed',
    //             'cancelled'
    //         )
    //             ->where('id', $id)
    //             ->whereNotNull('ordered')
    //             ->whereNotNull('cancelled')
    //             ->whereNull('deleted_at')
    //             ->orderBy('id', 'DESC')
    //             ->paginate(10),
    //         function ($paginateInstance) {
    //             return $paginateInstance->getCollection()->transform(function ($value) {
    //                 $cart_products = $this->getCartProducts($value->id);
    //                 $grand_total = $this->getGrandTotal($value->id);
    //                 $customer = $this->getCustomer($value->customer_id);
    //                 $value->cart_products = $cart_products;
    //                 $value->grand_total = $grand_total;
    //                 $value->customer = $customer;
    //                 return $value;
    //             });
    //         }
    //     );

    //     return response($orders, 200);
    // }

    // public function showCancelledOrderByCustomerId($id)
    // {
    //     $orders = tap(
    //         Order::select(
    //             'id',
    //             'ordered',
    //             'delivered',
    //             'processed',
    //             'cancelled'
    //         )
    //             ->where('customer_id', $id)
    //             ->whereNotNull('ordered')
    //             ->whereNotNull('cancelled')
    //             ->whereNull('deleted_at')
    //             ->orderBy('id', 'DESC')
    //             ->paginate(10),
    //         function ($paginateInstance) {
    //             return $paginateInstance->getCollection()->transform(function ($value) {
    //                 $cart_products = $this->getCartProducts($value->id);
    //                 $grand_total = $this->getGrandTotal($value->id);
    //                 $value->cart_products = $cart_products;
    //                 $value->grand_total = $grand_total;
    //                 return $value;
    //             });
    //         }
    //     );

    //     return response($orders, 200);
    // }

    // public function getCustomer($customer_id)
    // {
    //     return Customer::select('profile')
    //         ->where('id', $customer_id)
    //         ->get()
    //         ->first();
    // }

    // public function getCartProducts($order_id)
    // {
    //     return DB::table('carts as c')
    //         ->join('products as p', 'p.id', '=', 'c.product_id')
    //         ->select(
    //             'p.image',
    //             'p.cake_project_id',
    //             'p.file_extension',
    //             'p.name',
    //             'c.quantity',
    //             'c.total_price'
    //         )
    //         ->where('order_id', $order_id)
    //         ->orderBy('c.id', 'DESC')
    //         ->get();
    // }

    // public function getGrandTotal($order_id)
    // {
    //     $sum = DB::table('carts')
    //         ->where('order_id', $order_id)
    //         ->sum('total_price');

    //     return $sum;
    // }





    // public function checkout(Request $request, $id)
    // {
    //     $request->validate([
    //         'customer_id' => 'required|numeric|exists:customers,id',
    //         'location' => 'required|string',
    //         'status' => 'required|string'
    //     ]);

    //     $request['shipping_fee'] = 100;

    //     $order = Order::create($request->all());

    //     if ($order) {
    //         $carts = Cart::where('customer_id', $request->customer_id)
    //             ->whereNull('order_id')
    //             ->whereNull('deleted_at')
    //             ->update([
    //                 'order_id' => $order->id
    //             ]);

    //         if ($carts) {
    //             return response('Successfully checked out', 201);
    //         } else {
    //             $order->forceDelete();
    //         }
    //     }
    //     return response('Failed.', 400);
    // }


}
