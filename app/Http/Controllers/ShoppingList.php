<?php

namespace App\Http\Controllers;

use App\Price;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; 
use Illuminate\Support\Facades\Validator;
use App\Services\PaginationService;
use Illuminate\Support\Facades\Storage;

class ShoppingList extends Controller
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
        $shopping_list = json_decode(Storage::disk('local')->get(env('SHOPPING_LIST_FILE')));
        return response()->json($shopping_list, 200);
    }

    /**
     * Price a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $shopping_list = json_encode($request->all());
        Storage::disk('local')->put(env('SHOPPING_LIST_FILE'), $shopping_list);
        return response()->json($request->all(), 200);
    }
    
}
