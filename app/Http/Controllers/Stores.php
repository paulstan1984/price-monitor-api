<?php

namespace App\Http\Controllers;

use App\Store;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; 
use Illuminate\Support\Facades\Validator;
use App\Services\PaginationService;

class Stores extends Controller
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
        $stores = Store::all();
        return response()->json($stores, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required','max:100', Rule::unique('stores')],
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 400);
        }

        $data = $validator->valid();
        $item = new Store();
        $item->name = $data['name'];
        $item->save();
        return response()->json($item, 200);
    }

    /**
     * Search stores
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['string'],
            'order_by' => ['string'],
            'order_dir' => ['string'],
            'page' => ['required','numeric', 'min:1'],
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 400);
        }

        $data = $validator->valid();
        
        $items = Store::query();

        if(!empty($data['name'])){
            $items = $items->where('name', 'like', '%' . $data['name'] . '%');
        }

        if(!empty($data['order_by']) && !empty($data['order_by_dir'])) {
            $items = $this->paginationService->applyOrder($items, $data['order_by'], $data['order_by_dir']);
        } else if(!empty($data['order_by'])) {
            $items = $this->paginationService->applyOrder($items, $data['order_by']);
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
        $item = Store::find($id);

        if($item == null){
            return response()->json('Not Found', 404);
        }

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
        $item = Store::find($id);

        if($item == null){
            return response()->json('Not Found', 404);
        }


        $validator = Validator::make($request->all(), [
            'name' => ['required','max:100', Rule::unique('stores')->ignore($id)],
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 400);
        }

        $data = $validator->valid();
        $item->name = $data['name'];
        $item->save();

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
        $item = Store::find($id);

        if($item == null){
            return response()->json('Not Found', 404);
        }

        $item->delete();

        return response()->json('Deleted', 200);
    }
}
