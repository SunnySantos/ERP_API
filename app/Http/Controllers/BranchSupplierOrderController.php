<?php

namespace App\Http\Controllers;

use App\Models\BranchSupplierInvoice;
use App\Models\BranchSupplierOrder;
use App\Models\BranchSupplierOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchSupplierOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }



    public function count($id)
    {
        $pendingCount = BranchSupplierOrder::select('id')
            ->where('supplier_id', $id)
            ->whereNotNull('checked_out')
            ->whereNull('processed')
            ->whereNull('delivered')
            ->whereNull('deleted_at')
            ->count();


        $processedCount = BranchSupplierOrder::select('id')
            ->where('supplier_id', $id)
            ->whereNotNull('checked_out')
            ->whereNotNull('processed')
            ->whereNull('delivered')
            ->whereNull('deleted_at')
            ->count();

        $deliveredCount = BranchSupplierOrder::select('id')
            ->where('supplier_id', $id)
            ->whereNotNull('checked_out')
            ->whereNotNull('processed')
            ->whereNotNull('delivered')
            ->whereNull('deleted_at')
            ->count();

        return response([
            "pending" => $pendingCount,
            "processed" => $processedCount,
            "delivered" => $deliveredCount
        ], 200);
    }

    public function pendingOrders($id)
    {
        return BranchSupplierOrder::select(
            'id',
            'amount_tendered',
            'checked_out',
            'processed',
            'delivered'
        )
            ->whereNotNull('checked_out')
            ->whereNull('processed')
            ->whereNull('delivered')
            ->whereNull('deleted_at')
            ->where('supplier_id', '=', $id)
            ->paginate(10);
    }

    public function processedOrders($id)
    {
        return BranchSupplierOrder::select(
            'id',
            'amount_tendered',
            'checked_out',
            'processed',
            'delivered'
        )
            ->whereNotNull('checked_out')
            ->whereNotNull('processed')
            ->whereNull('delivered')
            ->whereNull('deleted_at')
            ->where('supplier_id', '=', $id)
            ->paginate(10);
    }

    public function deliveredOrders($id)
    {
        return BranchSupplierOrder::select(
            'id',
            'amount_tendered',
            'checked_out',
            'processed',
            'delivered'
        )
            ->whereNotNull('checked_out')
            ->whereNotNull('processed')
            ->whereNotNull('delivered')
            ->whereNull('deleted_at')
            ->where('supplier_id', '=', $id)
            ->paginate(10);
    }

    public function orderDetail($id)
    {
        $order = BranchSupplierOrder::where('id', $id)
            ->whereNull('deleted_at')
            ->get()
            ->first();

        if ($order !== null) {
            $invoice = BranchSupplierInvoice::where('order_id', $order->id)
                ->whereNull('deleted_at')
                ->get()
                ->first();

            $items = DB::table('branch_supplier_order_items as b')
                ->join('supplier_items as c', 'b.item_id', '=', 'c.id')
                ->select(
                    'b.id',
                    'b.quantity',
                    'c.image',
                    'c.name',
                    'c.price',
                    'c.unit'
                )
                ->where('b.order_id', '=', $order->id)
                ->get();

            $grandTotal = 0;

            for ($i = 0; $i < sizeof($items); $i++) {
                $item = $items[$i];
                $total = ($item->price * $item->quantity);
                $grandTotal += $total;
            }

            return response([
                "order" => $order,
                "items" => $items,
                "total" => $grandTotal,
                "hasInvoice" => ($invoice !== null)
            ], 200);
        }

        return response("Order does not exist.", 400);
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
            'supplier_id' => 'required|numeric|exists:suppliers,id',
            'item_id' => 'required|numeric|exists:supplier_items,id'
        ]);

        $orderData = ['supplier_id' => $request->supplier_id];
        $itemData = ['item_id' => $request->item_id];

        $order = BranchSupplierOrder::whereNull('deleted_at')
            ->whereNull('checked_out')
            ->get()
            ->first();

        if ($order === null) {
            $createOrder = BranchSupplierOrder::create($orderData);
            if ($createOrder !== null) {
                $itemData['order_id'] = $createOrder->id;
                $createOrderItem = BranchSupplierOrderItem::create($itemData);
                if ($createOrderItem !== null) {
                    return response("Successfully added!", 201);
                }
            }
        } else {
            $itemData['order_id'] = $order->id;
            $item = BranchSupplierOrderItem::whereNull('deleted_at')
                ->where('item_id', $itemData['item_id'])
                ->where('order_id', $itemData['order_id'])
                ->get()
                ->first();

            if ($item === null) {
                $createOrderItem = BranchSupplierOrderItem::create($itemData);
                if ($createOrderItem !== null) {
                    return response("Successfully added!", 201);
                }
            } else {
                $item->quantity += 1;
                $item->save();
                return response("Successfully added!", 201);
            }
        }

        return response("Invalid!", 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return DB::table('branch_supplier_orders as a')
            ->join('branch_supplier_order_items as b', 'a.id', '=', 'b.order_id')
            ->join('supplier_items as c', 'b.item_id', '=', 'c.id')
            ->select(
                'a.id',
                'b.id as bso_item_id',
                'b.quantity',
                'c.image',
                'c.name',
                'c.price',
                'c.unit'
            )
            ->whereNull('a.checked_out')
            ->whereNull('a.deleted_at')
            ->whereNull('b.deleted_at')
            ->where('a.supplier_id', '=', $id)
            ->get();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'supplier_id' => 'required|numeric|exists:suppliers,id',
            'quantity' => 'required|numeric|min:1'
        ]);

        $item = BranchSupplierOrderItem::find($id);
        if ($item !== null) {
            $item->quantity = $request->quantity;
            $item->save();
            return response($this->show($request->supplier_id), 200);
        }
        return response('Item does not exist.', 400);
    }

    public function checkout($id)
    {
        $order = BranchSupplierOrder::find($id);
        if ($order !== null) {
            $order->checked_out = date("Y-m-d");
            $order->save();
            return response("Checked out successfully", 200);
        }
        return response('Order does not exist.', 400);
    }

    public function payment(Request $request, $id)
    {
        $request->validate([
            'amount_tendered' => 'required|numeric|min:0'
        ]);

        $order = BranchSupplierOrder::where('id', $id)
            ->whereNull('deleted_at')
            ->get()
            ->first();

        if ($order !== null) {
            $items = DB::table('branch_supplier_order_items as b')
                ->join('supplier_items as c', 'b.item_id', '=', 'c.id')
                ->select(
                    'b.quantity',
                    'c.price'
                )
                ->where('b.order_id', '=', $order->id)
                ->get();

            $grandTotal = 0;

            for ($i = 0; $i < sizeof($items); $i++) {
                $item = $items[$i];
                $total = ($item->price * $item->quantity);
                $grandTotal += $total;
            }

            if ($grandTotal <= $request->amount_tendered) {
                if ($order->processed === null) {
                    $order->processed = date('Y-m-d');
                }
                $order->amount_tendered = $request->amount_tendered;
                $order->save();
                return response("Successfully updated.", 200);
            }
            return response(["errors" => ["amount_tendered" => "Insufficient amount."]], 422);
        }
        return response("Order does not exist.", 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $item =  BranchSupplierOrderItem::find($id);
        if ($item !== null) {
            $item->forceDelete();
            return response("Successfully deleted!", 200);
        }
        return response("Item does not exist!", 400);
    }
}
