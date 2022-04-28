<?php

namespace App\Import;

use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\Schedule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class EmployeeScheduleImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, ShouldQueue
{

    use Importable;

    public function model(array $row)
    {
        $employee = Employee::where('id', $row['employee_id'])
            ->where('branch_id', auth()->user()->employee->branch_id)
            ->whereNull('deleted_at')
            ->first();
        $schedule = Schedule::where('time_in', $row['time_in'])
            ->where('time_out', $row['time_out'])
            ->first();

        if ($employee && $schedule) {
            EmployeeSchedule::create([
                'employee_id' => $employee->id,
                'attend_date' => date('Y-m-d', strtotime($row['attend_date'])),
                'time_in' => $schedule->time_in,
                'time_out' => $schedule->time_out,
            ]);
        }
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
