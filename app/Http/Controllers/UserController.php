<?php

namespace App\Http\Controllers;

use App\Mail\RetrievePassword;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * User Register
     * 
     * @param \Illuminate\Http\Request $request
     * @return response()->json($response)
     */
    public function register(Request $request)
    {
        $response = ["status" => 1, "data" => [], "msg" => ""];

        $data = $request->getContent();

        $validator = Validator::make(json_decode($data, true), [
            'name' => 'required|max:255',
            'email' => 'required|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix|unique:users|max:255',
            'password' => 'required|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/',
            'workplace' => 'required|in:Directivo,RRHH,Empleado',
            'salary' => 'required|max:255',
            'biography' => 'required'
        ]);

        try {
            $data = json_decode($data);

            if ($validator->fails()) {
                $response['status'] = 0;
                $response['msg']= "Ha ocurrido un error: " . $validator->errors();
            } else {
                $user = new User();

                $user->name = $data->name;
                $user->email = $data->email;
                $user->password = Hash::make($data->password);
                $user->workplace = $data->workplace;
                $user->salary = $data->salary;
                $user->biography = $data->biography;

                $user->save();
                $response['status'] = 1;
                $response['msg'] = "Usuario guardado correctamente";
            }
        } catch (\Exception $e) {
            $response['status'] = 0;
            $response['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : $this->error);
        }
        return response()->json($response);
    }

    /**
     * User Login
     * 
     * @param \Illuminate\Http\Request $request
     * @return response()->json($response)
     */
    public function login(Request $request)
    {
        $response = ["status" => 1, "data" => [], "msg" => ""];

        $data = $request->getContent();
        $data = json_decode($data);

        try {
            $user = User::where('email', $data->email)->first();

            if ($user) {
                $hash_check = Hash::check($data->password, $user->password);

                if ($hash_check) {
                    $users_token = User::pluck('api_token')->toArray();
                    $update_token = new DateTime("now");

                    do {
                        $user_token = Hash::make(now() . $user->id . $user->name);
                    } while (in_array($user_token, $users_token));

                    $user->api_token = $user_token;
                    $user->update_token = $update_token->format('Y-m-d H:i:s');
                    $user->save();

                    $response['data'] = $user;
                    $response['msg'] = "Usuario Logeado Correctamente";
                    $response['status'] = 1;
                } else {
                    $response['msg'] = "Ha ocurrido un error, contraseña introducida erronea";
                    $response['status'] = 0;
                    $response['data']['data'] = "";
                }
            } else {
                $response['msg'] = "Este usuario no está registrado";
                $response['status'] = 0;
                $response['data']['data'] = "";
            }
        } catch (\Exception $e) {
            $response['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : $this->error);
            $response['status'] = 0;
            $response['data']['data'] = "";
        }

        return response()->json($response);
    }

    /**
     * List of Employees Depending on the Logged-In User
     * 
     * @param \Illuminate\Http\Request $request
     * @return response()->json($response)
     */
    public function employee_list(Request $request)
    {
        $response = ["status" => 1, "data" => [], "msg" => ""];

        $req_user = $request->user;

        try {
            if ($req_user->workplace == "Directivo") {
                $users = User::where('workplace', 'Empleado')
                    ->orWhere('workplace', 'RRHH')
                    ->get();

                $response['data'] = $this->employee_list_response($users);
                $response['status'] = 1;
            }

            if ($req_user->workplace == "RRHH") {
                $users = User::where('workplace', 'Empleado')->get();

                $response['data'] = $this->employee_list_response($users);
                $response['status'] = 1;
            }
        } catch (\Exception $e) {
            $response['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : $this->error);
            $response['status'] = 0;
        }

        return response()->json($response);
    }

    /**
     * Employee Detail Depending on the Logged-In User
     * 
     * @param \Illuminate\Http\Request $request
     * @return response()->json($response)
     */
    public function employee_detail(Request $request)
    {
        $response = ["status" => 1, "data" => [], "msg" => ""];

        $req_user = $request->user;
        $user_id = $request->user_id;

        try {
            if ($user_id) {
                $user = User::where('id', $user_id)->first();

                if ($user) {
                    if ($req_user->workplace == "Directivo") {
                        if ($user->workplace == "Directivo" && $req_user->id != $user_id) {
                            $response['msg'] = "No tienes permisos para ver este usuario";
                            $response['status'] = 0;
                        } else {
                            $response['data'] = $this->employee_detail_response($user);
                            $response['status'] = 1;
                        }
                    }

                    if ($req_user->workplace == "RRHH") {
                        if ($user->workplace == "Directivo" || $user->workplace == "RRHH" && $req_user->id != $user_id) {
                            $response['msg'] = "No tienes permisos para ver este usuario";
                            $response['status'] = 0;
                        } else {
                            $response['data'] = $this->employee_detail_response($user);
                            $response['status'] = 1;
                        }
                    }
                } else {
                    $response['msg'] = "El Usuario No Existe";
                    $response['status'] = 0;
                }
            } else {
                $response['msg'] = "Introduce el id del usuario";
                $response['status'] = 0;
            }
        } catch (\Exception $e) {
            $response['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : $this->error);
            $response['status'] = 0;
        }

        return response()->json($response);
    }

    /**
     * View the Profile of the Logged-In User
     * 
     * @param \Illuminate\Http\Request $request
     * @return response()->json($response)
     */
    public function see_profile(Request $request)
    {
        $response = ["status" => 1, "data" => [], "msg" => ""];

        try {
            $response['data'] = $request->user;
            $response['status'] = 1;
        } catch (\Exception $e) {
            $response['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : $this->error);
            $response['status'] = 0;
        }
        return response()->json($response);
    }

    /**
     * Recovery User Password With Email
     * 
     * @param \Illuminate\Http\Request $request
     * @return response()->json($response)
     */
    public function retrieve_password(Request $request)
    {
        $response = ["status" => 1, "data" => [], "msg" => ""];

        $email = $request->email;

        try {
            if ($request->has('email')) {
                $user = User::where('email', $email)->first();

                if ($user) {
                    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+-=[]{};:,./?\|`~';
                    $regex = '/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/';

                    do {
                        $password = $this->randomPassword($characters, 6);
                    } while (!preg_match($regex, $password));

                    Mail::to($user->email)->send(new RetrievePassword("Recuperar Contraseña", "Recuperar Contraseña", $password));

                    $user->password = Hash::make($password);
                    $user->api_token = "";
                    $user->save();

                    $response['msg'] = "Contraseña enviada a Email: " . $user->email;
                    $response['status'] = 1;
                } else {
                    $response['msg'] = "Este Usuario No Está Registrado";
                    $response['status'] = 0;
                }
            } else {
                $response['msg'] = "Introduzca el email";
                $response['status'] = 0;
            }
        } catch (\Exception $e) {
            $response['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : $this->error);
            $response['status'] = 0;
        }

        return response()->json($response);
    }

    /**
     * Modify User Data
     * 
     * @param \Illuminate\Http\Request $request
     * @return response()->json($response)
     */
    public function modify_data(Request $request)
    {
        $response = ["status" => 1, "msg" => ""];

        $req_user = $request->user;
        $user_id = $request->user_id;

        $data = $request->getContent();

        $validator = Validator::make(json_decode($data, true), [
            'name' => 'max:255',
            'email' => 'regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix|unique:users|max:255',
            'password' => 'regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/',
            'workplace' => 'in:Directivo,RRHH,Empleado',
            'salary' => 'max:255',
            'biography' => ''
        ]);

        $data = json_decode($data);

        try {
            if ($user_id) {
                $user = User::where('id', $user_id)->first();

                if ($user) {
                    if ($req_user->workplace == "Directivo") {
                        if ($user->workplace == "Directivo" && $req_user->id != $user_id) {
                            $response['msg'] = "No tienes permisos para modificar este usuario";
                            $response['status'] = 0;
                        } else {
                            if (isset($data->password)) {
                                $response['msg'] = "No puedes modificar la contraseña";
                                $response['status'] = 0;
                            } else {
                                $this->checkModifyData($data, $user);

                                if ($validator->fails()) {
                                    $response['msg'] = "Ha ocurrido un error: " . $validator->errors();
                                    $response['status'] = 0;
                                } else {
                                    $user->save();
                                    $response['msg'] = "Usuario modificado correctamente";
                                    $response['status'] = 1;
                                }
                            }
                        }
                    }

                    if ($req_user->workplace == "RRHH") {
                        if ($user->workplace == "Directivo" || $user->workplace == "RRHH" && $req_user->id != $user_id) {
                            $response['msg'] = "No tienes permisos para modificar este usuario";
                            $response['status'] = 0;
                        } else {
                            if (isset($data->password)) {
                                $response['msg'] = "No puedes modificar la contraseña";
                                $response['status'] = 0;
                            } else {
                                $this->checkModifyData($data, $user);

                                if ($validator->fails()) {
                                    $response['msg'] = "Ha ocurrido un error: " . $validator->errors();
                                    $response['status'] = 0;
                                } else {
                                    $user->save();
                                    $response['msg'] = "Usuario modificado correctamente";
                                    $response['status'] = 1;
                                }
                            }
                        }
                    }
                } else {
                    $response['msg'] = "El Usuario No Existe";
                    $response['status'] = 0;
                }
            } else {
                $response['msg'] = "Introduce el id del usuario";
                $response['status'] = 0;
            }
        } catch (\Exception $e) {
            $response['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : $this->error);
            $response['status'] = 0;
        }

        return response()->json($response);
    }

    /**
     * Modify User Password
     * 
     * @param \Illuminate\Http\Request $request
     * @return response()->json($response)
     */
    public function modify_password(Request $request)
    {
        $response = ["status" => 1, "msg" => ""];

        $user = $request->user;
        $data = $request->getContent();

        $validator = Validator::make(json_decode($data, true), [
            'password' => 'regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{6,}/',
        ]);

        $data = json_decode($data);

        try {
            if ($user) {
                if (isset($data->password) && isset($data->repeat_password)) {
                    if ($data->password == $data->repeat_password) {
                        $user->password = Hash::make($data->password);

                        if ($validator->fails()) {
                            $response['msg'] = "Ha ocurrido un error: " . $validator->errors();
                            $response['status'] = 0;
                        } else {
                            $user->api_token = NULL;
                            $user->save();
                            $response['msg'] = "Contraseña Guardada Correctamente!!";
                            $response['status'] = 0;
                        }
                    } else {
                        $response['msg'] = "Las contraseñas no coinciden.";
                        $response['status'] = 0;
                    }
                } else {
                    $response['msg'] = "Introduzca la Contraseña";
                    $response['status'] = 0;
                }
            }
        } catch (\Exception $e) {
            $response['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : $this->error);
            $response['status'] = 0;
        }

        return response()->json($response);
    }

    public function logout(Request $request) {
        $response = ["status" => 1, "msg" => ""];

        try {
            $user = $request->user;

            $user->api_token = NULL;
            $user->save();

            $response['msg'] = "Usuario Deslogeado";
            $response['status'] = 1;
        } catch (\Exception $e) {
            $response['msg'] = (env('APP_DEBUG') == "true" ? $e->getMessage() : $this->error);
            $response['status'] = 0;
        }

        return response()->json($response);
    }

    /**
     * Check If User Want Modify Data
     * 
     * @param object $data
     * @param object $user
     * @return object $user
     */
    private function checkModifyData($data, $user)
    {
        if (isset($data->name))
            $user->name = $data->name;

        if (isset($data->email))
            $user->email = $data->email;

        if (isset($data->workplace))
            $user->workplace = $data->workplace;

        if (isset($data->salary))
            $user->salary = $data->salary;

        if (isset($data->biography))
            $user->biography = $data->biography;

        return $user;
    }

    /**
     * Employees List Query Response
     * 
     * @param array $users
     * @return array $result_query
     */
    private function employee_list_response($users)
    {
        $result_query = [];

        foreach ($users as $user) {
            $query_response['id'] = $user->id;
            $query_response['name'] = $user->name;
            $query_response['workplace'] = $user->workplace;
            $query_response['salary'] = $user->salary;

            $result_query[] = $query_response;
        }

        return $result_query;
    }

    /**
     * Employee Detail Query Response
     * 
     * @param object $user
     * @return array $query_response
     */
    private function employee_detail_response($user)
    {
        $query_response = [];

        $query_response['id'] = $user->id;
        $query_response['name'] = $user->name;
        $query_response['email'] = $user->email;
        $query_response['workplace'] = $user->workplace;
        $query_response['biography'] = $user->biography;
        $query_response['salary'] = $user->salary;

        return $query_response;
    }

    /**
     * Generate Random Password
     * 
     * @param string $char
     * @param int $length
     * @return string $combinationRandom
     */
    function randomPassword($char, $length)
    {
        $combinationRandom = "";

        for ($i = 0; $i < $length; $i++) {
            $combinationRandom .= substr(str_shuffle($char), 0, $length);
        }

        return $combinationRandom;
    }
}
