<?php

namespace App\Http\Controllers;

use App\Import\AttendanceImport;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Overtime;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function importCSV(Request $request)
    {
        (new AttendanceImport)->queue($request->file('csv'), null, \Maatwebsite\Excel\Excel::CSV);
        return response('Successfully imported.', 201);
    }

    public function exportCSV()
    {
        $filename = 'attendances.csv';
        $attendances = Attendance::all();

        $columns = [
            'employee_id',
            'attend_date',
            'time_in',
            'time_out',
            'overtime'
        ];

        $file = fopen($filename, 'w');
        fputcsv($file, $columns);

        foreach ($attendances as $attendance) {
            $row = [];

            foreach ($columns as $column) {
                $row[$column] = $attendance[$column];
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


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    public function maxHours(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date'
        ]);

        $branch_id = auth()->user()->employee->branch_id;
        $from = $request->from;
        $to = $request->to;

        $max = DB::table('attendances as a')
            ->join('employees as e', 'a.employee_id', '=', 'e.id')
            ->select(DB::raw('max(abs(subtime(a.time_in,a.time_out))) as hours'))
            ->whereBetween('a.attend_date', [$from, $to])
            ->where('e.branch_id', $branch_id)
            ->get()
            ->first();
        $max = $max->hours / 10000;
        if ($max > 0) {
            return response($max, 200);
        }
        return response(0, 200);
    }

    public function minHours(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date'
        ]);

        $branch_id = auth()->user()->employee->branch_id;

        $from = $request->from;
        $to = $request->to;

        $min = DB::table('attendances as a')
            ->join('employees as e', 'a.employee_id', '=', 'e.id')
            ->select(DB::raw('min(abs(subtime(a.time_in,a.time_out))) as hours'))
            ->whereBetween('a.attend_date', [$from, $to])
            ->where('e.branch_id', $branch_id)
            ->get()
            ->first();
        $min = $min->hours / 10000;
        if ($min > 0) {
            return response($min, 200);
        }
        return response(0, 200);
    }

    public function aveHours(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date'
        ]);

        $from = $request->from;
        $to = $request->to;
        $branch_id = auth()->user()->employee->branch_id;

        $ave = DB::table('attendances as a')
            ->join('employees as e', 'a.employee_id', '=', 'e.id')
            ->select(DB::raw('floor(avg(abs(subtime(time_in,time_out)))) as hours'))
            ->whereBetween('a.attend_date', [$from, $to])
            ->where('e.branch_id', $branch_id)
            ->get()
            ->first();
        $ave = $ave->hours / 10000;
        if ($ave > 0) {
            return response($ave, 200);
        }
        return response(0, 200);
    }

    public function totalHours(Request $request)
    {

        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date'
        ]);

        $branch_id = auth()->user()->employee->branch_id;
        $from = $request->from;
        $to = $request->to;

        $sum = DB::table('attendances as a')
            ->join('employees as e', 'a.employee_id', '=', 'e.id')
            ->select(DB::raw('sum(abs(subtime(time_in,time_out))) as hours'))
            ->whereBetween('a.attend_date', [$from, $to])
            ->where('e.branch_id', $branch_id)
            ->get()
            ->first();
        $sum = $sum->hours / 10000;
        if ($sum > 0) {
            return response($sum, 200);
        }
        return response(0, 200);
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
            'attend_date' => 'required|date',
            'time_in' => 'required|date_format:H:i',
            'time_out' => 'required|date_format:H:i'
        ]);

        $schedule = DB::table('schedules as a')
            ->join('employees as b', 'a.id', '=', 'b.schedule_id')
            ->select('a.time_out')
            ->where('b.id', $request->employee_id)
            ->whereNull('a.deleted_at')
            ->first();

        if ($schedule) {
            $overtime = $this->getTimeDiff($request->time_out, $schedule->time_out);

            if ($overtime > 0) {
                Overtime::create([
                    'employee_id' => $request->employee_id,
                    'overtime_date' => $request->attend_date,
                    'hours' => $overtime,
                    'rate' => 0
                ]);
            }
            $request['overtime'] = $overtime;
            Attendance::create($request->all());
        }


        return response("Successfully added.", 201);
    }

    public function getTimeDiff($in, $out)
    {
        $in = new DateTime($in);
        $out = new DateTime($out);
        return $in->diff($out)->format('%h');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Attendance::find($id);
    }

    public function search(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|numeric|exists:employees,id',
            'start' => 'required|date',
            'end' => 'required|date'
        ]);

        $branch_id = auth()->user()->employee->branch_id;
        $start = $request->input('start');
        $end = $request->input('end');


        $data = DB::table('attendances as a')
            ->join('employees as e', 'a.employee_id', '=', 'e.id')
            ->join('branches as b', 'e.branch_id', '=', 'b.id')
            ->select(
                'a.employee_id',
                DB::raw('floor(sum(abs(subtime(a.time_in,a.time_out)))/10000) as hours'),
                DB::raw('sum(a.overtime) as ots')
            )
            ->whereBetween('a.attend_date', [$start, $end])
            ->where('b.id', $branch_id)
            ->whereNull('a.deleted_at')
            // ->where('a.employee_id', $request->employee_id)
            ->groupBy('a.employee_id')
            ->first();

        if ($data !== null) {
            $employee = $this->getEmployeeById($data->employee_id);
            $data->firstname = $employee->firstname;
            $data->lastname = $employee->lastname;
            return $data;
        }

        return response('No record found.', 400);
    }

    public function getEmployeeById($id)
    {
        return Employee::select(
            'firstname',
            'lastname'
        )
            ->where('id', $id)
            ->get()
            ->first();
    }

    public function showByBranchId(Request $request)
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date'
        ]);

        $branch_id = auth()->user()->employee->branch_id;
        $from = $request->from;
        $to = $request->to;

        return DB::table('attendances as a')
            ->join('employees as e', 'a.employee_id', '=', 'e.id')
            ->select(
                'a.id',
                'a.employee_id',
                'a.attend_date',
                'a.time_in',
                'a.time_out',
                'a.overtime',
                'e.firstname',
                'e.lastname'
            )
            ->whereBetween('a.attend_date', [$from, $to])
            // ->where('e.branch_id', $branch_id)
            ->whereNull('a.deleted_at')
            ->paginate(10);

        return tap(
            DB::table('attendances as a')
                ->join('employees as e', 'a.employee_id', '=', 'e.id')
                ->select(
                    'a.id',
                    'a.employee_id',
                    'a.attend_date',
                    'a.time_in',
                    'a.time_out',
                    'a.overtime',
                    'e.firstname',
                    'e.lastname'
                )
                ->whereBetween('a.attend_date', [$from, $to])
                ->where('e.branch_id', $branch_id)
                ->whereNull('a.deleted_at')
                ->paginate(10),
            function ($paginateInstance) {
                return $paginateInstance->getCollection()->transform(function ($value) {
                    $time = explode(':', $value->time_in);
                    $value->time_in = $time[0] . ':' . $time[1];
                    $time = explode(':', $value->time_out);
                    $value->time_out = $time[0] . ':' . $time[1];
                    return $value;
                });
            }
        );
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
            'employee_id' => 'required|numeric|exists:employees,id',
            'attend_date' => 'required|date',
            'time_in' => 'required',
            'time_out' => 'required'
        ]);

        $attendance1 = DB::table('schedules as a')
            ->join('employees as b', 'a.id', '=', 'b.schedule_id')
            ->select('a.time_out')
            ->where('b.id', $request->employee_id)
            ->whereNull('a.deleted_at')
            ->first();



        if ($attendance1) {
            $ot = $this->getTimeDiff($request->time_out, $attendance1->time_out);

            Attendance::where('id', $id)
                ->whereNull('deleted_at')
                ->update([
                    'employee_id' => $request->employee_id,
                    'attend_date' => $request->attend_date,
                    'time_in' => $request->time_in,
                    'time_out' => $request->time_out,
                    'overtime' => $ot
                ]);


            if ($ot > 0) {
                $overtime = Overtime::where('overtime_date', $request->attend_date)
                    ->whereNull('deleted_at')
                    ->update([
                        'hours' => $ot,
                    ]);

                if (!$overtime) {
                    Overtime::create([
                        'employee_id' => $request->employee_id,
                        'overtime_date' => $request->attend_date,
                        'hours' => $ot,
                        'rate' => 0
                    ]);
                }
            }

            return response("Successfully updated.", 200);
        }

        return response("Failed.", 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $attendance = Attendance::find($id);

        if ($attendance) {
            $attendance->delete();
            return response("Successfully deleted.", 200);
        }
        return response("Record does not exists.", 400);
    }
}
