<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\BranchProduct;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    public function customersOrderTable()
    {
        $select = 'p.name';
        $order =  DB::table('orders as o')
            ->join('customers as c', 'o.customer_id', '=', 'c.id')
            ->select(
                'o.id',
                'c.profile_image',
                'o.ordered_at',
                'o.delivered_at',
                'o.grand_total',
                'is_paid'
            )
            ->where('o.ordered_at', '!=', null)
            ->where('o.is_paid', '=', 0)
            ->where('o.deleted_at', '=', null)
            ->where('o.cancel', '=', 0)
            ->orderBy('o.ordered_at', 'desc')
            ->get();


        if ($order !== null) {
            for ($i = 0; $i < sizeof($order); $i++) {
                $o = $order[$i];
                $o->products = $this->getProductOnCart($o->id, $select);
            }
            return $order;
        }
        return response("Invalid", 400);
    }

    public function pendingCount()
    {
        $count = Order::select('id')
            ->where('ordered_at', '!=', null)
            ->where('delivered_at', null)
            ->where('amount_tendered', 0)
            ->where('is_paid', 0)
            ->where('cancel', 0)
            ->count();
        return $count;
    }

    public function totalPurchased()
    {
        $sum = DB::table('orders as o')
            ->join('carts as c', 'o.id', '=', 'c.order_id')
            ->where('o.amount_tendered', '!=', 0)
            ->where('o.is_paid', 1)
            ->where('o.cancel', 0)
            ->sum('c.quantity');
        return $sum;
    }

    public function purchaseHistory($id)
    {
        $select = ['p.name'];
        $order =  DB::table('orders as o')
            ->join('customers as c', 'o.customer_id', '=', 'c.id')
            ->select(
                'o.id',
                'c.profile_image',
                'o.ordered_at',
                'o.delivered_at',
                'o.grand_total',
                'is_paid'
            )
            ->where('c.id', $id)
            ->where('o.deleted_at', '=', null)
            ->orderBy('o.ordered_at', 'desc')
            ->get();

        for ($i = 0; $i < sizeof($order); $i++) {
            $o = $order[$i];
            $o->products = $this->getProductOnCart($o->id, $select);
        }

        return $order;
    }

    public function customerOrderedProductTable($id)
    {
        $select = [
            'p.name',
            'p.image',
            'p.price',
            'p.size',
            'p.category',
            'c.dedication',
            'c.quantity',
            'c.total_price',
            'bp.id as bp_id'
        ];
        $order =  DB::table('orders as o')
            ->join('customers as c', 'o.customer_id', '=', 'c.id')
            ->select(
                'o.id',
                'o.customer_id as customer_id',
                'c.profile_image',
                'o.ordered_at',
                'o.delivered_at',
                'o.grand_total',
                'is_paid',
                'c.firstname',
                'c.middlename',
                'c.lastname',
                'c.suffix',
                'c.home_address',
                'c.phone_number',
                'c.email',
                'o.amount_tendered'
            )
            ->where('o.customer_id', '=', $id)
            ->where('o.deleted_at', '=', null)
            ->orderBy('o.ordered_at', 'desc')
            ->get();

        $arr = array();


        for ($i = 0; $i < sizeof($order); $i++) {
            $o = $order[$i];
            $o->products = $this->getProductOnCart($o->id, $select);
            array_push($arr, $o);
        }

        return response($arr, 200);
    }

    public function customerOrderTable($id)
    {
        $select = [
            'p.name',
            'p.image',
            'p.price',
            'p.size',
            'p.category',
            'c.dedication',
            'c.quantity',
            'c.total_price',
            'bp.id as bp_id'
        ];
        $order =  DB::table('orders as o')
            ->join('customers as c', 'o.customer_id', '=', 'c.id')
            ->select(
                'o.id',
                'o.customer_id as customer_id',
                'c.profile_image',
                'o.ordered_at',
                'o.delivered_at',
                'o.grand_total',
                'is_paid',
                'c.firstname',
                'c.middlename',
                'c.lastname',
                'c.suffix',
                'c.home_address',
                'c.phone_number',
                'c.email',
                'o.amount_tendered'
            )
            ->where('o.ordered_at', '!=', null)
            ->where('o.id', '=', $id)
            ->where('o.deleted_at', '=', null)
            ->get()
            ->first();

        if ($order !== null) {
            $order->products = $this->getProductOnCart($order->id, $select);
            return $order;
        }
        return response("Invalid", 400);
    }

    public  function onPayment(Request $request, $id)
    {
        $request->validate([
            'amount_tendered' => 'required|numeric'
        ]);


        $order = Order::select(
            'id',
            'deleted_at',
            'is_paid',
            'amount_tendered',
        )
            ->where('deleted_at', null)
            ->where('cancel', 0)
            ->where('id', $id)
            ->get()
            ->first();


        if ($order !== null) {

            if ($order->grand_total <= $request->amount_tendered) {
                $order->amount_tendered = $request->amount_tendered;
                $order->is_paid = 1;
                $order->save();
                return response('Success', 200);
            }
        }
        return response('Invalid', 400);
    }

    public function onDeliver(Request $request, $id)
    {
        $request->validate([
            'delivered_at' => 'required'
        ]);

        $order = Order::select(
            'id',
            'delivered_at',
            'is_paid',
            'deleted_at'
        )
            ->where('id', $id)
            ->where('deleted_at', null)
            ->where('cancel', 0)
            ->get()
            ->first();

        if ($order !== null) {
            $order->delivered_at = $request->delivered_at;
            $order->save();

            $cart = Cart::select(
                'id',
                'order_id',
                'branch_product_id',
                'quantity'
            )
                ->where('order_id', $order->id)
                ->get();


            for ($i = 0; $i < sizeof($cart); $i++) {
                $c = $cart[$i];

                $branchProduct = $this->getBranchProduct([['id', $c->branch_product_id]]);

                if ($branchProduct->quantity >= $c->quantity) {
                    $branchProduct->quantity -= $c->quantity;
                    $branchProduct->save();
                } else {
                    $alternativeWhereArr = [
                        ['product_id', $branchProduct->product_id],
                        ['branch_id', $branchProduct->branch_id],
                        ['quantity', '>=', $c->quantity],
                        ['distributed_at', '!=', null]
                    ];

                    $alternativeBranchProduct = $this->getBranchProduct($alternativeWhereArr);

                    if ($alternativeBranchProduct !== null) {
                        $c->branch_product_id = $alternativeBranchProduct->id;
                        if ($c->save()) {
                            $alternativeBranchProduct->quantity -= $c->quantity;
                            $alternativeBranchProduct->save();
                        }
                    }
                }
            }
            return response('Success', 200);
        }
    }

    public function cancelOrder($id)
    {
        $order = Order::where('deleted_at', null)
            ->where('id', $id)
            ->where('cancel', 0)
            ->get()
            ->first();

        if ($order !== null) {
            $order->cancel = 1;
            $order->save();
            return response("Success", 200);
        }

        return response("Invalid", 400);
    }

    public function mostPurchasedProduct($id)
    {
        $orders = Order::where('deleted_at', null)
            ->select('id')
            ->where('customer_id', $id)
            ->where('cancel', 0)
            ->get();

        if (sizeof($orders) !== 0) {
            $products = array();

            for ($i = 0; $i < sizeof($orders); $i++) {
                $order = $orders[$i];
                $order->products = $this->getProductOnCart($order->id, ['name', 'c.quantity']);
                for ($j = 0; $j < sizeof($order->products); $j++) {
                    $product = $order->products[$j];
                    if (array_key_exists($product->name, $products)) {
                        $products[$product->name] += $product->quantity;
                    } else {
                        $products[$product->name] = $product->quantity;
                    }
                }
            }
            arsort($products);
            $firstKey = array_key_first($products);

            return response([
                "name" => $firstKey,
                "quantity" => $products[$firstKey]
            ], 200);
        }

        return response("No purchased", 200);
    }

    public function getBranchProduct($whereArray)
    {
        return BranchProduct::select(
            'id',
            'branch_id',
            'product_id',
            'quantity'
        )
            ->where($whereArray)
            ->get()
            ->first();
    }

    public function getProductOnCart($id, $select)
    {
        return DB::table('carts as c')
            ->join('branch_products as bp', 'c.branch_product_id', '=', 'bp.id')
            ->join('products as p', 'bp.product_id', '=', 'p.id')
            ->select($select)
            ->where('order_id', '=', $id)
            ->where('c.deleted_at', '=', null)
            ->get();
    }
}
