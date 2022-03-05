<?php

namespace App\Http\Controllers;

use App\Import\DeductionImport;
use App\Models\Deduction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        return Deduction::whereNull('deleted_at')
            ->paginate(10);
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
            'description' => 'required|string',
            'amount' => 'required|numeric'
        ]);

        Deduction::create($request->all());
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
        return Deduction::where('id', $id)
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
            'description' => 'required|string',
            'amount' => 'required|numeric'
        ]);

        $deduction = Deduction::where('id', $id)
            ->whereNull('deleted_at')
            ->update([
                'description' => $request->description,
                'amount' => $request->amount
            ]);

        if ($deduction) {
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
        $deduction = Deduction::find($id);

        if ($deduction) {
            $deduction->delete();
            return response('Successfully deleted.', 200);
        }
        return response('Failed.', 400);
    }
}
