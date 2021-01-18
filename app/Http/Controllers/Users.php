<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Middleware\Authenticate;

class Users extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $password = $request->get('Password');
        if($password == env('ADMIN_PASSWORD')) {
            return response()->json(['token'=>Authenticate::getToken()], 200);
        }

        return response()->json(['Password'=>'Invalid login!'], 400);
    }
    
}
