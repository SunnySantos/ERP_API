<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpensesReportRequest;
use App\Http\Requests\SalesReportRequest;
use App\Http\Resources\ExpenseReportResource;
use App\Models\Expenses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function sales(SalesReportRequest $request)
    {
        $employee = auth()->user()->employee;

        $records = DB::table('orders as a')
            ->join('carts as b', 'a.id', '=', 'b.order_id')
            ->join('products as c', 'c.id', '=', 'b.product_id')
            ->select(
                DB::raw("DATE(a.created_at) as created_at"),
                "c.name",
                "b.quantity",
                DB::raw("CONCAT('₱',FORMAT(b.total_price, 2)) as amount")
            )
            // ->where('a.branch_id', $employee->branch_id)
            // ->where('a.created_at', '>=', $request->from)
            // ->where('a.created_at', '<=', $request->to)
            ->whereBetween('a.created_at', [$request->from, $request->to])
            ->paginate(10);




        $total = DB::table('orders as a')
            ->join('carts as b', 'a.id', '=', 'b.order_id')
            ->join('products as c', 'c.id', '=', 'b.product_id')
            ->select(
                DB::raw("CONCAT('₱',FORMAT(SUM(b.total_price), 2)) as total")
            )
            // ->where('a.branch_id', $employee->branch_id)
            ->whereBetween('a.created_at', [$request->from, $request->to])
            ->whereNull('a.deleted_at')
            ->first();

        return response([
            'records' => $records,
            'total' => $total
        ], 200);
    }

    public function expenses(ExpensesReportRequest $request)
    {
        $start = $request->input('from');
        $end = $request->input('to');

        $expenses = [];
        $total = 0;

        if (preg_match("/^\d{4}-\d{2}-\d{2}$/", $start) && preg_match("/^\d{4}-\d{2}-\d{2}$/", $end)) {
            $expenses = Expenses::whereDate('created_at', '>=', $start)
                ->whereDate('created_at', '<=', $end)
                ->whereNull('deleted_at')
                ->paginate(10);

            $total = Expenses::select(
                DB::raw("CONCAT('₱',FORMAT(SUM(amount), 2)) as total")
            )->first();

            return response([
                'expenses' => ExpenseReportResource::collection($expenses)->response()->getData(),
                'total' => $total
            ], 200);
        }
        return response([
            'expenses' => $expenses,
            'total' => $total,
        ], 200);
    }
}
