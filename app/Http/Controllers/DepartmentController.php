<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Department::where('deleted_at', null)->paginate(10);
    }

    public function dropdown()
    {
        return Department::select('id', 'name')
            ->where('deleted_at', null)
            ->get();
    }

    public function count()
    {
        $count = Department::select('id')
            ->where('deleted_at', null)
            ->count();
        return $count;
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $department = Department::where('deleted_at', null)
            ->where('id', $id)
            ->get()
            ->first();
        if ($department === null) {
            return response('Not Exist', 400);
        }
        return response($department, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $search
     * @return \Illuminate\Http\Response
     */
    public function search($search)
    {
        return Department::where('name', 'like', '%' . $search . '%')
            ->where('deleted_at', null)
            ->orWhere('id', $search)
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
            'name' => 'required|string',
            'description' => 'required|string'
        ]);

        $department =  Department::find($id);
        if ($department !== null) {
            $department->update($request->all());
            return response("Successfully updated!", 200);
        }
        return response("No changes", 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $department = Department::find($id);

        if ($department !== null && $department->deleted_at === null) {
            $department->delete();
            return response("Successfully deleted!", 200);
        }
        return response("Department is not exists!", 400);
    }
}
