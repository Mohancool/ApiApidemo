<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Auth;

class UserController extends Controller
{

    protected function register(Request $request)
    {
        $name= $request->input('name');
        $email= $request->input('email');
        $password= $request->input('password');

        if(empty($name) && empty($email) && empty($password)){
            return response()->json(['status' => false, 'responseMessage' => "Name,email and password fields are required"]);
        }else if($name == '' || empty($name)){
            return response()->json(['status' => false, 'responseMessage' => "Name field is required"]);
        }else if($email== '' || empty($email)){
            return response()->json(['status' => false, 'responseMessage' => "Email field is required"]);
        } else if($password=='' || empty($password)) {
            return response()->json(['status' => false, 'responseMessage' => "Password field is required"]);
        }

        $validator = Validator::make($request->all(), [
            'password' => 'min:6',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'responseMessage' => "Password must contain at least six character"]);
        }

        if (User::where('email', $request->email)->exists()) {
            return response()->json(['responseMessage' => 'Email has been taken already', 'status' => false]);
        }
            $user = User::create([
                'role_id' => 3,
                'name' => $request['name'],
                'email' => $request['email'],
                'mobile' => $request['mobile'],
                'password' => bcrypt($request['password']),
                'active' => 1,
            ]);

        return response()->json(['status'=>true,'responseMessage'=>"Successfully Register"]);

    }

    public function login(Request $request)
    {
        $ln = $request->input('ln');
        $credentials = $request->only('email', 'password');
        if (empty($credentials)) {
            return response()->json(['status' => false, 'responseMessage' => "Email and Password fields are required"]);
        }elseif (empty($ln) || $ln == ''){
            return response()->json(['status' => false, 'responseMessage' => "Ln field is required"]);
        }
        $userdata = User::where(['email' => $request->email, 'active' => 1])->first();
        if (Auth::attempt($credentials)) {
         if(json_decode($userdata->name , true ) && $ln != ''){
             $lns =json_decode($userdata->name);
             $desc =json_decode($userdata->description);
             $name = $lns->$ln;
             $description = $desc->$ln;
             $user = array(
                 "id" => $userdata->id,
                 "shop_id" => $userdata->shop_id,
                 "role_id" => $userdata->role_id,
                 "name" => $name,
                 "email" => $userdata->email,
                 "mobile" => $userdata->contact,
                 "dob" => $userdata->dob,
                 "sex" => $userdata->sex,
                 "description"=>$description,
                 "active" => $userdata->active,
                 "ln"=>$ln
             );
         }else{
             $user = array(
                 "id" => $userdata->id,
                 "shop_id" => $userdata->shop_id,
                 "role_id" => $userdata->role_id,
                 "name" => $userdata->name,
                 "email" => $userdata->email,
                 "mobile" => $userdata->contact,
                 "dob" => $userdata->dob,
                 "sex" => $userdata->sex,
                 "description"=>$userdata->description,
                 "active" => $userdata->active,
                 "ln"=>"en"
             );
         }

            return response()->json(['status' => true, 'responseMessage' => "Successfully Login", "responseData" => $user]);
        }
        return response()->json(['status' => false, 'responseMessage' => "Please provide valid login credentials."]);
    }

}
