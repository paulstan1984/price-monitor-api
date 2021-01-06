<?php

namespace App\Http\Middleware;

class Authenticate 
{
    public function handle($request, $next)
    {
        if($request->header('authorization') == 'asdasd') {
            return  $next($request);
        }

        $obj = (object)[];
        $obj->message = 'Invalid token';
        return response(json_encode($obj), 403);
    }
}
