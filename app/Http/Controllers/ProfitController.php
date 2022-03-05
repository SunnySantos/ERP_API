<?php

namespace App\Http\Controllers;

use App\Models\Expenses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfitController extends Controller
{
    public function getProfitByMonth(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|numeric|exists:branches,id',
            'month' => 'required|numeric|min:1|max:12'
        ]);

        $expenses = DB::table('expenses')
            ->select(
                DB::raw('SUM(amount) as total')
            )
            ->whereMonth('created_at', $request->month)
            ->where('branch_id', $request->branch_id)
            ->whereNull('deleted_at')
            ->get()
            ->first();


        $revenue = DB::table('orders as o')
            ->join('carts as c', 'c.order_id', '=', 'o.id')
            ->select(
                DB::raw('SUM(c.total_price) as total')
            )
            ->where('branch_id', $request->branch_id)
            ->whereMonth('o.created_at', $request->month)
            ->whereNull('o.deleted_at')
            ->whereNull('c.deleted_at')
            ->get()
            ->first();

        if ($expenses && $revenue) {
            $profit = ($revenue->total - $expenses->total);
            if ($profit > 0) {
                return response($profit, 200);
            }
        }
        return response(0, 200);
    }

    public function getWholeYearProfit(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|numeric|exists:branches,id',
            'year' => 'required|digits:4|integer|min:1900|max:' . (date('Y') + 1)
        ]);

        $sales = DB::table('orders as o')
            ->join('carts as c', 'c.order_id', '=', 'o.id')
            ->select(
                DB::raw('AVG(c.total_price) as ave'),
                DB::raw("YEAR(o.processed) as year"),
                DB::raw("MONTH(o.processed) as month")
            )
            ->where('o.branch_id', $request->branch_id)
            ->whereYear('o.processed', $request->year)
            ->whereNull('o.deleted_at')
            ->whereNull('c.deleted_at')
            ->groupBy('month', 'year')
            ->orderBy('month', 'ASC')
            ->get();

        return $sales;
    }
}
