<?php

namespace App\Http\Controllers;

use App\CouponCustomer;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request){
        $response =[];
        $coupons= [];
        $ln=$request['ln'];
        $user_id = $request['user_id'];
        if(empty($ln) || $ln ==''){
            return response()->json(['status' => false, 'responseMessage' => "Ln field is required"]);
        }elseif (empty($user_id) || $user_id == ''){
            return response()->json(['status' => false, 'responseMessage' => "User id field is required"]);
        }

        $userCoupans = CouponCustomer::where('customer_id',$user_id)->with('coupon')->get();
        foreach ($userCoupans as $coupan){
            try {
                $coupons[] = $coupan->coupon;
            }catch (\ErrorException $e){
                continue;
            }
        }
        $response = array(
            'coupons' => $coupons,
        );
        return response()->json(['status' => true, 'responseMessage' => "Successfully", "responseData" => $response]);
    }
}
