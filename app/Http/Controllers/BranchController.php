<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Http\Resources\BranchResource;
use App\Import\BranchImport;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return BranchResource::collection(
            Branch::whereNull('deleted_at')
                ->paginate(10)
        );
    }

    public function dropdown()
    {
        return BranchResource::collection(Branch::all());
    }

    public function importCSV(Request $request)
    {
        (new BranchImport)->queue($request->file('csv'), null, \Maatwebsite\Excel\Excel::CSV);
        return response('Successfully imported.', 201);
    }

    public function exportCSV()
    {
        $filename = 'branches.csv';
        $branches = BranchResource::collection(Branch::all());

        $columns = [
            'id',
            'name',
            'address',
            'phone_number'
        ];

        $file = fopen($filename, 'w');
        fputcsv($file, $columns);

        foreach ($branches as $branch) {
            $row = [];

            foreach ($columns as $column) {
                $row[$column] = $branch[$column];
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


    public function count()
    {
        return Branch::select('id')
            ->whereNull('deleted_at')
            ->count();
    }

    public function sales($id)
    {
        return DB::table('orders as o')
            ->join('carts as c', 'o.id', '=', 'c.order_id')
            ->where('o.branch_id', $id)
            ->where('amount_tendered', '>', 0)
            ->sum('c.total_price');
    }

    public function purchased($id)
    {
        return DB::table('orders as o')
            ->join('carts as c', 'o.id', '=', 'c.order_id')
            ->where('o.branch_id', $id)
            ->where('amount_tendered', '>', 0)
            ->count();
    }

    public function mostPurchased($id)
    {
        return DB::table('orders as o')
            ->join('carts as c', 'o.id', '=', 'c.order_id')
            ->join('products as p', 'c.product_id', '=', 'p.id')
            ->select(DB::raw('sum(c.quantity) as product_count, p.name'))
            ->where('o.branch_id', $id)
            ->where('amount_tendered', '>', 0)
            ->groupBy('p.name')
            ->orderBy('product_count', 'DESC')
            ->take(1)
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBranchRequest $request)
    {
        Branch::create($request->only([
            'name', 'address', 'phone_number'
        ]));
        return response("Successfully created.", 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return  BranchResource::make(
            Branch::whereNull('deleted_at')
                ->where('id', $id)
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
    public function update(UpdateBranchRequest $request, $id)
    {
        $branch = Branch::where('id', $id)
            ->update($request->only([
                'name', 'address', 'phone_number'
            ]));
        if ($branch) {
            return response("Successfully updated.");
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
        Branch::find($id)->delete();
        return response("Successfully deleted.");
    }
}
