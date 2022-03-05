<?php

namespace App\Import;

use App\Models\Product;
use App\Models\Stock;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class StockImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, ShouldQueue
{

    use Importable;

    public function model(array $row)
    {
        $product = Product::find($row['product_id']);
        $branch_id = auth()->user()->employee->branch_id;

        if ($product && $branch_id) {

            $stock = Stock::where('product_id', $product->id)
                ->where('branch_id', $branch_id)
                ->whereNull('deleted_at')
                ->first();

            if ($stock) {
                $stock->quantity += $row['quantity'];
                $stock->save();
            } else {
                Stock::create([
                    'product_id' => $row['product_id'],
                    'quantity' => $row['quantity'],
                    'branch_id' => $branch_id,
                    'minimum' => $row['minimum'],
                ]);
            }
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
