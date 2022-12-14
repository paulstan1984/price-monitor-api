<?php

namespace App\Http\Middleware;

use App\User;

class Authenticate
{

    public static function getToken()
    {
        return md5(env('ADMIN_PASSWORD') . env('ADMIN_KEY'));
    }

    public static function computeUserToken(User $user)
    {
        return md5($user->username . $user->password . time() . env('ADMIN_KEY'));
    }

    public function handle($request, $next)
    {
        $user = User::where('token', $request->header('authorization'))
            ->first();

        if ($user != null) {
            $request->attributes->set("user_id", $user->id);
            $request->attributes->set("admin", true);
            return  $next($request);
        }

        $user = User::where('mobile_token', $request->header('mobile'))
            ->first();

        if ($user != null) {
            $request->attributes->set("user_id", $user->id);
            return  $next($request);
        }

        return response(json_encode(['Error' => 'Invalid token!']), 403);
    }
}
