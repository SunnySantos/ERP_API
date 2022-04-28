<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeScheduleRequest;
use App\Http\Resources\EmployeeScheduleResource;
use App\Import\EmployeeScheduleImport;
use App\Models\EmployeeSchedule;
use App\Models\Schedule;
use Illuminate\Http\Request;

class EmployeeScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $branch_id = auth()->user()->employee->branch_id;
        $search = $request->input('search');
        $employeeSchedules = EmployeeSchedule::with('employee')
            ->whereHas('employee', function ($query) use ($branch_id) {
                $query->where('branch_id', $branch_id);
            });

        if (!empty($search)) {
            $employeeSchedules->whereDate('attend_date', $search);
        }
        return EmployeeScheduleResource::collection(
            $employeeSchedules->whereNull('deleted_at')
                ->orderBy('id', 'DESC')
                ->paginate(10)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEmployeeScheduleRequest $request)
    {
        $schedule = Schedule::find($request->input('schedule_id'));

        if ($schedule) {
            EmployeeSchedule::create([
                'employee_id' => $request->input('employee_id'),
                'attend_date' => $request->input('attend_date'),
                'time_in' => $schedule->time_in,
                'time_out' => $schedule->time_out
            ]);

            return response("Successfully created.", 201);
        }

        return response("Failed.", 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return EmployeeScheduleResource::make(
            EmployeeSchedule::find($id)
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreEmployeeScheduleRequest $request, $id)
    {
        $schedule = Schedule::find($request->input('schedule_id'));

        if ($schedule) {
            $employeeSchedule = EmployeeSchedule::where('id', $id)
                ->update([
                    'employee_id' => $request->input('employee_id'),
                    'attend_date' => $request->input('attend_date'),
                    'time_in' => $schedule->time_in,
                    'time_out' => $schedule->time_out
                ]);
            if ($employeeSchedule) {
                return response("Successfully updated.");
            }
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
        EmployeeSchedule::find($id)->delete();

        return response("Successfully deleted.");
    }

    public function importCSV(Request $request)
    {
        (new EmployeeScheduleImport)->queue($request->file('csv'), null, \Maatwebsite\Excel\Excel::CSV);
        return response('Successfully imported.', 201);
    }

    public function exportCSV(Request $request)
    {
        $branch_id = auth()->user()->employee->branch_id;
        $filename = 'employee_schedules.csv';

        $search = $request->input('search');
        $employeeSchedules = EmployeeSchedule::with('employee')
            ->whereHas('employee', function ($query) use ($branch_id) {
                $query->where('branch_id', $branch_id);
            });

        if (!empty($search)) {
            $employeeSchedules->whereDate('attend_date', $search);
        }

        $employeeSchedules = $employeeSchedules->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->get();

        $columns = [
            'employee_id',
            'attend_date',
            'time_in',
            'time_out'
        ];

        $file = fopen($filename, 'w');
        fputcsv($file, $columns);

        foreach ($employeeSchedules as $employeeSchedule) {
            $row = [];

            foreach ($columns as $column) {
                $row[$column] = $employeeSchedule[$column];
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
}
