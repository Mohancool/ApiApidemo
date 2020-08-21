<?php

namespace App\Http\Controllers;

use App\Banner;
use App\Cart;
use App\Category;
use App\PurchaseRequire;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request){
        $response =[];
        $banners=Banner::all();
        $category=Category::all();

        $response = array(
            'banner' => $banners,
            'categories' => $category,
        );
        return response()->json(['message'=>"Successfully","purchase_msg"=>$purchase->message,'total_cart'=>$cart_count ,'status_code'=>200,'data'=>$response]);
    }
}
