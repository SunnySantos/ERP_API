<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function getSalesAveByMonth(Request $request)
    {

        $request->validate([
            'branch_id' => 'nullable|exists:branches,id'
        ]);

        $branch_id = is_null($request->input('branch_id')) ? auth()->user()->employee->branch_id : $request->input('branch_id');

        $sales = DB::table('orders as o')
            ->join('carts as c', 'c.order_id', '=', 'o.id')
            ->select(
                DB::raw('ROUND(AVG(c.total_price),2) as ave')
            )
            ->where('branch_id', $branch_id)
            ->whereMonth('o.created_at', date('m'))
            ->whereNull('o.deleted_at')
            ->whereNull('c.deleted_at')
            ->get()
            ->first();

        return '₱' . number_format($sales->ave, 2);
    }

    public function getWholeYearSales(Request $request)
    {
        $request->validate([
            'year' => 'required|digits:4|integer|min:1900|max:' . (date('Y') + 1),
            'branch_id' => 'nullable|exists:branches,id'
        ]);

        $branch_id = is_null($request->input('branch_id')) ? auth()->user()->employee->branch_id : $request->input('branch_id');

        $sales = DB::table('orders as o')
            ->join('carts as c', 'c.order_id', '=', 'o.id')
            ->select(
                DB::raw('SUM(c.quantity) as ave'),
                DB::raw("YEAR(o.created_at) as year"),
                DB::raw("MONTH(o.created_at) as month")
            )
            ->where('o.branch_id', $branch_id)
            ->whereYear('o.created_at', $request->year)
            ->whereNull('o.deleted_at')
            ->whereNull('c.deleted_at')
            ->where('amount_tendered', '>', 0)
            ->groupBy('month', 'year')
            ->orderBy('month', 'ASC')
            ->get();

        return $sales;
    }

    public function totalSales()
    {
        $orders = Order::withSum('carts', 'quantity')
            ->where('branch_id', auth()->user()->employee->branch_id)
            ->whereMonth('created_at', date('m'))
            ->get()
            ->toArray();

        return array_reduce($orders, function ($carry, $item) {
            return $carry += $item['carts_sum_quantity'];
        }, 0);
    }
}
