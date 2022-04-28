<?php

namespace App\Http\Controllers;

use App\Http\Requests\CountPayrollRequest;
use App\Http\Requests\IndexPayrollRequest;
use App\Http\Requests\StorePayrollRequest;
use App\Http\Requests\UpdatePayrollRequest;
use App\Models\Attendance;
use App\Models\Deduction;
use App\Models\Overtime;
use App\Models\Payroll;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(IndexPayrollRequest $request)
    {
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

    public function count(CountPayrollRequest $request)
    {
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
    public function store(StorePayrollRequest $request)
    {
        $employee_id = $request->input('employee_id');
        $start = $request->input('from');
        $end = $request->input('to');

        $overtime = Overtime::with('attendance')
            ->select(
                DB::raw('sum(rate) as rate'),
                DB::raw('sum(hours) as hours'),
            )
            ->whereHas('attendance', function ($query) use ($start, $end) {
                $query->whereDate('attend_date', '>=', $start)
                    ->whereDate('attend_date', '<=', $end);
            })
            ->where('employee_id', $employee_id)
            ->whereNull('deleted_at')
            ->first();

        if ($overtime) {
            $attendance = Attendance::select(DB::raw('sum(abs(subtime(time_in,time_out))) as hours'))
                ->whereDate('attend_date', '>=', $start)
                ->whereDate('attend_date', '<=', $end)
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

        return response('Failed.', 400);
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePayrollRequest $request, $id)
    {
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

        return $payroll ? response("Successfully updated.")
            : response('Failed.', 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Payroll::find($id)->delete();

        return response('Successfully deleted.');
    }
}
