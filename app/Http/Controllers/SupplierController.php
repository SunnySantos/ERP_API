<?php

namespace App\Http\Controllers;

use App\Http\Resources\SupplierResource;
use App\Import\SupplierImport;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function importCSV(Request $request)
    {
        (new SupplierImport)->queue($request->file('csv'), null, \Maatwebsite\Excel\Excel::CSV);
        return response('Successfully imported.', 201);
    }

    public function exportCSV()
    {
        $filename = 'suppliers.csv';
        $suppliers = Supplier::all();

        $columns = [
            'id',
            'name',
            'address',
            'phone',
            'email',
            'person'
        ];

        $file = fopen($filename, 'w');
        fputcsv($file, $columns);

        foreach ($suppliers as $supplier) {
            $row = [];

            foreach ($columns as $column) {
                $row[$column] = $supplier[$column];
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
        if ($request->search !== "null") {
            return SupplierResource::collection(
                Supplier::where('id', 'like', '%' . $request->search . '%')
                    ->orWhere('person', 'like', '%' . $request->search . '%')
                    ->orWhere('name', 'like', '%' . $request->search . '%')
                    ->whereNull('deleted_at')
                    ->orderBy('id', 'DESC')
                    ->paginate(10)
            );
        }

        return SupplierResource::collection(
            Supplier::whereNull('deleted_at')
                ->orderBy('id', 'DESC')
                ->paginate(10)
        );
    }

    public function count()
    {
        return Supplier::select('id')
            ->whereNull('deleted_at')
            ->count();
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
            'name' => 'required|string',
            'phone' => 'required|string|regex:/^9\d{9}$/',
            'address' => 'required|string',
            'email' => 'required|string',
            'person' => 'required|string',
        ]);

        $supplier = Supplier::create($request->all());
        if ($supplier) {
            return response("Successfully created!", 201);
        }

        return response("Failed!", 400);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string|regex:/^9\d{9}$/',
            'address' => 'required|string',
            'email' => 'required|string',
            'person' => 'required|string'
        ]);

        $supplier = Supplier::where('id', $id)
            ->update($request->all());

        if ($supplier) {
            return response('Successfully updated.', 200);
        }
        return response('Failed.', 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Supplier::find($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $supplier = Supplier::find($id);
        if ($supplier) {
            $supplier->delete();
            return response("Successfully deleted!", 200);
        }
        return response("Failed.", 400);
    }
}
