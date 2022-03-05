<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Import\CategoryImport;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function dropdown()
    {
        return CategoryResource::collection(
            Category::whereNull('deleted_at')
                ->where('id', '!=', 1)
                ->get()
        );
    }

    public function importCSV(Request $request)
    {
        (new CategoryImport)->queue($request->file('csv'), null, \Maatwebsite\Excel\Excel::CSV);
        return response('Successfully imported.', 201);
    }

    public function exportCSV()
    {
        $filename = 'categories.csv';
        $categories = CategoryResource::collection(Category::all());

        $columns = [
            'id',
            'name'
        ];


        $file = fopen($filename, 'w');
        fputcsv($file, $columns);

        foreach ($categories as $category) {
            $row = [];

            foreach ($columns as $column) {
                $row[$column] = $category[$column];
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
    public function index()
    {
        return CategoryResource::collection(
            Category::whereNull('deleted_at')
                ->where('id', '!=', 1)
                ->orderBy('id', 'DESC')
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
            'name' => 'required|string|unique:categories'
        ]);

        $category = Category::create($request->all());
        if ($category) {
            return response('Successfully created.', 201);
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
        return Category::where('id', $id)
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
            'name' => ['required', 'string', Rule::unique("categories", "name")->ignore($id)],
        ]);

        $category = Category::where('id', $id)
            ->whereNull('deleted_at')
            ->update([
                'name' => $request->name
            ]);

        if ($category) {
            return response('Successfully updated.', 200);
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
        $category = Category::find($id);

        if ($category) {
            $category->delete();
            return response('Successfully deleted.', 200);
        }

        return response('Failed.', 400);
    }
}
