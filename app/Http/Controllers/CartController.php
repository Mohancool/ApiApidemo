<?php

namespace App\Http\Controllers;

use App\Cart;
use App\CartItem;
use App\Inventory;
use Illuminate\Http\Request;

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
            $cart = CartItem::where(['cart_id' => $cart_id])->get();
            if (count($cart) > 0) {
                $total_cart = count($cart);
            } else {
                $total_cart = 0;
            }
            return response()->json(['status' => true, 'responseMessage' => "Cart Successfully","total_item"=>$total_cart]);

        }
}
