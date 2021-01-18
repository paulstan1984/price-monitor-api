<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class User extends Controller
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
            $token = md5($password.env('ADMIN_KEY'));
            return response()->json(['token'=>$token], 200);
        }

        return response()->json(['Password'=>'Invalid login!'], 400);
    }
    
}
