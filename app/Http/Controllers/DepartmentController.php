<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return DepartmentResource::collection(
            Department::whereNull('deleted_at')
                ->paginate(10)
        );
    }

    public function dropdown()
    {
        return DepartmentResource::collection(
            Department::whereNull('deleted_at')
                ->get()
        );
    }

    public function count()
    {
        return Department::select('id')
            ->whereNull('deleted_at')
            ->count();
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $department = Department::whereNull('deleted_at')
            ->where('id', $id)
            ->first();

        return $department ?  response($department)
            : response('Not Exist', 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $search
     * @return \Illuminate\Http\Response
     */
    public function search($search)
    {
        return DepartmentResource::collection(
            Department::where('name', 'like', '%' . $search . '%')
                ->whereNull('deleted_at')
                ->orWhere('id', $search)
                ->paginate(10)
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDepartmentRequest $request, $id)
    {

        $department =  Department::where('id', $id)
            ->update($request->only(['name', 'description']));

        return $department ? response("Successfully updated!")
            : response("No changes", 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Department::find($id)->delete();

        return response("Successfully deleted.");
    }
}
