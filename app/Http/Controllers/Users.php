<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Middleware\Authenticate;
use Illuminate\Validation\Rule; 
use Illuminate\Support\Facades\Validator;
use App\Services\PaginationService;
use App\User;

class Users extends Controller
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
    public function login(Request $request)
    {
        $username = $request->get('Username');
        $password = $request->get('Password');
        $user = User::where('username', $username)
            ->where('password', $password)
            ->first();

        if($user != null) {
            $user->token = Authenticate::computeUserToken($user);
            $user->save();
            return response()->json(['token'=>$user->token], 200);
        }

        return response()->json(['Password'=>'Invalid login!'], 400);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function mobile_login(Request $request)
    {
        $username = $request->get('Username');
        $password = $request->get('Password');
        $user = User::where('username', $username)
            ->where('password', $password)
            ->first();

        if($user != null) {
            $user->mobile_token = Authenticate::computeUserToken($user);
            $user->save();
            return response()->json(['token'=>$user->mobile_token], 200);
        }

        return response()->json(['Password'=>'Invalid login!'], 400);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $user = User::where('token', $request->header('authorization'))
            ->first();

        if($user != null) {
            $user->token = '';
            $user->save();
        }

        return response()->json(['loggedout'=>true], 200);
    }


    #region "Admin management methods"
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();
        return response()->json($users, 200);
    }

    public function test()
    {
        $user = (object) array();
        $user->message = '';
        $user->date = date('Y-m-d H:i:s');
        return response()->json($user, 200);
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
            'username' => ['required','max:100', Rule::unique('users')],
            'password' => ['required','min:4','max:20'],
            'name' => ['required','max:100']
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 400);
        }

        $data = $validator->valid();
        $item = new User();
        $item->username = $data['username'];
        $item->password = $data['password'];
        $item->name = $data['name'];
        $item->token = '';
        $item->mobile_token = '';
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
        
        $items = User::query();

        if(!empty($data['name'])){
            $items = $items->where('name', 'like', '%' . $data['name'] . '%');
        }

        if(!empty($data['order_by']) && !empty($data['order_by_dir'])) {
            $items = $this->paginationService->applyOrder($items, $data['order_by'], $data['order_by_dir']);
        } else if(!empty($data['order_by'])) {
            $items = $this->paginationService->applyOrder($items, $data['order_by']);
        }

        $items = $this->paginationService->applyPagination($items, $data['page']);

        foreach($items['results'] as &$item){
            $item['password'] = '***';
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
        $item = User::find($id);

        if($item == null){
            return response()->json('Not Found', 404);
        }

        $item->password = '***';

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
        $item = User::find($id);

        if($item == null){
            return response()->json('Not Found', 404);
        }


        $validator = Validator::make($request->all(), [
            'username' => ['required','max:100', Rule::unique('users')->ignore($id)],
            'name' => ['required','max:100'],
            'password' => ['max:20'],
        ]);
        if($validator->fails()){
            return response()->json($validator->messages(), 400);
        }

        $data = $validator->valid();
        $item->username = $data['username'];
        $item->name = $data['name'];
        if(!empty($data['password']) && $data['password'] != '***') {
            $item->password = $data['password'];
        }
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
        $item = User::find($id);

        if($item == null){
            return response()->json('Not Found', 404);
        }

        $item->delete();

        return response()->json('Deleted', 200);
    }
    #endregion 
}

