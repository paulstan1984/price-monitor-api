<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Middleware\Authenticate;
use App\User;

class Users extends Controller
{
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

        // if($password == env('ADMIN_PASSWORD')) {
        //     return response()->json(['token'=>Authenticate::getToken()], 200);
        // }

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
    public function logout(Request $request)
    {
        $user = User::where('token', $request->header('authorization'))
            ->first();

        if($user != null) {
            $user->token = '';
            $user->save();
            return response()->json(['loggedout'=>true], 200);
        }

        return response(json_encode(['Error'=>'Invalid token!']), 403);
    }
}
