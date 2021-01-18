<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Log;

class Authenticate 
{

    public static function getToken() {
        return md5(env('ADMIN_PASSWORD').env('ADMIN_KEY'));
    }

    public function handle($request, $next)
    {
        if($request->header('authorization') == Authenticate::getToken()) {
            return  $next($request);
        }

        return response(json_encode(['Error'=>'Invalid token!']), 403);
    }
}
