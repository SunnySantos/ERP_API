<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexStockTransferRequest;
use App\Http\Requests\StockTransferRequest;
use App\Http\Resources\StockTransferResource;
use App\Models\Stock;
use App\Models\StockTransfer;
use Illuminate\Http\Request;

class StockTransferController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(IndexStockTransferRequest $request)
    {
        $branch_id = auth()->user()->employee->branch_id;
        $isSender = $request->input('sender');

        $stocks = StockTransfer::orderBy('id', 'DESC');

        if ($isSender) {
            $stocks->where('branch_sender_id', $branch_id);
        } else {
            $stocks->where('branch_receiver_id', $branch_id);
        }

        return StockTransferResource::collection(
            $stocks->whereNull('deleted_at')
                ->paginate(10)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StockTransferRequest $request)
    {
        $branch_id = auth()->user()->employee->branch_id;
        $branch_receiver_id = $request->input('branch_receiver_id');
        $stock_id = $request->input('stock_id');
        $quantity = $request->input('quantity');

        $stock = Stock::where('branch_id', $branch_id)
            ->where('id', $stock_id)
            ->whereNull('deleted_at')
            ->first();

        if ($stock) {
            $product_id = $stock->product_id;

            $receiver_stock = Stock::where('product_id', $product_id)
                ->where('branch_id', $branch_receiver_id)
                ->whereNull('deleted_at')
                ->first();

            if ($receiver_stock) {
                $receiver_stock->quantity += $quantity;
                $receiver_stock->save();
            } else {
                $receiver_stock = Stock::create([
                    'branch_id' => $branch_receiver_id,
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'minimum' => 0
                ]);
            }


            if ($receiver_stock) {
                $stock->quantity -= $quantity;
                $stock->save();

                $request['branch_sender_id'] = $branch_id;
                $request['stock_id'] = $receiver_stock->id;
                StockTransfer::create($request->all());

                return response('Successfully added.', 201);
            }
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        StockTransfer::find($id)->delete();
        return response('Successfully deleted.');
    }
}
