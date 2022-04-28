<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetWholeYearRevenueRequest;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class RevenueController extends Controller
{
    public function getRevenueByMonth()
    {
        $orders = Order::withSum('carts', 'total_price')
            ->where('branch_id', auth()->user()->employee->branch_id)
            ->whereMonth('created_at', date('m'))
            ->get()
            ->toArray();

        $revenue = array_reduce($orders, function ($carry, $item) {
            return $carry += $item['carts_sum_total_price'];
        }, 0);

        return '₱' . number_format($revenue, 2);
    }

    public function getWholeYearRevenue(GetWholeYearRevenueRequest $request)
    {
        $branch_id = is_null($request->input('branch_id')) ? auth()->user()->employee->branch_id : $request->input('branch_id');

        $revenue = DB::table('orders as o')
            ->join('carts as c', 'c.order_id', '=', 'o.id')
            ->select(
                DB::raw('SUM(c.total_price) as total'),
                DB::raw("YEAR(o.created_at) as year"),
                DB::raw("MONTH(o.created_at) as month")
            )
            ->where('o.branch_id', $branch_id)
            ->whereYear('o.created_at', $request->year)
            ->whereNull('o.deleted_at')
            ->whereNull('c.deleted_at')
            ->groupBy('month', 'year')
            ->orderBy('month', 'ASC')
            ->get();

        return $revenue;
    }
}
