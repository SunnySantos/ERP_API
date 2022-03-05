<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Deduction;
use App\Models\Employee;
use App\Models\Overtime;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date'
        ]);

        $start = $request->input('from');
        $end = $request->input('to');
        $branch_id = auth()->user()->employee->branch_id;

        return DB::table('payrolls as p')
            ->join('employees as e', 'p.employee_id', '=', 'e.id')
            ->join('positions as pp', 'e.position_id', '=', 'pp.id')
            ->select(
                'p.id',
                'p.employee_id',
                'e.firstname',
                'e.lastname',
                'p.gross',
                'p.deduction',
                'p.net',
                'pp.rate',
                'p.total_hours',
                'p.start',
                'p.end'
            )
            ->whereDate('p.created_at', '>=', $start)
            ->whereDate('p.created_at', '<=', $end)
            ->where('e.branch_id', $branch_id)
            ->whereNull('p.deleted_at')
            ->orderBy('p.id', 'DESC')
            ->paginate(10);
    }

    public function count(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date'
        ]);

        $start = $request->input('from');
        $end = $request->input('to');
        $branch_id = auth()->user()->employee->branch_id;

        return DB::table('payrolls as p')
            ->join('employees as e', 'p.employee_id', '=', 'e.id')
            ->whereDate('p.created_at', '>=', $start)
            ->whereDate('p.created_at', '<=', $end)
            ->where('e.branch_id', $branch_id)
            ->whereNull('p.deleted_at')
            ->count();
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
            'employee_id' => 'required|numeric|exists:employees,id',
            'from' => 'required|date',
            'to' => 'required|date',
        ]);

        $employee_id = $request->input('employee_id');
        $start = $request->input('from');
        $end = $request->input('to');

        $overtime = Overtime::select(
            DB::raw('sum(rate) as rate'),
            DB::raw('sum(hours) as hours'),
        )
            ->where('employee_id', $employee_id)
            ->whereDate('overtime_date', '>=', $start)
            ->whereDate('overtime_date', '<=', $end)
            // ->whereBetween('overtime_date', [$request->from, $request->to])
            ->whereNull('deleted_at')
            ->first();

        if ($overtime) {
            $attendance = Attendance::select(DB::raw('sum(abs(subtime(time_in,time_out))) as hours'))
                ->whereDate('attend_date', '>=', $start)
                ->whereDate('attend_date', '<=', $end)
                // ->whereBetween('attend_date', [$request->from, $request->to])
                ->where('employee_id', $employee_id)
                ->whereNull('deleted_at')
                ->first();

            $sum = ($attendance->hours / 10000) - $overtime->hours;

            $position = DB::table('positions as a')
                ->join('employees as b', 'a.id', '=', 'b.position_id')
                ->select('a.rate')
                ->where('b.id', $employee_id)
                ->first();

            $gross = ($position->rate * $sum) + $overtime->rate;

            $deduction = Deduction::select(DB::raw('sum(amount) as amount'))
                ->whereNull('deleted_at')
                ->first();


            $payroll = Payroll::create([
                'employee_id' => $employee_id,
                'total_hours' => ($attendance->hours / 10000),
                'start' => $request->from,
                'end' => $request->to,
                'gross' => $gross,
                'deduction' => $deduction->amount,
                'net' => $gross - $deduction->amount
            ]);

            if ($payroll) {
                return response('Successfully created.', 201);
            }
        }

        return response('Record not saved.', 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function showByBranchId(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|numeric|exitst:branches,id',
            'start' => 'required|date',
            'end' => 'required|date'
        ]);

        return $request->all();
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
            'hour_rate' => 'required|between:0,99.99|min:0',
            'overtime_rate' => 'required|between:0,99.99|min:0',
            'deduction' => 'required|between:0,99.99|min:0',
        ]);

        $hour_rate = $request->input('hour_rate');
        $overtime_rate = $request->input('overtime_rate');
        $deduction = $request->input('deduction');

        $payroll = Payroll::where('id', $id)
            ->whereNull('deleted_at')
            ->update([
                'hour_rate' => $hour_rate,
                'overtime_rate' => $overtime_rate,
                'deduction' => $deduction
            ]);

        if ($payroll) {
            return response("Successfully updated.", 200);
        }
        return response('Failed to update.', 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $payroll = Payroll::find($id);

        if ($payroll) {
            $payroll->delete();
            return response('Successfully deleted.', 200);
        }
        return response('Record not found.', 400);
    }
}
