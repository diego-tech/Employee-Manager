<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = ["status" => 1, "msg" => ""];

        if ($request->has('api_token')){
            $user_token = $request->api_token;
            $user = User::where('api_token', $user_token)->first();

            if(!$user) {
                $response['msg'] = "Api Key no válida";
                $response['status'] = 0;
            } else {
                $request->user = $user;
                return $next($request);
            }
        } else {
            $response['msg'] = "Api Key no introducida";
            $response['status'] = 0;
        }

        return response()->json($response);
    }
}
