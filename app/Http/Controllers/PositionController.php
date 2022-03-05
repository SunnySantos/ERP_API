<?php

namespace App\Http\Controllers;

use App\Import\PositionImport;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PositionController extends Controller
{
    public function dropdown()
    {
        return Position::whereNull('deleted_at')
            ->get();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if ($request->search !== "null") {
            return Position::whereNull('deleted_at')
                ->where('id', 'like', '%' . $request->search . '%')
                ->orWhere('title', 'like', '%' . $request->search . '%')
                ->paginate(10);
        }
        return Position::whereNull('deleted_at')
            ->paginate(10);
    }

    public function importCSV(Request $request)
    {
        (new PositionImport)->queue($request->file('csv'), null, \Maatwebsite\Excel\Excel::CSV);
        return response('Successfully imported.', 201);
    }

    public function exportCSV()
    {
        $filename = 'positions.csv';
        $positions = Position::all();

        $columns = [
            'id',
            'title',
            'rate'
        ];

        $file = fopen($filename, 'w');
        fputcsv($file, $columns);

        foreach ($positions as $position) {
            $row = [];

            foreach ($columns as $column) {
                $row[$column] = $position[$column];
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'rate' => 'required|numeric|min:0'
        ]);

        Position::create($request->all());
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
        return Position::where('id', $id)
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
            'title' => 'required|string',
            'rate' => 'required|numeric|min:0'
        ]);

        $position = Position::where('id', $id)
            ->whereNull('deleted_at')
            ->update([
                'title' => $request->title,
                'rate' => $request->rate
            ]);

        if ($position) {
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
        $position = Position::find($id);

        if ($position) {
            $position->delete();
            return response('Successfully deleted.', 200);
        }
        return response('Failed.', 400);
    }
}
