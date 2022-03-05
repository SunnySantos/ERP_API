<?php

namespace App\Import;

use App\Models\Attendance;
use App\Models\Category;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ProductImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, ShouldQueue
{

    use Importable;

    public function model(array $row)
    {
        $category = Category::find($row['category_id']);
        if ($category) {
            Product::create([
                'name' => $row['name'],
                'description' => $row['description'],
                'category_id' => $row['category_id'],
                'price' => $row['price'],
                'cost' => $row['cost']
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
