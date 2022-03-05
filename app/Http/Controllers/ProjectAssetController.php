<?php

namespace App\Http\Controllers;

use App\Models\ProjectAssets;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isNull;

class ProjectAssetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
            'project_id' => 'required|numeric|exists:customize_cake_projects,id',
            'asset_id' => 'required|numeric|exists:assets,id',
            'uuid' => 'required|string',
            'posX' => 'required|numeric',
            'posY' => 'required|numeric',
            'posZ' => 'required|numeric'
        ]);

        $project_asset = ProjectAssets::create($request->all());
        if ($project_asset) {
            return response('Successfully created.', 200);
        }
        return response('Failed', 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
            'project_id' => 'required|numeric|exists:customize_cake_projects,id',
            'asset_id' => 'required|numeric|exists:assets,id',
            'uuid' => 'required|string',
            'posX' => 'required|numeric',
            'posY' => 'required|numeric',
            'posZ' => 'required|numeric'
        ]);

        if ($id < 0) {
            $project_asset1 = ProjectAssets::create($request->all());
            if ($project_asset1) {
                return response('Successfully created.', 201);
            }
        } else {
            $project_asset = ProjectAssets::where('id', $id)
                ->where('uuid', $request->uuid)
                ->whereNull('deleted_at')
                ->get()
                ->first();

            $project_asset->posX = $request->posX;
            $project_asset->posY = $request->posY;
            $project_asset->posZ = $request->posZ;
            $project_asset->save();
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
        $project_asset = ProjectAssets::find($id);

        if ($project_asset) {
            $project_asset->delete();
            return response('Successfully deleted.', 200);
        }
        return response('Failed.', 400);
    }
}
