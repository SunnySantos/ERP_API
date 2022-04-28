<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetProfitByMonthRequest;
use App\Http\Requests\GetWholeYearProfitRequest;
use App\Models\Cart;
use App\Models\Expenses;
use Illuminate\Support\Facades\DB;

class ProfitController extends Controller
{
    public function getProfitByMonth(GetProfitByMonthRequest $request)
    {
        $branch_id = auth()->user()->employee->branch_id;
        $expenses = Expenses::whereMonth("created_at", $request->input("month"))
            ->where("branch_id", $branch_id)
            ->whereNull("deleted_at")
            ->sum("amount");

        $revenue = Cart::whereNull("deleted_at")
            ->whereHas("order", function ($query) use ($request, $branch_id) {
                $query->where("branch_id", $branch_id)
                    ->where("created_at", $request->input("month"))
                    ->whereNull("deleted_at");
            })
            ->sum("total_price");

        // $revenue = DB::table('orders as o')
        //     ->join('carts as c', 'c.order_id', '=', 'o.id')
        //     ->select(
        //         DB::raw('SUM(c.total_price) as total')
        //     )
        //     ->where('o.branch_id', $branch_id)
        //     ->whereMonth('o.created_at', $request->input("month"))
        //     ->whereNull('o.deleted_at')
        //     ->whereNull('c.deleted_at')
        //     ->get()
        //     ->first();

        $profit = ($revenue - $expenses);
        return $profit > 0 ? '₱' . number_format($profit, 2) : "₱0.00";
    }

    public function getWholeYearProfit(GetWholeYearProfitRequest $request)
    {
        $sales = DB::table('orders as o')
            ->join('carts as c', 'c.order_id', '=', 'o.id')
            ->select(
                DB::raw('AVG(c.total_price) as ave'),
                DB::raw("YEAR(o.processed) as year"),
                DB::raw("MONTH(o.processed) as month")
            )
            ->where('o.branch_id', auth()->user()->employee->branch_id)
            ->whereYear('o.processed', $request->year)
            ->whereNull('o.deleted_at')
            ->whereNull('c.deleted_at')
            ->groupBy('month', 'year')
            ->orderBy('month', 'ASC')
            ->get();

        return $sales;
    }
}
