<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCareerRequest;
use App\Http\Requests\UpdateCareerRequest;
use App\Http\Resources\CareerResource;
use App\Models\Career;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CareerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $careers = Career::whereNull('deleted_at');

        if (!empty($search)) {
            $careers->where('title', 'like', "%$search%")
                ->orWhere('location', 'like', "%$search%");
        }

        return CareerResource::collection(
            $careers->orderBy('id', 'DESC')
                ->paginate(10)
        );
    }

    public function list($count)
    {
        $careers = Career::whereNull('deleted_at')
            ->get()
            ->chunk($count, function ($career) {
                foreach ($career as $value) {
                    return $value;
                }
            });

        return sizeof($careers) > 0 ? $careers[0] : [];
    }

    public function count()
    {
        return Career::select('id')
            ->whereNull('deleted_at')
            ->count();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCareerRequest $request)
    {
        $career = Career::create($request->all());

        return $career ? response("Successfully created.", 201)
            : response("Failed.", 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return CareerResource::make(
            Career::where('id', $id)
                ->whereNull('deleted_at')
                ->first()
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $search
     * @return \Illuminate\Http\Response
     */
    public function search($search)
    {
        if (!empty($search)) {
            return DB::table('careers')
                ->where('location', 'like', '%' . $search . '%')
                ->orWhere('title', 'like', '%' . $search . '%')
                ->whereNull('deleted_at')
                ->get();
        }
        return response([], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCareerRequest $request, $id)
    {
        $career = Career::where('id', $id)
            ->update($request->only([
                'title', 'location', 'description'
            ]));

        return $career ? response("Successfully updated.")
            : response("Failed.", 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Career::find($id)->delete();

        return response("Successfully deleted.");
    }
}
