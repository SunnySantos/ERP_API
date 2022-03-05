<?php

namespace App\Http\Controllers;

use App\Http\Resources\CakeModelResource;
use App\Models\CakeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class CakeModelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $models = CakeModel::whereNull('deleted_at');

        if ($search !== "null") {
            $models->where('id', $search)
                ->orWhere('name', 'like', "%$search%");
        }

        return CakeModelResource::collection($models->paginate(10));
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
            'zip_file' => 'required|file|mimes:zip'
        ]);

        $file = $request->file('zip_file');
        $zip = new ZipArchive();
        $path = $file->path();
        $status = $zip->open($path);

        if ($status !== true) {
            return response("Could not open", 400);
        }

        for ($i = 0; $i < $zip->count(); $i++) {
            $name = $zip->getNameIndex($i);
            $exist = Storage::disk('public')->exists('glb_file/' . $name);
            if ($exist) {
                $zip->deleteName($name);
            } else {
                CakeModel::create([
                    'name' => str_replace('.glb', '', $name),
                    'path' => $name
                ]);
            }
        }

        $zip->extractTo(Storage::path('public/glb_file'));
        $zip->close();

        return response("Successfully imported.", 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response(CakeModelResource::make(CakeModel::find($id)), 200);
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
            'name' => 'required|string'
        ]);

        $file = $request->file('file');

        if (is_null($file)) {
            $model = CakeModel::where('id', $id)
                ->update([
                    'name' => $request->input('name')
                ]);

            if ($model) {
                return response("Successfully updated.", 200);
            }
        }
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);


        $exist = Storage::disk('public')->exists('glb_file/' . $name);
        if (!$exist) {
            Storage::disk('public')->putFileAs('glb_file', $file, $name . ".glb");
            $model = CakeModel::where('id', $id)
                ->update([
                    'name' => $request->input('name'),
                    'path' => $name . ".glb"
                ]);

            if ($model) {
                return response("Successfully updated.", 200);
            }
        }

        return response("Failed.", 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $model = CakeModel::find($id);

        if ($model) {
            $model->delete();
            return response('Successfully deleted.', 200);
        }
        return response('Failed.', 400);
    }
}
