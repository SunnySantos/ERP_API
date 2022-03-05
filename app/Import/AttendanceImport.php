<?php

namespace App\Import;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Overtime;
use DateTime;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class AttendanceImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, ShouldQueue
{

    use Importable;

    public function model(array $row)
    {
        $employee = Employee::find($row['employee_id']);
        $schedule = DB::table('schedules as a')
            ->join('employees as b', 'a.id', '=', 'b.schedule_id')
            ->select('a.time_out')
            ->where('b.id', $row['employee_id'])
            ->whereNull('a.deleted_at')
            ->first();
        if ($employee) {
            $overtime = $this->getTimeDiff($row['time_out'], $schedule->time_out);

            Attendance::create([
                'employee_id' => $row['employee_id'],
                'attend_date' => date('Y-m-d', strtotime($row['attend_date'])),
                'time_in' => $row['time_in'],
                'time_out' => $row['time_out'],
                'overtime' => $overtime
            ]);

            if ($overtime > 0) {
                Overtime::create([
                    'employee_id' => $row['employee_id'],
                    'overtime_date' => date('Y-m-d', strtotime($row['attend_date'])),
                    'hours' => $overtime,
                    'rate' => 0
                ]);
            }
        }
    }

    public function getTimeDiff($in, $out)
    {
        $in = new DateTime($in);
        $out = new DateTime($out);
        return $in->diff($out)->format('%h');
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
