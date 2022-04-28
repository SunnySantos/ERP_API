<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCakeProjectRequest;
use App\Http\Requests\UpdateCakeProjectRequest;
use App\Http\Resources\CakeProjectResource;
use App\Models\CakeProject;
use App\Models\CakeProjectComponent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CakeProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return CakeProjectResource::collection(
            CakeProject::where('user_id', auth()->id())
                ->whereNull('deleted_at')
                ->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCakeProjectRequest $request)
    {
        $name = $request->input('name');

        $project = CakeProject::create([
            'user_id' => auth()->id(),
            'name' => $name
        ]);

        if ($project) return response('Successfully created.', 201);

        return response('Failed.', 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($name)
    {
        return CakeProjectResource::make(
            CakeProject::where('name', $name)
                ->whereNull('deleted_at')
                ->first()
        );
    }

    public function preview($id)
    {
        return CakeProjectResource::make(
            CakeProject::where('id', $id)
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
    public function update(UpdateCakeProjectRequest $request, $name)
    {
        $components = $request->input('components');

        $project = CakeProject::where('name', $request->input('name'))
            ->whereNull('deleted_at')
            ->first();

        if ($project) {

            $project->update($request->only(['name', 'description']));

            for ($i = 0; $i < sizeof($components); $i++) {
                $component = $components[$i];
                $id = $component['id'];
                $component['cake_project_id'] = $project->id;

                if ($id < 0) {
                    CakeProjectComponent::create($component);
                } else {
                    CakeProjectComponent::where('id', $id)
                        ->where('uuid', $component['uuid'])
                        ->whereNull('deleted_at')
                        ->update([
                            'posX' => $component['posX'],
                            'posY' => $component['posY'],
                            'posZ' => $component['posZ']
                        ]);
                }
            }

            return response("Successfully updated.");
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
        CakeProject::find($id)->delete();
        return response('Successfully deleted.');
    }


    public function removeCakeProjectComponent($id)
    {
        CakeProjectComponent::find($id)->forceDelete();
        return response("Successfully deleted.");
    }
}
