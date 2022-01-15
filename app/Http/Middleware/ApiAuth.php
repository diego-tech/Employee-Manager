<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class ApiAuth
{
    /**
     * Handle an incoming request.
     * Check If User Is Login
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = ["status" => 1, "data" => []];

        $headers = $request->header('Authorization');

        if ($headers){
            $user_token = $headers;
            $user = User::where('api_token', $user_token)->first();

            if(!$user) {
                $response['data']['msg'] = "Api Key No Válida";
                $response['status'] = 0;
            } else {
                $request->user = $user;
                return $next($request);
            }
        } else {
            $response['data']['msg'] = "Api Key no introducida";
            $response['status'] = 0;
        }

        return response()->json($response);
    }
}
