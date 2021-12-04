<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
                $response['msg'] = "Ha ocurrido un error " . $validator->errors()->first();
            } else {
                $user = new User();

                $user->name = $data->name;
                $user->email = $data->email;
                $user->password = Hash::make($data->password);
                $user->workplace = $data->workplace;
                $user->salary = $data->salary;
                $user->biography = $data->biography;

                $user->save();
                $response['msg'] = "Usuario guardado correctamente";
            }
        } catch (\Exception $e) {
            $response['msg'] = "Ha ocurrido un error " . $e->getMessage();
            $response['status'] = 0;
        }
        return response()->json($response);
    }
    
    public function login(Request $request){
        $response = ["status" => 1, "msg" => ""];
        
        $data = $request->getContent();
        $data = json_decode($data);

        try {
            $user = User::where('email', $data->email)->first();
            
            if ($user) {
                $hash_check = Hash::check($data->password, $user->password);

                if($hash_check){
                    do {
                        $user_token = Hash::make(now().$user->id);
                        $user->api_token = $user_token;
                        $user->save();
                    } while ($user->api_token != $user_token);

                    $response['msg'] = "Token: " . $user_token;
                } else {
                    $response['msg'] = "Ha ocurrido un error, contraseña introducida erronea";
                    $response['status'] = 0;
                }
            } else {
                $response['msg'] = "Este usuario no está registrado";
                $response['status'] = 0;
            }
        } catch (\Exception $e) {
            $response['msg'] = "Ha ocurrido un error " . $e->getMessage();
            $response['status'] = 0;
        }

        return response()->json($response);
    }

    public function employee_list(Request $request){
        $response = ["status" => 1, "msg" => ""];

        $req_user = $request->user;

        try {
            if ($req_user->workplace == "Directivo"){
                $users = User::where('workplace', 'Empleado')
                    ->orWhere('workplace', 'RRHH')
                    ->get();
                
                $response['msg'] = $this->employee_list_response($users);
                $response['status'] = 1;
            }

            if ($req_user->workplace == "RRHH") {
                $users = User::where('workplace', 'Empleado')->get();
                    
                $response['msg'] = $this->employee_list_response($users);
                $response['status'] = 1;
            }

        } catch (\Exception $e) {
            $response['msg'] = "Ha ocurrido un error " . $e->getMessage();
            $response['status'] = 0;
        }

        return response()->json($response);
    }

    public function employee_detail(Request $request){
        $response = ["status" => 1, "msg" => ""];

        $req_user = $request->user;

        $user_id = $request->user_id;

        try {
            if ($user_id) {
                if ($req_user->workplace == "Directivo"){
                    $user = User::where('id', $user_id)->first();    
                    
                    $response['msg'] = $this->employee_detail_response($user);
                    $response['status'] = 1;
                }
    
                if ($req_user->workplace == "RRHH") {
                    $user = User::where('id', $user_id)->first();

                    if($user->workplace == "Directivo") {
                        $response['msg'] = "No tienes permisos para ver este usuario";
                        $response['status'] = 0;
                    } else {
                        $response['msg'] = $this->employee_detail_response($user);
                        $response['status'] = 1;
                    }
                }
            } else {
                $response['msg'] = "Introduce el id del usuario";
                $response['status'] = 0;
            }

        } catch (\Exception $e) {
            $response['msg'] = "Ha ocurrido un error " . $e->getMessage();
            $response['status'] = 0;
        }

        return response()->json($response);
    }

    public function see_profile(Request $request){
        $response = ["status" => 1, "msg" => ""];

        try {
            $response['msg'] = $request->user;
            $response['status'] = 1;
        } catch (\Exception $e) {
            $response['msg'] = "Ha ocurrido un error " . $e->getMessage();
            $response['status'] = 0;
        }
        return response()->json($response);
    }
 
    private function employee_list_response($users){
        foreach ($users as $user) {
            $query_response['Name'] = $user->name;
            $query_response['Workplace'] = $user->workplace;
            $query_response['Salary'] = $user->salary;

            $result_query[] = $query_response;
        }

        return $result_query;
    }

    private function employee_detail_response($user){
        $query_response['Name'] = $user->name;
        $query_response['Email'] = $user->email;
        $query_response['Workplace'] = $user->workplace;
        $query_response['Biography'] = $user->biography;
        $query_response['Salary'] = $user->salary;

        return $query_response;
    }
}
