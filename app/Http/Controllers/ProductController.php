<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Import\ProductImport;
use App\Models\CustomizeCakeProject;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;


class ProductController extends Controller
{

    public function importCSV(Request $request)
    {
        (new ProductImport)->queue($request->file('csv'), null, \Maatwebsite\Excel\Excel::CSV);
        return response('Successfully imported.', 201);
    }

    public function exportCSV()
    {
        $filename = 'products.csv';
        $products = Product::all();

        $columns = [
            'name',
            'description',
            'category_id',
            'price',
            'cost'
        ];

        $file = fopen($filename, 'w');
        fputcsv($file, $columns);

        foreach ($products as $product) {
            $row = [];

            foreach ($columns as $column) {
                $row[$column] = $product[$column];
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
        $search = $request->input('search');

        $productQuery = Product::orderBy('id', 'DESC')
            ->with('category');

        if ($search !== "null") {
            $productQuery->where('id', $search);
            $productQuery->orWhere('name', 'like', "%$search%");
            $productQuery->orWhereHas('category', function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            });
            $productQuery->whereNull('deleted_at');
        }

        return ProductResource::collection($productQuery->paginate(10));
    }

    public function catalogue(Request $request)
    {
        $request->validate([
            'category' => 'required|string'
        ]);

        $category = $request->input('category');

        return ProductResource::collection(
            Product::with('category')
                ->whereHas('category', function ($query) use ($category) {
                    $query->where('name', $category);
                })
                ->get()
        );
    }

    public function count()
    {
        $count = Product::select('id')
            ->whereNull('deleted_at')
            ->count();
        return $count;
    }

    public function bestSeller()
    {
        $products = DB::table('orders as o')
            ->join('carts as c', 'o.id', '=', 'c.order_id')
            ->join('branch_products as bp', 'c.branch_product_id', '=', 'bp.id')
            ->join('products as p', 'bp.product_id', '=', 'p.id')
            ->select(
                'p.name',
                'p.description',
                'p.price',
                'p.image',
                'c.quantity'
            )
            ->where('o.delivered_at', '!=', null)
            ->where('amount_tendered', '!=', 0)
            ->where('is_paid', '=', 1)
            ->where('cancel', '=', 0)
            ->whereNull('o.deleted_at')
            ->whereNull('c.deleted_at')
            ->whereNull('p.deleted_at')
            ->get();

        $productList = [];

        for ($i = 0; $i < sizeof($products); $i++) {
            $product = $products[$i];
            $name = $product->name;
            $description = $product->description;
            $quantity = 0;
            $image = $product->image;
            $price = $product->price;


            for ($j = 0; $j < sizeof($products); $j++) {
                $_product = $products[$j];
                $_name = $_product->name;
                $_quantity = $_product->quantity;
                if ($name === $_name) {
                    $quantity += $_quantity;
                }
            }


            $productList[$name] = [
                'name' => $name,
                'description' => $description,
                'quantity' => $quantity,
                'price' => $price,
                'image' => $image
            ];
        }

        return response($productList, 200);
    }

    public function bestSellerByCategory($category)
    {
        $products = DB::table('orders as o')
            ->join('carts as c', 'o.id', '=', 'c.order_id')
            ->join('branch_products as bp', 'c.branch_product_id', '=', 'bp.id')
            ->join('products as p', 'bp.product_id', '=', 'p.id')
            ->select(
                'p.name',
                'p.description',
                'p.price',
                'p.image',
                'c.quantity'
            )
            ->where('p.category', '=', $category)
            ->where('o.delivered_at', '!=', null)
            ->where('amount_tendered', '!=', 0)
            ->where('is_paid', '=', 1)
            ->where('cancel', '=', 0)
            ->whereNull('o.deleted_at')
            ->whereNull('c.deleted_at')
            ->whereNull('p.deleted_at')
            ->get();

        $productList = [];

        for ($i = 0; $i < sizeof($products); $i++) {
            $product = $products[$i];
            $name = $product->name;
            $description = $product->description;
            $quantity = 0;
            $image = $product->image;
            $price = $product->price;


            for ($j = 0; $j < sizeof($products); $j++) {
                $_product = $products[$j];
                $_name = $_product->name;
                $_quantity = $_product->quantity;
                if ($name === $_name) {
                    $quantity += $_quantity;
                }
            }


            $productList[$name] = [
                'name' => $name,
                'description' => $description,
                'quantity' => $quantity,
                'price' => $price,
                'image' => $image
            ];
        }

        return response($productList, 200);
    }

    public function showByTypeCategory($category)
    {
        return Product::select(
            'name',
            'description',
            'price',
            'cost',
            'image',
        )
            ->where('category', $category)
            ->where('deleted_at', null)
            ->get();
    }

    public function mostPurchased()
    {
        $products = DB::table('orders as o')
            ->join('carts as c', 'o.id', '=', 'c.order_id')
            ->join('branch_products as bp', 'c.branch_product_id', '=', 'bp.id')
            ->join('products as p', 'bp.product_id', '=', 'p.id')
            ->select(
                'p.name',
                'p.description',
                'p.image',
                'c.quantity'
            )
            ->where('o.delivered_at', '!=', null)
            ->where('amount_tendered', '!=', 0)
            ->where('is_paid', '=', 1)
            ->where('cancel', '=', 0)
            ->whereNull('o.deleted_at')
            ->whereNull('c.deleted_at')
            ->whereNull('p.deleted_at')
            ->get();

        $currentName = null;
        $currentQuantity = 0;
        $currentDescription = null;
        $currentImage = null;

        for ($i = 0; $i < sizeof($products); $i++) {
            $product = $products[$i];
            $name = $product->name;
            $description = $product->description;
            $quantity = 0;
            $image = $product->image;


            for ($j = 0; $j < sizeof($products); $j++) {
                $_product = $products[$j];
                $_name = $_product->name;
                $_quantity = $_product->quantity;
                if ($name === $_name) {
                    $quantity += $_quantity;
                }
            }

            if ($currentName === null && $currentQuantity === 0) {
                $currentName = $name;
                $currentDescription = $description;
                $currentQuantity = $quantity;
                $currentImage = $image;
            } else if ($currentQuantity < $quantity) {
                $currentName = $name;
                $currentDescription = $description;
                $currentQuantity = $quantity;
                $currentImage = $image;
            }
        }

        $best = [
            'name' => $currentName,
            'description' => $currentDescription,
            'quantity' => $currentQuantity,
            'image' => $currentImage
        ];

        return response($best, 200);
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
            'image' => 'required',
            'name' => 'required|string|unique:products|max:80|regex:/^[a-zA-Z\s]*$/',
            'description' => 'required|string|max:240',
            'category_id' => 'required|numeric|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0'
        ]);

        $image = $request->file('image');
        $extension = $image->extension();
        $name = pathinfo($image->hashName(), PATHINFO_FILENAME);
        $imageName = time() . '_' . $name . '.' . $extension;
        if ($extension === "bin") {
            Storage::disk('public')->putFileAs('glb_file', $image, $imageName);
        } else {
            Storage::disk('public')->putFileAs('product_img', $image, $imageName);
        }
        $newRequest = [
            'category_id' => $request['category_id'],
            'name' => $request['name'],
            'description' => $request['description'],
            'price' => $request['price'],
            'cost' => $request['cost'],
            'image' => $imageName,
            'file_extension' => $extension
        ];
        $product = Product::create($newRequest);
        if ($product !== null) {
            return response("Successfully created.", 201);
        }

        return response("Failed.", 400);
    }

    public function storeCustom(Request $request)
    {
        $request->validate([
            'project_name' => 'required|string|exists:customize_cake_projects,name',
            'name' => 'required|string|unique:products|max:80',
            'description' => 'nullable|string|max:240',
            'category' => 'required|string',
            'price' => 'required|numeric|min:0'
        ]);

        $cost = 0;

        $projectId = CustomizeCakeProject::where('name', $request->project_name)
            ->first('id');

        $project =  DB::table('customize_cake_projects as a')
            ->join('project_assets as b', 'a.id', '=', 'b.project_id')
            ->join('assets as c', 'b.asset_id', '=', 'c.id')
            ->select(
                DB::raw('cost-(cost*0.2) as cost')
            )
            ->where('a.name', $request->project_name)
            ->get();

        foreach ($project as $key => $value) {
            $cost += $value->cost;
        }

        $request['project_id'] = $projectId->id;
        $request['cost'] = $cost;
        $request['category_id'] = 1;
        $request['description'] = '';

        $product = Product::create($request->all());
        return response($product, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return DB::table('products as b')
            ->join('categories as a', 'a.id', '=', 'b.category_id')
            ->select(
                'b.id',
                'b.image',
                'b.name',
                'a.id as category_id',
                'a.name as category',
                'b.description',
                'b.price',
                'b.cost'
            )
            ->where('b.id', $id)
            ->whereNull('b.deleted_at')
            ->first();
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function search($name)
    {
        return Product::where('name', 'like', '%' . $name . '%')
            ->whereNull('deleted_at')
            ->paginate(10);
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
            'name' => ['required', 'string', 'max:89', Rule::unique("products", "name")->ignore($id)],
            'description' => 'required|string|max:240',
            'category_id' => 'required|numeric|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0'
        ]);

        $product = Product::find($id);

        $product->category_id = $request->category_id;
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->cost = $request->cost;

        $image = $request->file('image');
        if ($image) {
            $extension = $image->extension();
            $name = pathinfo($image->hashName(), PATHINFO_FILENAME);
            $imageName = time() . '_' . $name . '.' . $extension;
            if ($extension === "bin") {
                Storage::disk('public')->putFileAs('glb_file', $image, $imageName);
            } else {
                Storage::disk('public')->putFileAs('product_img', $image, $imageName);
            }

            $product->image = $imageName;
            $product->file_extension = $extension;
        }

        if ($product->save()) {
            return response('Successfully updated.', 200);
        }
        return response('Failed.', 400);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateImage(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $product = Product::find($id);

        if ($product !== null) {
            $image = $request->file('image');
            $extension = $image->extension();
            $name = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $imageName = time() . '_' . $name . '.' . $extension;
            Storage::disk('public')->putFileAs('product_img', $image, $imageName);
            $product->image = $imageName;
            $product->save();
            return Product::all();
        }

        return response('No changes', 404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::where('id', $id)
            ->whereNull('deleted_at')
            ->get()
            ->first();
        if ($product !== null) {
            $product->delete();
            return response('Successfully deleted.', 200);
        }
        return response('Product does not exist.', 400);
    }
}
