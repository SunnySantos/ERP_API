<?php

namespace App\Http\Controllers;

use App\Http\Resources\CakeProjectResource;
use App\Models\CakeProject;
use App\Models\CakeProjectComponent;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CakeProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();

        if ($user) {
            return CakeProjectResource::collection(
                CakeProject::where('user_id', $user->id)
                    ->whereNull('deleted_at')
                    ->get()
            );
        }
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
            'name' => 'required|string|unique:cake_projects',
        ]);

        $name = $request->input('name');
        $user = auth()->user();

        if ($user) {
            $project = CakeProject::create([
                'user_id' => $user->id,
                'name' => $name
            ]);

            if ($project) {
                return response('Successfully created.', 201);
            }
        }

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
        $project = CustomizeCakeProject::where('id', $id)
            ->select('name', 'id as project_id', 'description')
            ->whereNull('deleted_at')
            ->get()
            ->first();

        if ($project) {
            $project_assets = DB::table('project_assets as b')
                ->join('assets as c', 'c.id', '=', 'b.asset_id')
                ->select(
                    'b.id',
                    'b.project_id',
                    'b.asset_id',
                    'b.uuid',
                    'b.posX',
                    'b.posY',
                    'b.posZ',
                    'c.glb',
                    'c.name',
                    'c.size',
                    'c.category',
                    'c.shape',
                    'c.cost'
                )
                ->where('b.project_id', $project->project_id)
                ->whereNull('b.deleted_at')
                ->get();

            foreach ($project_assets as $key => $value) {
                $posX = (float) $value->posX;
                $posY = (float) $value->posY;
                $posZ = (float) $value->posZ;
                $file = $value->glb;
                $content = Storage::get('public/glb_file/' . $file);
                $exist = Storage::disk('public')->exists('glb_file/' . $file);
                if ($exist) {
                    $value->base64 = "data:application/glb;base64," . base64_encode($content);
                }
                $value->position = [$posX, $posY, $posZ];

                $value->ingredients = DB::table('asset_ingredients as a')
                    ->join('ingredients as b', 'a.ingredient_id', '=', 'b.id')
                    ->where('a.asset_id', $value->asset_id)
                    ->whereNull('a.deleted_at')
                    ->get();
            }
            $project->objects = $project_assets;
            return response($project, 200);
        }
        return response('Project not found.', 400);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $name)
    {
        $request->validate([
            'name' => ['required', 'string', Rule::unique("cake_projects", "name")->ignore($name, 'name')],
            'description' => 'nullable|string',
            'components' => 'nullable|array'
        ]);

        $name = $request->input('name');
        $description = $request->input('description');
        $components = $request->input('components');

        $project = CakeProject::where('name', $name)
            ->whereNull('deleted_at')
            ->first();

        if ($project) {

            $project->update([
                'name' => $name,
                'description' => $description
            ]);

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
