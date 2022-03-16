<?php

namespace App\Http\Controllers;

use App\Product;
use App\Price;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; 
use Illuminate\Support\Facades\Validator;
use App\Services\PaginationService;
use Illuminate\Support\Facades\DB;

class Products extends Controller
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
        $products = Product::all();
        foreach($products as $item){
            $item->category->get();
        }
        return response()->json($products, 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function lastOrderedProducts()
    {
        $items = Product::query();
        $items = $items->whereIn('product_id', DB::raw('select '));
        $items = $items->take(20);

        return response()->json($items, 200);
    }

    /**
     * Product a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required','max:100', Rule::unique('products')],
            'category_id' => ['required','numeric', 'min:0', 'not_in:0', 'exists:categories,id'],
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 400);
        }

        $data = $validator->valid();
        $item = new Product();
        $item->name = $data['name'];
        $item->category_id = $data['category_id'];
        $item->save();
        $item->category->get();
        return response()->json($item, 200);
    }

    /**
     * Search products
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['string', 'nullable'],
            'category' => ['string', 'nullable'],
            'order_by' => ['string'],
            'order_dir' => ['string'],
            'page' => ['required','numeric', 'min:1'],
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 400);
        }

        $data = $validator->valid();
        
        $items = Product::query()->with(['category']);

        if(!empty($data['category'])){
            $items = $items->whereHas('category', function($q) use ($data) {
                $q->where('name', 'like', '%' . $data['category'] . '%');
            });
        }

        if(!empty($data['name'])){
            $items = $items->where('name', 'like', '%' . $data['name'] . '%');
        }

        if(!empty($data['order_by']) && !empty($data['order_by_dir'])) {
            $items = $this->paginationService->applyOrder($items, $data['order_by'], $data['order_by_dir']);
        } else if(!empty($data['order_by'])) {
            $items = $this->paginationService->applyOrder($items, $data['order_by']);
        }

        $items = $this->paginationService->applyPagination($items, $data['page']);

        foreach($items['results'] as &$product) {
            $product['lastPrice'] = Price::getLastPrice($product['id']);
            $product['avgPrice'] = Price::getAvgPrice($product['id']);
        }
        
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
        $item = Product::find($id);

        if($item == null){
            return response()->json('Not Found', 404);
        }
        $item->category->get();

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
        $item = Product::find($id);

        if($item == null){
            return response()->json('Not Found', 404);
        }


        $validator = Validator::make($request->all(), [
            'name' => ['required','max:100', Rule::unique('products')->ignore($id)],
            'category_id' => ['required','numeric', 'min:0', 'not_in:0', 'exists:categories,id'],
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 400);
        }

        $data = $validator->valid();
        $item->name = $data['name'];
        $item->category_id = $data['category_id'];
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
        $item = Product::find($id);

        if($item == null){
            return response()->json('Not Found', 404);
        }

        $item->delete();

        return response()->json('Deleted', 200);
    }
}
