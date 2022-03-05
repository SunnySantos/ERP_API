<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchSupplierInvoice;
use App\Models\BranchSupplierOrder;
use App\Models\BranchSupplierOrderItem;
use App\Models\SupplierItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchSupplierInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    public function showBySupplierId($id)
    {
        return DB::table('branch_supplier_orders as b1')
            ->join('branch_supplier_invoices as b2', 'b1.id', '=', 'b2.order_id')
            ->select(
                'b2.id',
                'b2.created_at'
            )
            ->where('b1.supplier_id', $id)
            ->whereNull('b2.deleted_at')
            ->paginate(10);
    }

    public function unpaid()
    {
        return DB::table('branch_supplier_invoices as a')
            ->join('branch_supplier_orders as b', 'a.order_id', '=', 'b.id')
            ->select(
                'a.id',
                'a.created_at',
                'a.due',
                'b.amount_tendered'
            )
            ->where('b.amount_tendered', '=', 0)
            ->whereNull('a.deleted_at')
            ->paginate(10);
    }

    public function paid()
    {
        return DB::table('branch_supplier_invoices as a')
            ->join('branch_supplier_orders as b', 'a.order_id', '=', 'b.id')
            ->select(
                'a.id',
                'a.created_at',
                'a.due',
                'b.amount_tendered'
            )
            ->where('b.amount_tendered', '>', 0)
            ->whereNull('a.deleted_at')
            ->paginate(10);
    }

    public function overdue()
    {
        return DB::table('branch_supplier_invoices as a')
            ->join('branch_supplier_orders as b', 'a.order_id', '=', 'b.id')
            ->select(
                'a.id',
                'a.created_at',
                'a.due',
                'b.amount_tendered'
            )
            ->where('a.due', '<', date('Y-m-d'))
            ->whereNull('a.deleted_at')
            ->paginate(10);
    }

    public function countBySupplierId($id)
    {
        $count = DB::table('suppliers as s')
            ->join('branch_supplier_orders as bso', 'bso.supplier_id', '=', 's.id')
            ->join('branch_supplier_invoices as bsi', 'bsi.order_id', '=', 'bso.id')
            ->select('bsi.id')
            ->where('s.id', $id)
            ->whereNull('bsi.deleted_at')
            ->count();
        return $count;
    }

    public function count()
    {
        $count = BranchSupplierInvoice::select('id')
            ->whereNull('deleted_at')
            ->count();
        return $count;
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
            'order_id' => 'required|numeric|exists:branch_supplier_orders,id',
            'due' => 'required|date'
        ]);

        $order_id = $request->order_id;

        $invoice_ = BranchSupplierInvoice::select('id', 'order_id')
            ->where('order_id', $order_id)
            ->get()
            ->first();

        if ($invoice_ === null) {
            $order = BranchSupplierOrder::where('id', $order_id)
                ->whereNull('deleted_at')
                ->get()
                ->first();

            $items = BranchSupplierOrderItem::where('order_id', $order->id)
                ->whereNull('deleted_at')
                ->get();

            $flag = true;

            foreach ($items as $key => $value) {
                $item_id = $value->item_id;
                $quantity = $value->quantity;

                $item = SupplierItems::where('id', $item_id)
                    ->get()
                    ->first();

                $stock = $item->stock;

                if ($quantity > $stock) {
                    $flag = false;
                }
            }

            if ($flag) {
                foreach ($items as $key => $value) {
                    $item_id = $value->item_id;
                    $quantity = $value->quantity;

                    $item = SupplierItems::where('id', $item_id)
                        ->get()
                        ->first();

                    $item->stock -= $quantity;
                    $item->save();
                }
            }

            if ($order !== null) {
                $order->processed = date("Y-m-d");
                $order->save();

                if ($order !== null) {
                    $invoice = BranchSupplierInvoice::create($request->all());
                    if ($invoice !== null) {
                        return response("Successfully created.", 200);
                    }
                }
            }
        }

        return response("Invoice already exist.", 400);
    }

    public function search(Request $request, $id)
    {
        $request->validate([
            'supplier_id' => 'required|numeric'
        ]);

        $order = BranchSupplierOrder::where('id', $id)
            ->where('supplier_id', $request->supplier_id)
            ->whereNotNull('checked_out')
            ->whereNull('deleted_at')
            ->get()
            ->first();

        if ($order !== null) {
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

            return response(["order" => $order, "items" => $items, "total" => $grandTotal], 200);
        }

        return response(["errors" => ["order" => "Order does not exist."]], 422);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $invoice = BranchSupplierInvoice::where('id', $id)
            ->whereNull('deleted_at')
            ->get()
            ->first();

        if ($invoice !== null) {
            $order = BranchSupplierOrder::where('id', $invoice->order_id)
                ->whereNull('deleted_at')
                ->get()
                ->first();

            if ($order !== null) {

                $supplier = DB::table('suppliers as a')
                    ->join('supplier_companies as b', 'a.companies_id', '=', 'b.id')
                    ->select(
                        'b.name',
                        'b.address',
                        'b.phone_number'
                    )
                    ->get()
                    ->first();

                $branch = Branch::select(
                    'name',
                    'address',
                    'phone_number'
                )
                    ->where('id', $order->branch_id)
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
                    "supplier" => $supplier,
                    "branch" => $branch,
                    "invoice" => $invoice,
                    "order" => $order,
                    "items" => $items,
                    "total" => $grandTotal
                ], 200);
            }
        }

        return response("Invoice does not exist.", 400);
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
    }

    public function received($id)
    {
        $invoice = BranchSupplierInvoice::where('id', $id)
            ->whereNull('deleted_at')
            ->get()
            ->first();

        if ($invoice !== null) {
            $order = BranchSupplierOrder::where('id', $invoice->order_id)
                ->whereNull('deleted_at')
                ->get()
                ->first();

            if ($order !== null) {
                $order->delivered = date('Y-m-d');
                $order->save();
                return response('Successfully updated.', 200);
            }
            return response('Order does not exist.', 400);
        }

        return response('Invoice does not exist.', 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $invoice =  BranchSupplierInvoice::find($id);
        if ($invoice !== null) {
            $invoice->delete();
            return response("Successfully deleted!", 200);
        }
        return response("Invoice does not exist!", 400);
    }
}
