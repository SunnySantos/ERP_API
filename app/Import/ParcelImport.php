<?php

namespace App\Import;

use App\Models\Carrier;
use App\Models\Order;
use App\Models\Parcel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ParcelImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, ShouldQueue
{

    use Importable;

    public function model(array $row)
    {

        $order = Order::find($row['order_id']);
        $carrier = Carrier::find($row['carrier_id']);
        if ($order && $carrier) {
            Parcel::create([
                'tracking' => (int) (date('Ymd') . mt_rand(1, 100)),
                'order_id' => $row['order_id'],
                'carrier_id' => $row['carrier_id'],
                'status' => $order->status,
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
