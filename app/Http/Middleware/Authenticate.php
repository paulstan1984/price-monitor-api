<?php

namespace App\Http\Middleware;
use App\User;

class Authenticate 
{

    public static function getToken() {
        return md5(env('ADMIN_PASSWORD').env('ADMIN_KEY'));
    }

    public static function computeUserToken(User $user) {
        return md5($user->username.$user->password.time().env('ADMIN_KEY'));
    }

    public function handle($request, $next)
    {
        $user = User::where('token', $request->header('authorization'))
            ->orWhere('mobile_token', $request->header('mobile'))
            ->first();

        if($user != null) {
            return  $next($request);
        }

        return response(json_encode(['Error'=>'Invalid token!']), 403);
    }
}
