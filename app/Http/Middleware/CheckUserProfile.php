<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class CheckUserProfile
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

        if($request->has('api_token')) {
            // $token = $request->api_token;
            // $user = User::where('api_token', $token)->first();
            
            $token = $request->api_token;
            $user = User::find($token);

            if(!$user){
                $response["msg"] = "No";
                return response()->json($response);
            } else {
                if($user->workplace == 'RRHH' || $user->workplace == 'Directivo'){
                    return $next($request);
                } else {
                    return response()->json($response);
                }
            }
        }
    }
}
