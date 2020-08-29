<?php

namespace App\Http\Controllers;

use App\Image;
use App\Inventory;
use App\Wishlist;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function recommendedData(Request $request){
        $response =[];
        $products =[];
        $ln=$request['ln'];
        $user_id = $request['user_id'];
        if(empty($ln) || $ln ==''){
            return response()->json(['status' => false, 'responseMessage' => "Ln field is required"]);
        }elseif (empty($user_id) || $user_id == ''){
            return response()->json(['status' => false, 'responseMessage' => "User id field is required"]);
        }
        $recommends=Wishlist::where(['customer_id'=>$user_id])->get();
        foreach ($recommends as $recommend) {
            $product = Inventory::where('id',$recommend->inventory_id)->first();
            if(isset($product->description) || $product->description == null){
                if (json_decode($product->description , true )) {
                    $descriptions = json_decode($product->description);
                    $description = $descriptions->$ln;
                    unset($product->description);
                    $product->description = ($description == null) ? "" : $description;
                }else{
                    if(isset($product->description) || $product->description == null){
                        $product->description =   ($product->description == null) ? "" : $product->description;
                    }
                }
            }
            if (json_decode($product->title , true )) {
                if (isset($product->title)) {
                    $names = json_decode($product->title);
                    $name = $names->$ln;
                    unset($product->title);
                    $product->title = ($name == null) ? "" : $name;
                }
            }
                if (isset($product->meta_title)) {
                    $product->title = ($name == null) ? "" : $name;
                }

            if(isset($product->meta_title) || $product->meta_title == null){
                $product->meta_title =   ($product->meta_title == null) ? "" : $product->meta_title;
            }
            if(isset($product->meta_description) || $product->meta_description == null){
                $product->meta_description =   ($product->meta_description == null) ? "" : $product->meta_description;
            }
            if(isset($product->price_status) || $product->price_status == null){
                $product->price_status =   ($product->price_status == null) ? "" : $product->price_status;
            }
            if(isset($product->bulk_price) || $product->bulk_price == null){
                $product->bulk_price =   ($product->bulk_price == null) ? "" : $product->bulk_price;
            }
            if(isset($product->free_shipping) || $product->free_shipping == null){
                $product->free_shipping =   ($product->free_shipping == null) ? "" : $product->free_shipping;
            }

            $c_image = Image::where(['imageable_id'=>$product->id,'imageable_type'=>'App\Inventory'])->first();
            if($c_image){
                $product->image=$c_image->path;
            }else{
                $product->image="";
            }
            unset($product->condition_note);
            unset($product->damaged_quantity);
            unset($product->stuff_pick);
            $products[]= $product;
        }
        $response = array(
            'recommend_products' => $products,
        );
        return response()->json(['status' => true, 'responseMessage' => "Successfully", "responseData" => $response]);
    }

    public function additionalItems(Request $request){
        $response =[];
        $products = [];
        $ln=$request['ln'];
        if(empty($ln) || $ln ==''){
            return response()->json(['status' => false, 'responseMessage' => "Ln field is required"]);
        }
        $products = Inventory::where('active',1)->orderByDesc('id')->take(6)->get();
        foreach ($products as $product) {
            if(isset($product->description) || $product->description == null){
                if (json_decode($product->description , true )) {
                    $descriptions = json_decode($product->description);
                    $description = $descriptions->$ln;
                    unset($product->description);
                    $product->description = ($description == null) ? "" : $description;
                }else{
                    if(isset($product->description) || $product->description == null){
                        $product->description =   ($product->description == null) ? "" : $product->description;
                    }
                }
            }
            if (json_decode($product->title , true )) {
                if (isset($product->title)) {
                    $names = json_decode($product->title);
                    $name = $names->$ln;
                    unset($product->title);
                    $product->title = ($name == null) ? "" : $name;
                }
            }
            if (isset($product->meta_title)) {
                $product->title = ($name == null) ? "" : $name;
            }

            if(isset($product->meta_title) || $product->meta_title == null){
                $product->meta_title =   ($product->meta_title == null) ? "" : $product->meta_title;
            }
            if(isset($product->meta_description) || $product->meta_description == null){
                $product->meta_description =   ($product->meta_description == null) ? "" : $product->meta_description;
            }
            if(isset($product->price_status) || $product->price_status == null){
                $product->price_status =   ($product->price_status == null) ? "" : $product->price_status;
            }
            if(isset($product->bulk_price) || $product->bulk_price == null){
                $product->bulk_price =   ($product->bulk_price == null) ? "" : $product->bulk_price;
            }
            if(isset($product->free_shipping) || $product->free_shipping == null){
                $product->free_shipping =   ($product->free_shipping == null) ? "" : $product->free_shipping;
            }

            $c_image = Image::where(['imageable_id'=>$product->id,'imageable_type'=>'App\Inventory'])->first();
            if($c_image){
                $product->image=$c_image->path;
            }else{
                $product->image="";
            }
            unset($product->condition_note);
            unset($product->damaged_quantity);
            unset($product->stuff_pick);
            $products[]= $product;
        }
        $response = array(
            'additional_products' => $products,
        );
        return response()->json(['status' => true, 'responseMessage' => "Successfully", "responseData" => $response]);
    }
}
