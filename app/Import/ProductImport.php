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
        $category = Category::where('name', $row['category'])->whereNull('deleted_at')->first();
        $product = Product::where('name', $row['name'])->first();
        if ($category && is_null($product)) {
            Product::create([
                'name' => $row['name'],
                'description' => $row['description'],
                'category_id' => $category->id,
                'price' => $row['price']
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
