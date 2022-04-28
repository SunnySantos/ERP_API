<?php

namespace App\Http\Controllers;

use App\Http\Requests\AveHoursRequest;
use App\Http\Requests\IndexAttendanceRequest;
use App\Http\Requests\MaxHoursRequest;
use App\Http\Requests\MinHoursRequest;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\TotalHoursRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Import\AttendanceImport;
use App\Models\Attendance;
use App\Models\EmployeeSchedule;
use App\Models\Overtime;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDOException;

class AttendanceController extends Controller
{
    public function importCSV(Request $request)
    {
        (new AttendanceImport)->queue($request->file('csv'), null, \Maatwebsite\Excel\Excel::CSV);
        return response('Successfully imported.', 201);
    }

    public function exportCSV(IndexAttendanceRequest $request)
    {
        $branch_id = auth()->user()->employee->branch_id;
        $filename = 'attendances.csv';
        $attendances = Attendance::with('employee')
            ->whereHas('employee', function ($query) use ($branch_id) {
                $query->where('branch_id', $branch_id);
            })
            ->whereDate('attend_date', '>=', $request->input("from"))
            ->whereDate('attend_date', '<=', $request->input("to"))
            ->whereNull('deleted_at')
            ->orderBy('attend_date', 'ASC')
            ->get();

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
    public function index(IndexAttendanceRequest $request)
    {
        $branch_id = auth()->user()->employee->branch_id;

        return AttendanceResource::collection(
            Attendance::with('employee')
                ->whereHas('employee', function ($query) use ($branch_id) {
                    $query->where('branch_id', $branch_id);
                })
                ->whereDate('attend_date', '>=', $request->input("from"))
                ->whereDate('attend_date', '<=', $request->input("to"))
                ->whereNull('deleted_at')
                ->paginate(10)
        );
    }

    public function maxHours(MaxHoursRequest $request)
    {
        $branch_id = auth()->user()->employee->branch_id;

        $max = Attendance::with('employee')
            ->select(DB::raw('max(abs(subtime(time_in,time_out))) as hours'))
            ->whereHas('employee', function ($query) use ($branch_id) {
                $query->where('branch_id', $branch_id);
            })
            ->whereDate('attend_date', '>=', $request->input("from"))
            ->whereDate('attend_date', '<=', $request->input("to"))
            ->whereNull('deleted_at')
            ->first();

        $max = $max->hours / 10000;
        return $max > 0 ? response(number_format($max, 2)) : response(0);
    }

    public function minHours(MinHoursRequest $request)
    {
        $branch_id = auth()->user()->employee->branch_id;

        $min = Attendance::with('employee')
            ->select(DB::raw('min(abs(subtime(time_in,time_out))) as hours'))
            ->whereHas('employee', function ($query) use ($branch_id) {
                $query->where('branch_id', $branch_id);
            })
            ->whereDate('attend_date', '>=', $request->input("from"))
            ->whereDate('attend_date', '<=', $request->input("to"))
            ->whereNull('deleted_at')
            ->first();

        $min = $min->hours / 10000;
        return $min > 0 ? response(number_format($min, 2)) : response(0);
    }

    public function aveHours(AveHoursRequest $request)
    {
        $branch_id = auth()->user()->employee->branch_id;

        $ave = Attendance::with('employee')
            ->select(DB::raw('floor(avg(abs(subtime(time_in,time_out)))) as hours'))
            ->whereHas('employee', function ($query) use ($branch_id) {
                $query->where('branch_id', $branch_id);
            })
            ->whereDate('attend_date', '>=', $request->input("from"))
            ->whereDate('attend_date', '<=', $request->input("to"))
            ->whereNull('deleted_at')
            ->first();

        $ave = $ave->hours / 10000;
        return $ave > 0 ? response(number_format($ave, 2)) : response(0);
    }

    public function totalHours(TotalHoursRequest $request)
    {
        $branch_id = auth()->user()->employee->branch_id;

        $sum =  Attendance::with('employee')
            ->select(DB::raw('sum(abs(subtime(time_in,time_out))) as hours'))
            ->whereHas('employee', function ($query) use ($branch_id) {
                $query->where('branch_id', $branch_id);
            })
            ->whereDate('attend_date', '>=', $request->input("from"))
            ->whereDate('attend_date', '<=', $request->input("to"))
            ->whereNull('deleted_at')
            ->first();

        $sum = $sum->hours / 10000;
        return $sum > 0 ? response(number_format($sum, 2)) : response(0);
    }

    public function presentToday()
    {
        $count = Attendance::whereDate('attend_date', now())
            ->whereNull('deleted_at')
            ->count();
        return number_format($count);
    }

    public function absentToday()
    {
        $employeeScheduleCount = EmployeeSchedule::whereDate('attend_date', now())
            ->whereNull('deleted_at')
            ->count();

        $attendanceCount = Attendance::whereDate('attend_date', now())
            ->whereNull('deleted_at')
            ->count();

        return number_format($employeeScheduleCount - $attendanceCount);
    }

    public function chart(Request $request)
    {
        $request->validate([
            'year' => 'required'
        ]);

        $branch_id = auth()->user()->employee->branch_id;

        $attendances = Attendance::select(
            DB::raw("COUNT(id) as present"),
            DB::raw("YEAR(attend_date) as year"),
            DB::raw("MONTH(attend_date) as month")
        )
            ->whereHas('employee', function ($query) use ($branch_id) {
                $query->where('branch_id', $branch_id);
            })
            ->whereYear('attend_date', $request->input('year'))
            ->whereNull('deleted_at')
            ->groupBy("month", "year")
            ->orderBy("month", "ASC")
            ->get();

        $employeeSchedules =  EmployeeSchedule::select(
            DB::raw("COUNT(id) as count"),
            DB::raw("YEAR(attend_date) as year"),
            DB::raw("MONTH(attend_date) as month")
        )
            ->whereHas('employee', function ($query) use ($branch_id) {
                $query->where('branch_id', $branch_id);
            })
            ->whereIn(DB::raw("YEAR(attend_date)"), $attendances->pluck("year"))
            ->whereIn(DB::raw("MONTH(attend_date)"), $attendances->pluck("month"))
            ->whereNull('deleted_at')
            ->groupBy("month", "year")
            ->orderBy("month", "ASC")
            ->get();

        foreach ($attendances as $key => $attendance) {
            foreach ($employeeSchedules as $key => $employeeSchedule) {
                if ($attendance->month == $employeeSchedule->month) {
                    $attendance['absent'] = ($employeeSchedule->count - $attendance->present);
                }
            }
        }

        return $attendances;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAttendanceRequest $request)
    {
        $employeeSchedule = EmployeeSchedule::with('employee')
            ->select('time_out')
            ->whereHas('employee', function ($query) use ($request) {
                $query->where('id', $request->input('employee_id'));
            })
            ->where('attend_date', $request->input('attend_date'))
            ->whereNull('deleted_at')
            ->first();

        if ($employeeSchedule) {
            try {
                $result = DB::transaction(function () use ($request, $employeeSchedule) {
                    $overtime = $this->getTimeDiff($request->time_out, $employeeSchedule->time_out);
                    $attendance =  Attendance::create($request->all());
                    if ($overtime > 0) {
                        Overtime::create([
                            'employee_id' => $request->input('employee_id'),
                            'attendance_id' => $attendance->id,
                            'hours' => $overtime,
                            'rate' => 0
                        ]);
                    }
                    return "Successfully created.";
                });

                return response($result, 201);
            } catch (Exception $e) {
                return response("Failed.", 400);
            }
        }
        return response("No schedule.", 400);
    }

    public function getTimeDiff($out1, $out2)
    {
        $out1 = new DateTime($out1);
        $out2 = new DateTime($out2);

        return $out1 > $out2 ? $out1->diff($out2)->format('%h') : 0;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return AttendanceResource::make(Attendance::find($id));
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAttendanceRequest $request, $id)
    {
        $employeeSchedule = EmployeeSchedule::with('employee')
            ->select('time_out')
            ->whereHas('employee', function ($query) use ($request) {
                $query->where('id', $request->input('employee_id'));
            })
            ->whereNull('deleted_at')
            ->first();

        if ($employeeSchedule) {
            try {
                $result = DB::transaction(function () use ($request, $employeeSchedule, $id) {
                    $overtime = $this->getTimeDiff($request->time_out, $employeeSchedule->time_out);

                    $attendance = Attendance::where('id', $id)
                        ->whereNull('deleted_at')
                        ->update([
                            'employee_id' => $request->employee_id,
                            'attend_date' => $request->attend_date,
                            'time_in' => $request->time_in,
                            'time_out' => $request->time_out,
                            'overtime' => $overtime
                        ]);

                    if (!$attendance) throw new Exception("Failed.");


                    if ($overtime > 0) {
                        $overtime = Overtime::where('attendance_id', $attendance->id)
                            ->whereNull('deleted_at')
                            ->update([
                                'hours' => $overtime,
                            ]);

                        if (!$overtime) {
                            Overtime::create([
                                'employee_id' => $request->input('employee_id'),
                                'attendance_id' => $attendance->id,
                                'hours' => $overtime,
                                'rate' => 0
                            ]);
                        }
                    }

                    return "Successfully updated.";
                });

                return response($result);
            } catch (PDOException $e) {
                return response("Failed.", 400);
            } catch (Exception $e) {
                return response("Failed.", 400);
            }
        }

        return response("No schedule.", 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Attendance::find($id)->forceDelete();
        return response("Successfully deleted.");
    }


    // public function search(SearchAttendanceRequest $request)
    // {
    //     $branch_id = auth()->user()->employee->branch_id;
    //     $start = $request->input('start');
    //     $end = $request->input('end');


    //     $data = DB::table('attendances as a')
    //         ->join('employees as e', 'a.employee_id', '=', 'e.id')
    //         ->join('branches as b', 'e.branch_id', '=', 'b.id')
    //         ->select(
    //             'a.employee_id',
    //             DB::raw('floor(sum(abs(subtime(a.time_in,a.time_out)))/10000) as hours'),
    //             DB::raw('sum(a.overtime) as ots')
    //         )
    //         ->whereBetween('a.attend_date', [$start, $end])
    //         ->where('b.id', $branch_id)
    //         ->whereNull('a.deleted_at')
    //         // ->where('a.employee_id', $request->employee_id)
    //         ->groupBy('a.employee_id')
    //         ->first();

    //     if ($data !== null) {
    //         $employee = $this->getEmployeeById($data->employee_id);
    //         $data->firstname = $employee->firstname;
    //         $data->lastname = $employee->lastname;
    //         return $data;
    //     }

    //     return response('No record found.', 400);
    // }


    // public function showByBranchId(Request $request)
    // {
    //     $request->validate([
    //         'from' => 'required|date',
    //         'to' => 'required|date'
    //     ]);

    //     $branch_id = auth()->user()->employee->branch_id;
    //     $from = $request->from;
    //     $to = $request->to;

    //     return DB::table('attendances as a')
    //         ->join('employees as e', 'a.employee_id', '=', 'e.id')
    //         ->select(
    //             'a.id',
    //             'a.employee_id',
    //             'a.attend_date',
    //             'a.time_in',
    //             'a.time_out',
    //             'a.overtime',
    //             'e.firstname',
    //             'e.lastname'
    //         )
    //         ->whereBetween('a.attend_date', [$from, $to])
    //         // ->where('e.branch_id', $branch_id)
    //         ->whereNull('a.deleted_at')
    //         ->paginate(10);

    //     return tap(
    //         DB::table('attendances as a')
    //             ->join('employees as e', 'a.employee_id', '=', 'e.id')
    //             ->select(
    //                 'a.id',
    //                 'a.employee_id',
    //                 'a.attend_date',
    //                 'a.time_in',
    //                 'a.time_out',
    //                 'a.overtime',
    //                 'e.firstname',
    //                 'e.lastname'
    //             )
    //             ->whereBetween('a.attend_date', [$from, $to])
    //             ->where('e.branch_id', $branch_id)
    //             ->whereNull('a.deleted_at')
    //             ->paginate(10),
    //         function ($paginateInstance) {
    //             return $paginateInstance->getCollection()->transform(function ($value) {
    //                 $time = explode(':', $value->time_in);
    //                 $value->time_in = $time[0] . ':' . $time[1];
    //                 $time = explode(':', $value->time_out);
    //                 $value->time_out = $time[0] . ':' . $time[1];
    //                 return $value;
    //             });
    //         }
    //     );
    // }
}
