<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogueProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\CakeProjectResource;
use App\Http\Resources\ProductResource;
use App\Import\ProductImport;
use App\Models\CakeProject;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class ProductController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $productQuery = Product::whereNotNull('category_id')
            ->with('category');

        if (!empty($search)) {
            $productQuery->where('id', $search);
            $productQuery->orWhere('name', 'like', "%$search%");
            $productQuery->orWhereHas('category', function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            });
            $productQuery->whereNull('deleted_at');
        }

        return ProductResource::collection(
            $productQuery->orderBy('id', 'DESC')
                ->paginate(10)
        );
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreProductRequest $request)
    {
        if ($request->hasFile('image')) {
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
                'image' => $imageName,
                'file_extension' => $extension
            ];
            $product = Product::create($newRequest);
            if ($product) {
                return response("Successfully created.", 201);
            }
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
            )
            ->where('b.id', $id)
            ->whereNotNull('b.category_id')
            ->whereNull('b.deleted_at')
            ->first();
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProductRequest $request, $id)
    {
        $product = Product::find($id);

        $product->category_id = $request->category_id;
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $extension = $image->extension();
            $imageName = pathinfo($image->hashName(), PATHINFO_FILENAME) . '.' . $extension;
            if ($extension === "bin") {
                Storage::disk('public')->putFileAs('glb_file', $image, $imageName);
            } else {
                Storage::disk('public')->putFileAs('product_img', $image, $imageName);
            }

            $product->image = $imageName;
            $product->file_extension = $extension;
        }

        return $product->save() ? response('Successfully updated.')
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
        Product::find($id)->delete();
        return response('Successfully deleted.');
    }


    public function importCSV(Request $request)
    {
        (new ProductImport)->queue($request->file('csv'), null, \Maatwebsite\Excel\Excel::CSV);
        return response('Successfully imported.', 201);
    }

    public function exportCSV()
    {
        $filename = 'products.csv';
        $products = Product::with('category')
            ->whereNull('deleted_at')
            ->whereNotNull('category_id')
            ->get();

        $columns = [
            'name',
            'description',
            'category',
            'price',
        ];

        $file = fopen($filename, 'w');
        fputcsv($file, $columns);

        foreach ($products as $product) {
            $row = [];

            foreach ($columns as $column) {
                if ($column === "category") {
                    $row[$column] = $product[$column]->name;
                } else {
                    $row[$column] = $product[$column];
                }
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

    public function dropdown()
    {
        return ProductResource::collection(
            Product::all()
        );
    }


    public function catalogue(CatalogueProductRequest $request)
    {
        return ProductResource::collection(
            Product::with('category')
                ->whereHas('category', function ($query) use ($request) {
                    $query->where('name', $request->input('category'));
                })
                ->where('name', 'like', '%' . $request->input('search') . '%')
                ->whereNull('deleted_at')
                ->whereNull('cake_project_id')
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


    public function search($name)
    {
        return Product::where('name', 'like', '%' . $name . '%')
            ->whereNull('deleted_at')
            ->paginate(10);
    }

    public function storeCustom(Request $request)
    {
        $request->validate([
            'project_name' => 'required|string|exists:cake_projects,name',
        ]);

        $price = 0;

        $cakeProject = CakeProjectResource::make(
            CakeProject::where('name', $request->project_name)
                ->first()
        );

        $product = Product::where('cake_project_id', $cakeProject->id)
            ->whereNull('deleted_at')
            ->first();

        if ($product) {
            return response($product, 201);
        }

        foreach ($cakeProject->cake_project_components as $key => $value) {
            $price += (float) $value->cake_component->cost;
        }

        $product = Product::create([
            'cake_project_id' => $cakeProject->id,
            'name' => $cakeProject->name,
            'description' => $cakeProject->description,
            'price' => $price,
            'image' => null,
            'file_extension' => 'glb'
        ]);
        return response($product, 201);
    }
}
