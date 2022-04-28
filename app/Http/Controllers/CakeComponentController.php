<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetAssetByGroupRequest;
use App\Http\Requests\StoreCakeComponentRequest;
use App\Http\Requests\UpdateCakeComponentRequest;
use App\Http\Resources\AssetResource;
use App\Http\Resources\CakeComponentResource;
use App\Import\CakeComponentImport;
use App\Models\CakeComponent;
use App\Models\CakeIngredient;
use App\Models\CakeModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CakeComponentController extends Controller
{



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $cake_components = CakeComponent::join('cake_models', 'cake_components.cake_model_id', '=', 'cake_models.id')
            ->select(
                'cake_components.id',
                'cake_components.name',
                'cake_components.category',
                'cake_components.cost',
                'cake_models.path',
                'cake_components.shape',
                'cake_components.size'
            )
            ->orderBy('cake_components.id', 'DESC')
            ->whereNull('cake_components.deleted_at');

        if ($search !== "null") {
            $cake_components->where('cake_components.id', $request->search)
                ->orWhere('cake_components.name', 'like', '%' . $request->search . '%')
                ->orWhere('cake_components.size', 'like', '%' . $request->search . '%')
                ->orWhere('cake_components.category', 'like', '%' . $request->search . '%');
        }

        return AssetResource::collection($cake_components->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCakeComponentRequest $request)
    {
        $ingredients = json_decode($request->ingredients);

        $cake_component = CakeComponent::create($request->all());
        if ($cake_component) {
            foreach ($ingredients as $key => $value) {
                CakeIngredient::create([
                    'ingredient_id' => $value->id,
                    'cake_component_id' => $cake_component->id,
                    'amount' => $value->amount
                ]);
            }
            return response("Successfully added.", 201);
        }
        return response('Failed.', 400);
    }

    public function getGroups()
    {
        return CakeComponent::distinct()
            ->orderBy('shape', 'DESC')
            ->get(['category', 'shape']);
    }

    public function getAssetsByGroup(GetAssetByGroupRequest $request)
    {
        $category = $request->input('category');
        $shape = $request->input('shape');

        $cake_components = CakeComponent::where('category', $category)
            ->whereNull('deleted_at');


        if ($shape !== "null") {
            return CakeComponentResource::collection(
                $cake_components->where('shape', $shape)
                    ->whereHas('cake_model')
                    ->get()
            );
        }

        return CakeComponentResource::collection(
            $cake_components->get()
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return CakeComponent::with(['cake_ingredients' => function ($query) {
            $query->join('ingredients', 'cake_ingredients.ingredient_id', '=', 'ingredients.id');
            $query->select(
                'cake_ingredients.cake_component_id',
                'cake_ingredients.amount',
                'ingredients.id',
                'ingredients.name',
                'ingredients.unit'
            );
        }])
            ->where('id', $id)
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
    public function update(UpdateCakeComponentRequest $request, $id)
    {
        $ingredients = json_decode($request->ingredients);

        $model = CakeModel::where('id', $request->input('cake_model_id'))
            ->whereNull('deleted_at')
            ->first();

        if (!$model) {
            return response(["errors" => ["cake_model_id" => "Model ID does not exist."]], 422);
        }

        $cake_component = CakeComponent::where('id', $id)
            ->whereNull('deleted_at')
            ->update([
                'name' => $request->name,
                'category' => $request->category,
                'size' => $request->size,
                'shape' => $request->shape,
                'cost' => $request->cost
            ]);
        if ($cake_component) {
            $cakeIngredients = CakeIngredient::where('cake_component_id', $id)->get(['id']);
            CakeIngredient::destroy($cakeIngredients->toArray());
            foreach ($ingredients as $key => $value) {
                CakeIngredient::create([
                    'ingredient_id' => $value->id,
                    'cake_component_id' => $id,
                    'amount' => $value->amount
                ]);
            }
            return response("Successfully updated.", 200);
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
        CakeComponent::find($id)->delete();
        return response('Successfully deleted.');
    }


    public function importCSV(Request $request)
    {
        (new CakeComponentImport)->queue($request->file('csv'), null, \Maatwebsite\Excel\Excel::CSV);
        return response('Successfully imported.', 201);
    }


    public function exportCSV()
    {
        $filename = 'cake_components.csv';
        $cake_components = CakeComponent::all();

        $columns = [
            'id',
            'cake_model_id',
            'name',
            'size',
            'category',
            'shape',
            'cost'
        ];

        $file = fopen($filename, 'w');
        fputcsv($file, $columns);

        foreach ($cake_components as $cake_component) {
            $row = [];

            foreach ($columns as $column) {
                $row[$column] = $cake_component[$column];
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
}
