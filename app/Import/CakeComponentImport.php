<?php

namespace App\Import;

use App\Models\CakeComponent;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CakeComponentImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, ShouldQueue
{

    use Importable;

    public function model(array $row)
    {
        CakeComponent::create([
            'cake_model_id' => $row['cake_model_id'],
            'name' => $row['name'],
            'size' => $row['size'],
            'category' => $row['category'],
            'shape' => $row['shape'],
            'cost' => $row['cost']
        ]);
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
