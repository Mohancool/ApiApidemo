<?php

namespace App\Http\Controllers;

use App\Mail\EmailVerification;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
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

        if (User::where('email', $request->email)->exists() || User::onlyTrashed()->where('email',$request->email)->exists()) {
            return response()->json(['responseMessage' => 'Email has been taken already', 'status' => false]);
        }
        $ctime = round(microtime(true)*1000);
        //expire time after 15 min
        $expire_at = $ctime + 900000;

        $digits = 6;
        $otp = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
            $user = User::create([
                'role_id' => 3,
                'name' => $request['name'],
                'email' => $request['email'],
                'contact' => $request['mobile'],
                'password' => bcrypt($request['password']),
                'active' => 0,
                'otp' => $otp,
                'otp_expire_at'=>$expire_at
            ]);
        //Mail::to($request->email)->send(new EmailVerification($user));

        return response()->json(['status'=>true,'responseMessage'=>"Your otp send on your mail successfully.",'otp'=>$otp]);

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
        if (!User::where('email',$request->email)->exists() || User::onlyTrashed()->where('email',$request->email)->exists()) {
            return response()->json(['error' => 'This email does not exists', 'status_code' => 204]);
        }
        if(User::where(['email' => $request->email, 'active' => 0])->exists()){
            return response()->json(['status' => false, 'responseMessage' => "Your email is not verified"]);
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
                 "shop_id" => ($userdata->shop_id == null) ? "" : $userdata->shop_id,
                 "role_id" => ($userdata->role_id == null) ? "" : $userdata->role_id,
                 "name" => ($name == null) ? "" : $name,
                 "email" => ($userdata->email == null) ? "" : $userdata->email,
                 "mobile" => ($userdata->contact == null) ? "" : $userdata->contact,
                 "dob" => ($userdata->dob == null) ? "" : $userdata->dob,
                 "sex" => ($userdata->sex == null) ? "" : $userdata->sex,
                 "description"=>($description == null) ? "" : $description,
                 "active" => $userdata->active,
                 "ln"=>$ln
             );
         }else{
             $user = array(
                 "id" => $userdata->id,
                 "shop_id" => ($userdata->shop_id == null) ? "" : $userdata->shop_id,
                 "role_id" => ($userdata->role_id == null) ? "" : $userdata->role_id,
                 "name" => ($userdata->name == null) ? "" : $userdata->name,
                 "email" => ($userdata->email == null) ? "" : $userdata->email,
                 "mobile" => ($userdata->contact == null) ? "" : $userdata->contact,
                 "dob" => ($userdata->dob == null) ? "" : $userdata->dob,
                 "sex" => ($userdata->sex == null) ? "" : $userdata->sex,
                 "description"=>($userdata->description == null) ? "" : $userdata->description,
                 "active" => $userdata->active,
                 "ln"=>"en"
             );
         }

            return response()->json(['status' => true, 'responseMessage' => "Successfully Login", "responseData" => $user]);
        }
        return response()->json(['status' => false, 'responseMessage' => "Please provide valid login credentials."]);
    }

    public function verifyOtp(Request $request)
    {
        $ln = $request->input('ln');
        if (empty($ln) || $ln == ''){
            return response()->json(['status' => false, 'responseMessage' => "Ln field is required"]);
        }
        $validator = \Validator::make($request->all(), [
            'otp' => 'required|min:6',
            'email' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'responseMessage' => "Otp and email fields are required"]);
        }
        $email = $request->input('email');
        $ctime = round(microtime(true)*1000);
        if ($email != '' || $email != null)
        {
            if (!User::where(['email' => $request->email, 'otp' => $request->otp])->exists()) {
                return response()->json(['status' => false, 'responseMessage' => "This otp is invalid"]);
            }
            $udata = User::where(['email' => $request->email, 'otp' => $request->otp])->first();
            if ($ctime > $udata->otp_expire_at) {
                return response()->json(['status' => false, 'responseMessage' => "This otp is expired"]);
            }
            User::where('email', $request->email)->update(['email_verified_at'=>Carbon::now(),'active' => 1, 'otp' => 0,'otp_expire_at'=>0]);
            $userdata = User::where(['email' => $request->email, 'active' => 1])->first();
        }

        if(json_decode($userdata->name , true ) && $ln != ''){
            $lns =json_decode($userdata->name);
            $desc =json_decode($userdata->description);
            $name = $lns->$ln;
            $description = $desc->$ln;
            $user = array(
                "id" => $userdata->id,
                "shop_id" => ($userdata->shop_id == null) ? "" : $userdata->shop_id,
                "role_id" => ($userdata->role_id == null) ? "" : $userdata->role_id,
                "name" => ($name == null) ? "" : $name,
                "email" => ($userdata->email == null) ? "" : $userdata->email,
                "mobile" => ($userdata->contact == null) ? "" : $userdata->contact,
                "dob" => ($userdata->dob == null) ? "" : $userdata->dob,
                "sex" => ($userdata->sex == null) ? "" : $userdata->sex,
                "description"=>($description == null) ? "" : $description,
                "active" => $userdata->active,
                "ln"=>$ln
            );
        }else{
            $user = array(
                "id" => $userdata->id,
                "shop_id" => ($userdata->shop_id == null) ? "" : $userdata->shop_id,
                "role_id" => ($userdata->role_id == null) ? "" : $userdata->role_id,
                "name" => ($userdata->name == null) ? "" : $userdata->name,
                "email" => ($userdata->email == null) ? "" : $userdata->email,
                "mobile" => ($userdata->contact == null) ? "" : $userdata->contact,
                "dob" => ($userdata->dob == null) ? "" : $userdata->dob,
                "sex" => ($userdata->sex == null) ? "" : $userdata->sex,
                "description"=>($userdata->description == null) ? "" : $userdata->description,
                "active" => $userdata->active,
                "ln"=>"en"
            );
        }
        return response()->json(['status' => true, 'responseMessage' => "Successfully Login", "responseData" => $user]);
    }

    public function updateProfile(Request $request){
        $validator = \Validator::make($request->all(), [
            'user_id'=> 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'mobile' => 'required',
            'gender' => 'required',
            'email'=>'required',
            "dob" => 'required',
            "location"=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'responseMessage' => "All fields are required"]);
        }
        $validator = \Validator::make($request->all(), [
            'email'=>'unique:users,email,'.$request->user_id,
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'responseMessage' => "This email already taken by another user"]);
        }
       if(!User::where(['id'=>$request->user_id,'active'=>1])){
           return response()->json(['status' => false, 'responseMessage' => "User does not exists."]);
       }

       if(!empty($request->password) || $request->password != ''){
           $user = User::where(['id'=>$request->user_id,'active'=>1])->update([
               'name' => $request->first_name.''. $request->last_name,
               'email' => $request->email,
               'contact' => $request->mobile,
               'password' => bcrypt($request['password']),
               "sex"=> $request->gender,
               "dob"=> $request->dob,
               "description" => $request->location,
           ]);
       }else{
           $user = User::where(['id'=>$request->user_id,'active'=>1])->update([
               'name' => $request->first_name.''. $request->last_name,
               'email' => $request->email,
               'contact' => $request->mobile,
               "sex"=> $request->gender,
               "dob"=> $request->dob,
               "description" => $request->location,
           ]);
       }
        return response()->json(['status' => false, 'responseMessage' => "Successfully Updated"]);
    }

}
