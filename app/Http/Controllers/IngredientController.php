<?php

namespace App\Http\Controllers;

use App\Http\Resources\IngredientResource;
use App\Models\Employee;
use App\Models\Ingredients;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IngredientController extends Controller
{

    public function count(Request $request)
    {
        $branch_id = auth()->user()->employee->branch_id;

        return Ingredients::where('branch_id', $branch_id)
            ->whereNull('deleted_at')
            ->count();
    }

    public function search($name)
    {
        return IngredientResource::collection(
            Ingredients::where('name', 'like', "%$name%")
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
        $search = $request->input('search');

        $branch_id = auth()->user()->employee->branch_id;

        $ingredients = Ingredients::where('branch_id', $branch_id)
            ->whereNull('deleted_at');

        if ($search !== "null") {
            $ingredients->where('name', 'like', '%' . $search . '%')
                ->orWhere('id', 'like', '%' . $search . '%');
        }

        return IngredientResource::collection(
            $ingredients->orderBy('id', 'DESC')
                ->paginate(10)
        );
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
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'name' => 'required|string',
            'unit' => 'required|string',
            'stock' => 'required|numeric|min:0',
            'low_level' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'category' => 'required|string'
        ]);

        $branch_id = auth()->user()->employee->branch_id;

        $image = $request->file('image');
        $extension = $image->extension();
        $name = pathinfo($image->hashName(), PATHINFO_FILENAME);
        $imageName = time() . '_' . $name . '.' . $extension;
        Storage::disk('public')->putFileAs('ingredient_img', $image, $imageName);


        $data = [
            'branch_id' => $branch_id,
            'image' => $imageName,
            'name' => $request->name,
            'unit' => $request->unit,
            'stock' => $request->stock,
            'low_level' => $request->low_level,
            'cost' => $request->cost,
            'category' => $request->category
        ];

        $ingredient = Ingredients::create($data);
        if ($ingredient) {
            return response("Successfully added.", 201);
        }
        return response("Failed.", 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Ingredients::where('id', $id)
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
            'branch_id' => 'required|numeric|exists:branches,id',
            'name' => 'required|string',
            'unit' => 'required|string',
            'stock' => 'required|numeric|min:0',
            'low_level' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'category' => 'required|string'
        ]);

        $ingredient = Ingredients::where('id', $id)
            ->whereNull('deleted_at')
            ->get()
            ->first();
        if ($ingredient) {
            if ($ingredient->update($request->all())) {
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
        $ingredient = Ingredients::find($id);
        if ($ingredient) {
            $ingredient->delete();
            return response('Successfully deleted.', 200);
        }
        return response('Failed.', 400);
    }
}
