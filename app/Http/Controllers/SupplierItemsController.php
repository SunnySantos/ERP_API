<?php

namespace App\Http\Controllers;

use App\Models\SupplierItems;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupplierItemsController extends Controller
{
    // /**
    //  * Display a listing of the resource.
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public function index()
    // {
    //     return SupplierItems::whereNull('deleted_at')->paginate(10);
    // }

    public function index(Request $request)
    {
        return SupplierItems::whereNull('deleted_at')
            ->where('category', $request->category)
            ->where('supplier_id', $request->id)
            ->paginate(10);
    }


    public function showByDryCategory($id)
    {
        return SupplierItems::whereNull('deleted_at')
            ->where('category', 'dry')
            ->where('supplier_id', $id)
            ->paginate(10);
    }

    public function showByWetCategory($id)
    {
        return SupplierItems::whereNull('deleted_at')
            ->where('category', 'solid')
            ->where('supplier_id', $id)
            ->paginate(10);
    }

    public function showByOtherCategory($id)
    {
        return SupplierItems::whereNull('deleted_at')
            ->where('category', 'liquid')
            ->where('supplier_id', $id)
            ->paginate(10);
    }

    // public function count()
    // {
    //     $count = SupplierItems::select('id')
    //         ->whereNull('deleted_at')
    //         ->count();
    //     return $count;
    // }


    // public function countBySupplierAccountId($id)
    // {

    //     return $count;
    // }

    public function countItemByCategoryAndSupplierId($id)
    {

        $dryCount = SupplierItems::select('id')
            ->where('supplier_id', $id)
            ->where('category', 'dry')
            ->whereNull('deleted_at')
            ->count();

        $wetCount = SupplierItems::select('id')
            ->where('supplier_id', $id)
            ->where('category', 'wet')
            ->whereNull('deleted_at')
            ->count();

        $otherCount = SupplierItems::select('id')
            ->where('supplier_id', $id)
            ->where('category', 'other')
            ->whereNull('deleted_at')
            ->count();

        return response([
            "dry" => $dryCount,
            "wet" => $wetCount,
            "other" => $otherCount
        ], 200);
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
            'supplier_id' => 'required|numeric|exists:suppliers,id',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'name' => 'required|unique:supplier_items|max:80|string|regex:/^[a-zA-Z\s]*$/',
            'description' => 'required|string|max:240',
            'category' => 'required|string|in:dry,solid,liquid',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|numeric|min:0',
            'unit' => 'required|string'
        ]);

        $image = $request->file('image');
        $extension = $image->extension();
        $name = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        $imageName = time() . '_' . $name . '.' . $extension;
        Storage::disk('public')->putFileAs('item_img', $image, $imageName);
        $newRequest = [
            'supplier_id' => $request->supplier_id,
            'image' => $imageName,
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'price' => $request->price,
            'stock' => $request->stock,
            'unit' => $request->unit
        ];
        $product = SupplierItems::create($newRequest);

        if ($product !== null) {
            return response('Successfully created!', 201);
        }
        return response('Failed!', 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return SupplierItems::whereNull('deleted_at')
            ->where('id', $id)
            ->get()
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
            'supplier_id' => 'required|numeric|exists:suppliers,id',
            'name' => ['required', 'max:80', 'string', 'regex:/^[A-Za-z\s]+$/', Rule::unique('supplier_items', 'name')->ignore($id)],
            'description' => 'required|string|max:240|regex:/^[A-Za-z\s]+$/',
            'category' => 'required|string|in:dry,wet,other',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|numeric|min:0',
            'unit' => 'required|string'
        ]);

        $item = SupplierItems::find($id);

        if ($item !== null) {
            if ($item->update($request->all())) {
                return response("Successfully updated!", 200);
            }
        }
        return response("Item does not exists!", 200);


        // $image = $request->file('image');
        // $extension = $image->extension();
        // $name = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
        // $imageName = time() . '_' . $name . '.' . $extension;
        // Storage::disk('public')->putFileAs('item_img', $image, $imageName);
        // $newRequest = [
        //     'supplier_id' => $request->supplier_id,
        //     'image' => $imageName,
        //     'name' => $request->name,
        //     'description' => $request->description,
        //     'category' => $request->category,
        //     'price' => $request->price,
        //     'stock' => $request->stock,
        //     'unit' => $request->unit
        // ];
        // $product = SupplierItems::create($newRequest);

        // if ($product !== null) {
        //     return response('Successfully created!', 201);
        // }
        // return response('Failed!', 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
