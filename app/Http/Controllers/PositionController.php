<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePositionRequest;
use App\Http\Requests\UpdatePositionRequest;
use App\Http\Resources\PositionResource;
use App\Import\PositionImport;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function dropdown()
    {
        return PositionResource::collection(
            Position::whereNull('deleted_at')
                ->get()
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $positions = Position::whereNull('deleted_at');

        if ($request->search !== "null") {
            $positions->where('id', 'like', '%' . $request->search . '%')
                ->orWhere('title', 'like', '%' . $request->search . '%');
        }
        return PositionResource::collection(
            $positions->orderBy('id', 'DESC')
                ->paginate(10)
        );
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
    public function store(StorePositionRequest $request)
    {
        Position::create($request->only(['title', 'rate']));
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
        return PositionResource::make(
            Position::where('id', $id)
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
    public function update(UpdatePositionRequest $request, $id)
    {
        $position = Position::where('id', $id)
            ->whereNull('deleted_at')
            ->update($request->only(['title', 'rate']));

        return $position ? response('Successfully updated.')
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
        Position::find($id)->delete();

        return response('Successfully deleted.');
    }
}
