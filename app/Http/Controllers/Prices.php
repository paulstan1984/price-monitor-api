<?php

namespace App\Http\Controllers;

use App\Price;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; 
use Illuminate\Support\Facades\Validator;
use App\Services\PaginationService;

class Prices extends Controller
{

    protected $paginationService;

    public function __construct(PaginationService $paginationService) {

        $this->paginationService = $paginationService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $prices = Price::all();
        foreach($prices as $item){
            $item->store->get();
            $item->product->get();
        }
        return response()->json($prices, 200);
    }

    /**
     * Price a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'row.*.product_id' => ['required','numeric', 'min:0', 'not_in:0', 'exists:products,id'],
            'row.*.store_id' => ['required','numeric', 'min:0', 'not_in:0', 'exists:stores,id'],
            'row.*.amount' => ['required','numeric', 'min:0', 'not_in:0'],
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 400);
        }

        $data = $validator->valid();
        // $item = new Price();
        // $item->product_id = $data['product_id'];
        // $item->store_id = $data['store_id'];
        // $item->amount = $data['amount'];
        // $item->save();
        // $item->product->get();
        // $item->store->get();
        // return response()->json($item, 200);

        return response()->json($data, 200);
    }

    /**
     * Price a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function buy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => ['required','numeric', 'min:0', 'not_in:0', 'exists:products,id'],
            'store_id' => ['required','numeric', 'min:0', 'not_in:0', 'exists:stores,id'],
            'amount' => ['required','numeric', 'min:0', 'not_in:0'],
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 400);
        }

        $data = $validator->valid();
        $item = new Price();
        $item->product_id = $data['product_id'];
        $item->store_id = $data['store_id'];
        $item->amount = $data['amount'];
        $item->save();
        $item->product->get();
        $item->store->get();
        return response()->json($item, 200);
    }

    /**
     * Search price
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product' => ['string', 'nullable'],
            'store' => ['string', 'nullable'],
            'order_by' => ['string'],
            'order_dir' => ['string'],
            'page' => ['required','numeric', 'min:1'],
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 400);
        }

        $data = $validator->valid();
        
        $items = Price::query()->with(['product', 'store']);

        if(!empty($data['product'])){
            $items = $items->whereHas('product', function($q) use ($data) {
                $q->where('name', 'like', '%' . $data['product'] . '%');
            });
        }

        if(!empty($data['store'])){
            $items = $items->whereHas('store', function($q) use ($data) {
                $q->where('name', 'like', '%' . $data['store'] . '%');
            });
        }

        if(!empty($data['order_by']) && !empty($data['order_by_dir'])) {
            $items = $this
                ->paginationService
                ->applyOrder($items, $data['order_by'], $data['order_by_dir'], 'prices');
        } else if(!empty($data['order_by'])) {
            $items = $this
                ->paginationService
                ->applyOrder($items, $data['order_by'], 'ASC', 'prices');
        }

        $items = $this->paginationService->applyPagination($items, $data['page']);
        
        return response()->json($items, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param int id the id of the store
     * @return \Illuminate\Http\Response
     */
    public function read(int $id)
    {
        $item = Price::find($id);

        if($item == null){
            return response()->json('Not Found', 404);
        }
        $item->product->get();
        $item->store->get();

        return response()->json($item, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param int id the id of the store
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        $item = Price::find($id);

        if($item == null){
            return response()->json('Not Found', 404);
        }


        $validator = Validator::make($request->all(), [
            'product_id' => ['required','numeric', 'min:0', 'not_in:0', 'exists:products,id'],
            'store_id' => ['required','numeric', 'min:0', 'not_in:0', 'exists:stores,id'],
            'amount' => ['required','numeric', 'min:0', 'not_in:0'],
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 400);
        }

        $data = $validator->valid();
        $item->product_id = $data['product_id'];
        $item->store_id = $data['store_id'];
        $item->amount = $data['amount'];
        $item->save();
        $item->category->get();

        return response()->json($item, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int id the id of the store
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        $item = Price::find($id);

        if($item == null){
            return response()->json('Not Found', 404);
        }

        $item->delete();

        return response()->json('Deleted', 200);
    }
}
