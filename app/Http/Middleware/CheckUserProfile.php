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
    
        $user = $request->user;

        if(!$user){
            $response["msg"] = "Usuario no Existe";
            $response["status"] = 0;
        } else {
            if($user->workplace == 'RRHH' || $user->workplace == 'Directivo'){
                return $next($request);
            } else {
                $response["msg"] = "No tienes los permisos suficentes";
                $response["status"] = 0;
            }
        }
        return response()->json($response);
    }
}
