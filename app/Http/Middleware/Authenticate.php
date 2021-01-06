<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Log;

class Authenticate 
{
    public function handle($request, $next)
    {
        Log::debug('An informational message.');

        if($request->header('authorization') == 'asdasd') {
            return  $next($request);
        }

        $obj = (object)[];
        $obj->message = 'Invalid token';
        return response(json_encode($obj), 403);
    }
}
