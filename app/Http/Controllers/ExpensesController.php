<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExpenseResource;
use App\Import\ExpenseImport;
use App\Models\Employee;
use App\Models\Expenses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpensesController extends Controller
{

    public function importCSV(Request $request)
    {
        (new ExpenseImport)->queue($request->file('csv'), null, \Maatwebsite\Excel\Excel::CSV);
        return response('Successfully imported.', 201);
    }

    public function exportCSV()
    {
        $filename = 'expenses.csv';
        $expenses = Expenses::all();

        $columns = [
            'id',
            'branch_id',
            'name',
            'amount',
            'status'
        ];

        $file = fopen($filename, 'w');
        fputcsv($file, $columns);

        foreach ($expenses as $expense) {
            $row = [];

            foreach ($columns as $column) {
                $row[$column] = $expense[$column];
            }

            fputcsv($file, $row);
        }

        fclose($file);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($filename));
        header('Content-Transfer-Encoding: binary');
        header('Access-Control-Allow-Origin: *');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        ob_clean();
        flush();
        readfile($filename);
        unlink($filename);
    }

    public function index(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date'
        ]);

        $branch_id = auth()->user()->employee->branch_id;
        $start = $request->input('from');
        $end = $request->input('to');

        return ExpenseResource::collection(
            Expenses::whereDate('created_at', '>=', $start)
                ->whereDate('created_at', '<=', $end)
                ->where('branch_id', $branch_id)
                ->whereNull('deleted_at')
                ->paginate(10)
        );
    }

    public function getExpensesByMonth(Request $request)
    {
        $request->validate([
            'month' => 'required|numeric|min:1|max:12'
        ]);

        $branch_id = auth()->user()->employee->branch_id;
        $month = $request->input('month');

        if ($branch_id) {
            $expenses = Expenses::select(
                DB::raw("CONCAT('₱',FORMAT(AVG(amount), 2)) as ave")
            )
                ->where('branch_id', $branch_id)
                ->whereMonth('created_at', $month)
                ->whereNull('deleted_at')
                ->first();

            $ave = $expenses->ave;

            if ($ave) {
                return response($ave, 200);
            }
        }

        return response('₱0', 200);
    }

    public function getWholeYearExpenses(Request $request)
    {
        $request->validate([
            'year' => 'required|digits:4|integer|min:1900|max:' . (date('Y') + 1)
        ]);

        $branch_id = auth()->user()->employee->branch_id;
        $year = $request->input('year');

        if ($branch_id) {
            return Expenses::select(
                DB::raw('AVG(amount) as ave'),
                DB::raw("YEAR(created_at) as year"),
                DB::raw("MONTH(created_at) as month")
            )
                ->where('branch_id', $branch_id)
                ->whereYear('created_at', $year)
                ->whereNull('deleted_at')
                ->groupBy('month', 'year')
                ->orderBy('month', 'ASC')
                ->get();
        }

        return response([], 200);
    }

    public function count(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date'
        ]);

        $branch_id = auth()->user()->employee->branch_id;
        $start = $request->input('from');
        $end = $request->input('to');

        $amount = DB::table('expenses')
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->where('branch_id', $branch_id)
            ->whereNull('deleted_at')
            ->sum('amount');

        return is_int((int)$amount) ? '₱' . number_format($amount, 2) : '₱0';
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
            'name' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        $branch_id = auth()->user()->employee->branch_id;
        $name = $request->input('name');
        $amount = $request->input('amount');

        Expenses::create([
            'branch_id' => $branch_id,
            'name' => $name,
            'amount' => $amount
        ]);
        return response('Successfully added.', 201);
    }


    public function report(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|numeric|exists:branches,id',
            'employee_id' => 'required|numeric|exists:employees,id',
            'start' => 'required|date',
            'end' => 'required|date'
        ]);

        $employee_id = $request->employee_id;
        $purpose = $request->purpose;
        $branch_id = $request->branch_id;
        $start = $request->start;
        $end = $request->end;

        $employee = Employee::select(
            'id',
            'firstname',
            'lastname'
        )
            ->where('id', $employee_id)
            ->whereNull('deleted_at')
            ->first();

        if ($employee && preg_match("/^\d{4}-\d{2}-\d{2}$/", $start) && preg_match("/^\d{4}-\d{2}-\d{2}$/", $end)) {
            $expenses = [];
            if ($start !== $end) {
                if ($branch_id === "1" || $branch_id === 1) {
                    $expenses = Expenses::whereBetween('created_at', [$start, $end])
                        ->whereNull('deleted_at')
                        ->get();
                } else {
                    $expenses = Expenses::whereBetween('created_at', [$start, $end])
                        ->where('branch_id', $branch_id)
                        ->whereNull('deleted_at')
                        ->get();
                }
            } else {
                if ($branch_id === "1" || $branch_id === 1) {
                    $expenses = Expenses::whereDate('created_at', $start)
                        ->whereNull('deleted_at')
                        ->get();
                } else {
                    $expenses = Expenses::whereDate('created_at', $start)
                        ->where('branch_id', $branch_id)
                        ->whereNull('deleted_at')
                        ->get();
                }
            }

            $total = 0;

            foreach ($expenses as $key => $value) {
                $total += $value->amount;
            }

            return response([
                'expenses' => $expenses,
                'total' => $total,
                'employee' => $employee,
                'purpose' => $purpose
            ], 200);
        }
        return response([
            'expenses' => [],
            'total' => 0,
            'employee' => null,
            'purpose' => $purpose
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    public function approve($id)
    {
        Expenses::where('id', $id)
            ->update([
                'status' => "APPROVED"
            ]);
        return response('Successfully updated.', 200);
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
            'branch_id' => 'required|numeric|exists:branches,id',
            'name' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0'
        ]);

        $expenses = Expenses::where('id', $id)
            ->where('branch_id', $request->branch_id)
            ->whereNull('deleted_at')
            ->update([
                'name' => $request->name,
                'amount' => $request->amount
            ]);

        if ($expenses) {
            return response('Successfully updated.', 200);
        }

        return response('Record not found.', 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $expenses = Expenses::find($id);

        if ($expenses) {
            $expenses->delete();
            return response('Successfully deleted.', 200);
        }

        return response('Record not deleted.', 400);
    }
}
