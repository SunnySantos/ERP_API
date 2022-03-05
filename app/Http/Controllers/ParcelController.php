<?php

namespace App\Http\Controllers;

use App\Http\Resources\ParcelResource;
use App\Import\ParcelImport;
use App\Models\Carrier;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Parcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParcelController extends Controller
{
    public function importCSV(Request $request)
    {
        (new ParcelImport)->queue($request->file('csv'), null, \Maatwebsite\Excel\Excel::CSV);
        return response('Successfully imported.', 201);
    }

    public function exportCSV()
    {
        $filename = 'parcels.csv';
        $parcels = Parcel::all();

        $columns = [
            'tracking',
            'order_id',
            'carrier_id',
            'status'
        ];

        $file = fopen($filename, 'w');
        fputcsv($file, $columns);

        foreach ($parcels as $parcel) {
            $row = [];

            foreach ($columns as $column) {
                $row[$column] = $parcel[$column];
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
    public function index(Request $request)
    {
        $search = $request->input('search');

        if ($search !== "null") {
            return ParcelResource::collection(
                DB::table('parcels as a')
                    ->join('carriers as b', 'a.carrier_id', '=', 'b.id')
                    ->join('orders as c', 'a.order_id', '=', 'c.id')
                    ->join('customers as d', 'c.customer_id', '=', 'd.id')
                    ->select(
                        'a.id',
                        'a.tracking',
                        DB::raw("CONCAT(b.lastname, ', ', b.firstname) as sender"),
                        DB::raw("CONCAT(d.lastname,', ', d.firstname) as recipient"),
                        'c.status'
                    )
                    ->where('tracking', $search)
                    ->whereNull('a.deleted_at')
                    ->orderBy('a.id', 'DESC')
                    ->paginate(10)
            );
        }
        return ParcelResource::collection(
            DB::table('parcels as a')
                ->join('carriers as b', 'a.carrier_id', '=', 'b.id')
                ->join('orders as c', 'a.order_id', '=', 'c.id')
                ->join('customers as d', 'c.customer_id', '=', 'd.id')
                ->select(
                    'a.id',
                    'a.tracking',
                    DB::raw("CONCAT(b.lastname, ', ', b.firstname) as sender"),
                    DB::raw("CONCAT(d.lastname,', ', d.firstname) as recipient"),
                    'c.status'
                )
                ->whereNull('a.deleted_at')
                ->orderBy('a.id', 'DESC')
                ->paginate(10)
        );
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
            'order_id' => 'required|numeric|exists:orders,id',
            'carrier_id' => 'required|numeric|exists:carriers,id',
            'status' => 'required|string'
        ]);

        $branch_id = auth()->user()->employee->branch_id;

        $order = Order::where('id', $request->input('order_id'))
            ->where('branch_id', $branch_id)
            ->where('status', '!=', 'CANCELLED')
            ->whereNull('deleted_at')
            ->first();

        if (!$order) {
            return response([
                'errors' => ['order_id' => ['Order ID does not exist.']]
            ], 422);
        }

        $order = Order::where('id', $request->order_id)
            ->whereNull('deleted_at')
            ->first();

        if ($order) {
            $request['tracking'] = (int) (date('Ymd') . mt_rand(1, 100));
            Parcel::create($request->all());
            return response('Successfully created.', 201);
        }
        return response('Failed.', 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($tracking)
    {
        return Parcel::where('tracking', $tracking)
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
            'order_id' => 'required|numeric|exists:orders,id',
            'carrier_id' => 'required|numeric|exists:carriers,id'
        ]);

        $branch_id = auth()->user()->employee->branch_id;

        $order = Order::where('id', $request->input('order_id'))
            ->where('branch_id', $branch_id)
            ->where('status', '!=', 'CANCELLED')
            ->whereNull('deleted_at')
            ->first();

        if (!$order) {
            return response([
                'errors' => ['order_id' => ['Order ID does not exist.']]
            ], 422);
        }

        $carrier = Carrier::find($request->input('carrier_id'));
        if (!$carrier) {
            return response([
                'errors' => ['carrier_id' => ['Carrier ID does not exist.']]
            ], 422);
        }

        $parcel = Parcel::where('tracking', $id)
            ->whereNull('deleted_at')
            ->update($request->all());

        if ($parcel) {
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
        $parcel = Parcel::find($id);

        if ($parcel) {
            $parcel->delete();
            return response('Successfully deleted.', 200);
        }
        return response('Failed.', 400);
    }
}
