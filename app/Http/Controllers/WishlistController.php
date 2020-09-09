<?php

namespace App\Http\Controllers;

use App\Image;
use App\Inventory;
use App\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function addToWishlist(Request $request)
    {
        $response =[];
        $ln=$request['ln'];
        $product_id = $request['product_id'];
        $user_id = $request['user_id'];
        if(empty($ln) || $ln ==''){
            return response()->json(['status' => false, 'responseMessage' => "Ln field is required"]);
        }else if (empty($product_id) || $product_id == '') {
            return response()->json(['status' => false, 'responseMessage' => "Product id field is required"]);
        }else if(empty($user_id) || $user_id == '') {
            return response()->json(['status' => false, 'responseMessage' => "User id field is required"]);
        }
        $product = Inventory::find($product_id);
        if(!$product){
            return response()->json(['status' => false, 'responseMessage' => "Product does not exists"]);
        }
          if(!Wishlist::where(['customer_id'=>$user_id,'inventory_id'=>$product->id])->exists()){
              Wishlist::create([
                  'inventory_id'=>$product->id,
                  'product_id'=>$product->product_id,
                  'customer_id'=>$user_id,
              ]);
          }else{
              return response()->json(['status' => true, 'responseMessage' => "This product already exist in your wishlist"]);
          }
        return response()->json(['status' => true, 'responseMessage' => "Add to wishlist Successfully"]);

    }

    public function getWishlist(Request $request)
    {
        $response =[];
        $ln=$request['ln'];
        $products = [];
        $product_id = $request['product_id'];
        $user_id = $request['user_id'];
        if(empty($ln) || $ln ==''){
            return response()->json(['status' => false, 'responseMessage' => "Ln field is required"]);
        }else if(empty($user_id) || $user_id == '') {
            return response()->json(['status' => false, 'responseMessage' => "User id field is required"]);
        }
        $wishlists =Wishlist::where('customer_id',$user_id)->get();
        foreach ($wishlists as $wishlist) {
            $product = Inventory::where('id',$wishlist->inventory_id)->first();
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
            'wishlist_products' => $products,
        );
        return response()->json(['status' => true, 'responseMessage' => "Successfully", "responseData" => $response]);
    }
}
