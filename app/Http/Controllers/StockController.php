<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockRequest;
use App\Http\Requests\UpdateStockRequest;
use App\Http\Resources\StockResource;
use App\Import\StockImport;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $branch_id = auth()->user()->employee->branch_id;

        $stocks = Stock::with('product')
            ->where('branch_id', $branch_id)
            ->whereHas('product', function ($query) use ($search) {
                if ($search !== "null") {
                    $query->where('name', 'like', "%$search%");
                }
            })
            ->orWhere('id', $search);

        return StockResource::collection($stocks->paginate(10));
    }



    public function dropdown()
    {
        $branch_id = auth()->user()->employee->branch_id;
        return StockResource::collection(
            Stock::where('branch_id', $branch_id)
                ->whereNull('deleted_at')
                ->get()
        );
    }


    public function count()
    {
        $branch_id = auth()->user()->employee->branch_id;
        return Stock::where('branch_id', $branch_id)
            ->whereNull('deleted_at')
            ->sum('quantity');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreStockRequest $request)
    {
        $branch_id = auth()->user()->employee->branch_id;
        $product_id = $request->input('product_id');
        $quantity = $request->input('quantity');

        $stock = Stock::where('product_id', $product_id)
            ->where('branch_id', $branch_id)
            ->whereNull('deleted_at')
            ->first();

        if ($stock) {
            $stock->quantity += $quantity;
            $stock->save();
            return response('Successfully added.');
        } else {
            $request['branch_id'] = $branch_id;
            $stock = Stock::create($request->all());
            if ($stock) return response('Successfully created.', 201);
        }

        return response('Failed.', 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return StockResource::make(Stock::find($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateStockRequest $request, $id)
    {
        $stock = Stock::where('id', $id)
            ->whereNull('deleted_at')
            ->update([
                'quantity' => $request->input('quantity'),
                'minimum' => $request->input('minimum')
            ]);

        return $stock ? response('Successfully updated.')
            : response('Stock not found.', 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Stock::find($id)->delete();
        return response('Successfully deleted.');
    }


    public function importCSV(Request $request)
    {
        (new StockImport)->queue($request->file('csv'), null, \Maatwebsite\Excel\Excel::CSV);
        return response('Successfully imported.', 201);
    }

    public function exportCSV()
    {

        $filename = 'stocks.csv';
        $products = Product::join('stocks', 'products.id', '=', 'stocks.product_id')
            ->select(
                'products.name',
                'stocks.product_id',
                'stocks.quantity',
                'stocks.minimum'
            )
            ->whereNull('stocks.deleted_at')
            ->get();

        $columns = [
            'product_id',
            'name',
            'quantity',
            'minimum'
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
}
