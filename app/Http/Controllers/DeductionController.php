<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeductionRequest;
use App\Http\Requests\UpdateDeductionRequest;
use App\Http\Resources\DeductionResource;
use App\Import\DeductionImport;
use App\Models\Deduction;
use Illuminate\Http\Request;

class DeductionController extends Controller
{
    public function importCSV(Request $request)
    {
        (new DeductionImport)->queue($request->file('csv'), null, \Maatwebsite\Excel\Excel::CSV);
        return response('Successfully imported.', 201);
    }

    public function exportCSV()
    {
        $filename = 'deductions.csv';
        $deductions = Deduction::all();

        $columns = [
            'id',
            'description',
            'amount'
        ];

        $file = fopen($filename, 'w');
        fputcsv($file, $columns);

        foreach ($deductions as $deduction) {
            $row = [];

            foreach ($columns as $column) {
                $row[$column] = $deduction[$column];
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
    public function index()
    {
        return DeductionResource::collection(
            Deduction::whereNull('deleted_at')
                ->orderBy('id', 'DESC')
                ->paginate(10)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDeductionRequest $request)
    {
        Deduction::create($request->only([
            'description', 'amount'
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
        return DeductionResource::make(
            Deduction::where('id', $id)
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
    public function update(UpdateDeductionRequest $request, $id)
    {
        $deduction = Deduction::where('id', $id)
            ->whereNull('deleted_at')
            ->update($request->only(['description', 'amount']));

        return $deduction ? response('Successfully updated.')
            : response('Failed.', 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Deduction::find($id)->delete();

        return response('Successfully deleted.');
    }

    public function deleteChecked($ids)
    {
        Deduction::whereIn('id', explode(',', $ids))->delete();

        return response('Successfully deleted.');
    }
}
