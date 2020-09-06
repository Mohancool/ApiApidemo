<?php

namespace App\Http\Controllers;

use App\Cart;
use App\CartItem;
use App\Image;
use App\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function addToCart(Request $request)
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
        $cart = Cart::where(['customer_id' => $user_id, 'payment_status' => 1])->orderby('id', 'desc')->take(1)->first();
        if ($cart) {
            $cart_id = $cart->id;
        } else {
            $cart = Cart::create([
                'customer_id' => $user_id,
                'item_count' => 0,
                'quantity' => 0
            ]);
            $cart_id = $cart->id;
        }
            if (CartItem::where(['cart_id' => $cart_id, 'inventory_id' => $product_id])->exists()) {
                $cartItem = CartItem::where(['cart_id' => $cart_id, 'inventory_id' => $product_id])->first();
                $quantity = $cartItem->quantity + 1;
                $price = $cartItem->unit_price + $product->sale_price;
                CartItem::where(['cart_id' => $cart_id, 'inventory_id' => $product_id])->update([
                    'unit_price' => $price,
                    'quantity' => $quantity,
                ]);
            } else {
                $quantity = 1;
                $price = $product->sale_price;
                if(isset($product->description) || $product->description == null){
                    if (json_decode($product->description , true )) {
                        $descriptions = json_decode($product->description);
                        $description = ($descriptions->$ln == null) ? "" : $descriptions->$ln;
                    }else{
                        if(isset($product->description) || $product->description == null){
                            $description =   ($product->description == null) ? "" : $product->description;
                        }
                    }
                }
                CartItem::create([
                    'cart_id'=>$cart_id,
                    'inventory_id'=>$product->id,
                    'item_description'=>$description,
                    'quantity'=>$quantity,
                    'unit_price'=>$price,
                    'pbulk'=>0
                ]);
            }
            $totalcart = CartItem::where('cart_id', $cart_id)->get();
            if (count($totalcart) > 0) {
                $total_cart = count($totalcart);
            } else {
                $total_cart = 0;
            }
            return response()->json(['status' => true, 'responseMessage' => "Cart Successfully","total_item"=>$total_cart]);

        }

        public function removeCart(Request $request){
            $ln=$request['ln'];
            if(empty($ln) || $ln ==''){
                return response()->json(['status' => false, 'responseMessage' => "Ln field is required"]);
            }
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'action' => 'required',
                'product_id'=>'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'responseMessage' => "User id and action field are required"]);
            }
            $user_id = $request['user_id'];
            $action = $request['action'];
            $product_id = $request['product_id'];
            $product = Inventory::find($product_id);
            if(!$product){
                return response()->json(['status' => false, 'responseMessage' => "Product does not exists"]);
            }

            switch ($action) {
                case 'remove-single':
                    $cart = Cart::where(['customer_id' => $user_id, 'payment_status' => 1])->orderby('id', 'desc')->take(1)->first();
                    if ($cart) {
                        $cart_id = $cart->id;
                    } else {
                        return response()->json(['status' => false, 'responseMessage' => "You does not have an active cart"]);
                    }
                    if (CartItem::where(['cart_id' => $cart_id, 'inventory_id' => $product_id])->exists()) {
                        $cartItem = CartItem::where(['cart_id' => $cart_id, 'inventory_id' => $product_id])->first();
                        $quantity = $cartItem->quantity - 1;
                        if($quantity ==1){
                            return response()->json(['status' => true, 'responseMessage' => "For buy this item 1 quantity required ",'item_count'=>$quantity]);
                        }
                        $price = $cartItem->unit_price - $product->sale_price;
                        CartItem::where(['cart_id' => $cart_id, 'inventory_id' => $product_id])->update([
                            'unit_price' => $price,
                            'quantity' => $quantity,
                        ]);


                        return response()->json(['status' => true, 'responseMessage' => "Successfully removed",'item_count'=>$quantity]);
                    }
                    break;


              case 'remove':
                  $cart = Cart::where(['customer_id' => $user_id, 'payment_status' => 1])->orderby('id', 'desc')->take(1)->first();
                  if ($cart) {
                      $cart_id = $cart->id;
                  } else {
                      return response()->json(['status' => false, 'responseMessage' => "You does not have an active cart"]);
                  }
                  if (CartItem::where(['cart_id' => $cart_id, 'inventory_id' => $product_id])->exists()) {
                      CartItem::where(['cart_id' => $cart_id, 'inventory_id' => $product_id])->delete();

                      $totalcart = CartItem::where('cart_id', $cart_id)->get();
                      if (count($totalcart) > 0) {
                          $total_cart = count($totalcart);
                      } else {
                          $total_cart = 0;
                      }
                      return response()->json(['status' => true, 'responseMessage' => "Successfully removed this item from your cart","total_item"=>$total_cart]);
                  }
                    break;
            }

        }

        public function cartList(Request $request){
            $ln=$request['ln'];
            $products = [];
            if(empty($ln) || $ln ==''){
                return response()->json(['status' => false, 'responseMessage' => "Ln field is required"]);
            }
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'responseMessage' => "User id field is required"]);
            }
            $user_id = $request['user_id'];

            $cart = Cart::where(['customer_id' => $user_id, 'payment_status' => 1])->orderby('id', 'desc')->take(1)->first();
            if ($cart) {
                $cart_id = $cart->id;
            } else {
                return response()->json(['status' => false, 'responseMessage' => "You does not have an active cart"]);
            }

            $data = CartItem::where('cart_id',$cart_id)->get();
            foreach ($data as $item){
                $product = Inventory::where('id',$item->inventory_id)->first();
                if(!$product){
                    continue;
                }
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
                $product->cart_id = $cart_id;
                $product->quantity = $item->quantity;
                $products[]= $product;
            }
            $response = array(
                'cart_products' => $products,
            );
            if(count($products)>0){
                return response()->json(['status' => true, 'responseMessage' =>  'Found '. count($products).' products', "responseData" => $response]);
            }else{
                return response()->json(['status' => true, 'responseMessage' => "Not found product in your cart", "responseData" => $response]);
            }
        }
}
