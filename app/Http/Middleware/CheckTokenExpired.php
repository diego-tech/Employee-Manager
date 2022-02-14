<?php

namespace App\Http\Middleware;

use Closure;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;

class CheckTokenExpired
{
    /**
     * Handle an incoming request.
     * Check If Token Is Expired
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = ["status" => 1,  "data" => [], "msg" => ""];

        $user = $request->user;

        if($user) {
            $update_token = $user->update_token;
            $diff24Hours = new DateInterval('PT12H');
            $update_token_datetime = new DateTime($update_token);
            $add_period = $update_token_datetime->add($diff24Hours);
            $now = new DateTime('now');

            if($add_period > $now) {
                return $next($request);
            } else {
                $response['msg'] = "Sesión Expirada Vuelva a Logearse";
                $response['status'] = 0;
            }
        
        } else {
            $response['msg'] = "Api Key No Válida";
            $response['status'] = 0;
        }

        return response()->json($response);
    }
}
