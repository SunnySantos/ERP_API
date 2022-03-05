<?php

namespace App\Http\Controllers;

use App\Import\ScheduleImport;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{

    public function dropdown()
    {
        return Schedule::whereNull('deleted_at')
            ->get();
    }

    public function importCSV(Request $request)
    {
        (new ScheduleImport)->queue($request->file('csv'), null, \Maatwebsite\Excel\Excel::CSV);
        return response('Successfully imported.', 201);
    }

    public function exportCSV()
    {
        $filename = 'schedules.csv';
        $schedules = Schedule::all();

        $columns = [
            'id',
            'time_in',
            'time_out'
        ];

        $file = fopen($filename, 'w');
        fputcsv($file, $columns);

        foreach ($schedules as $schedule) {
            $row = [];

            foreach ($columns as $column) {
                $row[$column] = $schedule[$column];
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
        return Schedule::whereNull('deleted_at')
            ->paginate(10);
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
            'time_in' => 'required|string',
            'time_out' => 'required|string'
        ]);

        Schedule::create($request->all());
        return response('Successfully created.', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Schedule::where('id', $id)
            ->whereNull('deleted_at')
            ->first();
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
            'time_in' => 'required|string',
            'time_out' => 'required|string'
        ]);

        $schedule = Schedule::where('id', $id)
            ->update([
                'time_in' => $request->time_in,
                'time_out' => $request->time_out,
            ]);

        if ($schedule) {
            return response('Successfully updated.', 200);
        }
        return response('Failed.', 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $schedule = Schedule::find($id);

        if ($schedule) {
            $schedule->delete();
            return response('Successfully deleted.', 200);
        }
        return response('Failed.', 400);
    }
}
