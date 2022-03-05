<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function getSalesAveByMonth(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|numeric|exists:branches,id',
            'month' => 'required|numeric|min:1|max:12'
        ]);


        $sales = DB::table('orders as o')
            ->join('carts as c', 'c.order_id', '=', 'o.id')
            ->select(
                DB::raw('ROUND(AVG(c.total_price),2) as ave')
            )
            ->where('branch_id', $request->branch_id)
            ->whereMonth('o.created_at', $request->month)
            ->whereNull('o.deleted_at')
            ->whereNull('c.deleted_at')
            ->get()
            ->first();

        if ($sales) {
            return response($sales->ave, 200);
        }
        return response(0, 200);
    }

    public function getWholeYearSales(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|numeric|exists:branches,id',
            'year' => 'required|digits:4|integer|min:1900|max:' . (date('Y') + 1)
        ]);

        $sales = DB::table('orders as o')
            ->join('carts as c', 'c.order_id', '=', 'o.id')
            ->select(
                DB::raw('AVG(c.total_price) as ave'),
                DB::raw("YEAR(o.created_at) as year"),
                DB::raw("MONTH(o.created_at) as month")
            )
            ->where('o.branch_id', $request->branch_id)
            ->whereYear('o.created_at', $request->year)
            ->whereNull('o.deleted_at')
            ->whereNull('c.deleted_at')
            ->where('amount_tendered', '>', 0)
            ->groupBy('month', 'year')
            ->orderBy('month', 'ASC')
            ->get();

        return $sales;
    }
}
