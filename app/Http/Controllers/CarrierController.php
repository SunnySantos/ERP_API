<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCarrierRequest;
use App\Http\Requests\UpdateCarrierRequest;
use App\Http\Resources\CarrierResource;
use App\Import\CarrierImport;
use App\Models\Carrier;
use Illuminate\Http\Request;

class CarrierController extends Controller
{
    public function importCSV(Request $request)
    {
        (new CarrierImport)->queue($request->file('csv'), null, \Maatwebsite\Excel\Excel::CSV);
        return response('Successfully imported.', 201);
    }

    public function exportCSV()
    {
        $filename = 'carriers.csv';
        $carriers = CarrierResource::collection(Carrier::all());

        $columns = [
            'id',
            'firstname',
            'middlename',
            'lastname',
            'address',
            'phone_number'
        ];


        $file = fopen($filename, 'w');
        fputcsv($file, $columns);

        foreach ($carriers as $carrier) {
            $row = [];

            foreach ($columns as $column) {
                $row[$column] = $carrier[$column];
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

        $carriers = Carrier::whereNull('deleted_at');

        if ($search !== "null") $carriers->where('id', $search);

        return CarrierResource::collection(
            $carriers->orderBy('id', 'DESC')
                ->paginate(10)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCarrierRequest $request)
    {
        Carrier::create($request->only([
            'firstname', 'middlename', 'lastname',  'address', 'phone_number'
        ]));
        
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
        return CarrierResource::make(
            Carrier::where('id', $id)
                ->whereNull('deleted_at')
                ->first()
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCarrierRequest $request, $id)
    {
        $carrier = Carrier::where('id', $id)
            ->update($request->only([
                'firstname', 'middlename', 'lastname',  'address', 'phone_number'
            ]));

        if ($carrier) return response('Successfully updated.');

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
        Carrier::find($id)->delete();

        return response('Successfully deleted.');
    }
}
