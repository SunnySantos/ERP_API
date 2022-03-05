<?php

namespace App\Import;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Expenses;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ExpenseImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, ShouldQueue
{

    use Importable;

    public function model(array $row)
    {
        $branch_id = auth()->user()->employee->branch_id;

        if ($branch_id) {
            Expenses::create([
                'branch_id' => $branch_id,
                'name' => $row['name'],
                'amount' => $row['amount']
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
