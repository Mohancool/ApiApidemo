<?php

namespace App\Http\Controllers;

use App\CouponCustomer;
use App\Inventory;
use App\Order;
use App\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request){
        $response =[];
        $orders= [];
        $ln=$request['ln'];
        $user_id = $request['user_id'];
        if(empty($ln) || $ln ==''){
            return response()->json(['status' => false, 'responseMessage' => "Ln field is required"]);
        }elseif (empty($user_id) || $user_id == ''){
            return response()->json(['status' => false, 'responseMessage' => "User id field is required"]);
        }
        $ordersData = Order::where('customer_id',$user_id)->get();
        foreach ($ordersData as $order){
            $orderItems = OrderItem::where('order_id',$order->id)->get();
            //dd($orderItems);
            foreach ($orderItems as $item){
                $product = Inventory::where('id',$item->inventory_id)->first();
                if (json_decode($product->title , true )) {
                    if (isset($product->title)) {
                        $names = json_decode($product->title);
                        $name = $names->$ln;
                        unset($product->title);
                        $product->title = ($name == null) ? "" : $name;
                    }
                }
                $makeorder = array(
                    'order_id' => $item->order_id,
                    'inventory_id'=>$item->inventory_id,
                    'product_name'=>$product->title,
                    'quantity'=>$item->quantity,
                    'order_price'=>$item->price,
                    'purchase_price'=>$product->purchase_price,
                    'sale_price' => $product->sale_price,
                    'discount' =>($order->discount == null) ? "" : $order->discount,
                    'order_status'=>1,
                );
                $orders[]= $makeorder;
            }
        }

        $response = array(
            'orders' => $orders,
        );
        return response()->json(['status' => true, 'responseMessage' => "Successfully", "responseData" => $response]);
    }
}
