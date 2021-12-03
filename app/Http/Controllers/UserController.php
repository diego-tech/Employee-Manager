<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(Request $request){

        $response = ["status" => 1, "msg" => ""];

        $data = $request->getContent();
        
        $validator = Validator::make(json_decode($data, true),[
            'name' => 'required|max:255',
            'email' => 'required|unique:users|max:255',
            'password' => 'required|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/',
            'workplace' => 'required|in:Directivo,RRHH,Empleado',
            'salary' => 'required|max:255',
            'biography' => 'required'
        ]);
        
        try {
            $data = json_decode($data);
            
            if($validator->fails()){
                $response['msg'] = $validator->errors()->first();
            } else {
                $user = new User();

                $user->name = $data->name;
                $user->email = $data->email;
                $user->password = $data->password;
                $user->workplace = $data->workplace;
                $user->salary = $data->salary;
                $user->biography = $data->biography;

                $user->save();
                $response['msg'] = "Usuario guardado correctamente";
            }
        } catch (\Exception $e) {
            $response['msg'] = "Ha ocurrido un error " . $e->getMessage();
        }

        // Validator
        // https://laravel.com/docs/8.x/validation
        // $validator = Validator::make(json_decode($request->getContent(), true)[]);

        // Middleware
        // https://laravel.com/docs/8.x/middleware


        // do while token generate
        // pasarle middleware kernel
        // ->withoutMiddleware([EnsureTokenIsValid::class]
        return response()->json($response);
    }
}
