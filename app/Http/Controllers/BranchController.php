<?php

namespace App\Http\Controllers;

use App\Http\Resources\BranchResource;
use App\Import\BranchImport;
use App\Models\Branch;
use Illuminate\Validation\Rule;
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
        return Branch::whereNull('deleted_at')->paginate(10);
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
            'phone_number',
            'started_at'
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function allStore()
    {
        return Branch::select(['name', 'address', 'phone_number'])
            ->where('deleted_at', null)
            ->get();
    }

    public function count()
    {
        $count = Branch::select('id')
            ->where('deleted_at', null)
            ->count();

        return $count;
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
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:branches|string|regex:/^[A-Za-z\s]*$/',
            'address' => 'required|string',
            'phone_number' => 'required|numeric|regex:/^9\d{9}$/|unique:branches',
            'started_at' => 'required'
        ]);

        Branch::create($request->all());
        return response("Successfully created!", 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $branch = Branch::where('deleted_at', null)
            ->where('id', $id)
            ->first();
        if ($branch === null) {
            return response('Not found', 400);
        }
        return response($branch, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $name
     * @return \Illuminate\Http\Response
     */
    public function search($name)
    {
        return Branch::where('name', 'like', '%' . $name . '%')
            ->where('deleted_at', null)
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
            'name' => 'required|string|regex:/^[A-Za-z\s]*$/',
            'address' => 'required|string',
            'phone_number' => ['required', 'string', 'regex:/^9\d{9}$/', Rule::unique("branches", "phone_number")->ignore($id)],
            'started_at' => 'required'
        ]);
        $branch = Branch::find($id);
        if ($branch !== null) {
            if ($branch->update($request->all())) {
                return response("Successfully updated!", 200);
            }
        }
        return response("Branch does not exist!", 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $branch =  Branch::find($id);
        if ($branch !== null) {
            $branch->delete();
            return response("Successfully deleted!", 200);
        }
        return response("Branch does not exist!", 400);
    }
}
