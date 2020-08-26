<?php

namespace App\Http\Controllers;

use App\Banner;
use App\Cart;
use App\Category;
use App\Image;
use App\PurchaseRequire;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request){
        $response =[];
        $ln=$request['ln'];
        if(empty($ln) || $ln ==''){
            return response()->json(['status' => false, 'responseMessage' => "Ln field is required"]);
        }
        $banners=Banner::all();
        foreach ($banners as $banner){
            if(isset($banner->title)){
                $titles = json_decode($banner->title);
                $title=$titles->$ln;
                unset($banner->title);
                $banner->title= $title;
            }
            if(isset($banner->link_label)){
                $link_labels = json_decode($banner->link_label);
                $link_label=$link_labels->$ln;
                unset($banner->link_label);
                $banner->link_label= $link_label;
            }
            if(isset($banner->description)){
                $descriptions = json_decode($banner->description);
                $description=$descriptions->$ln;
                unset($banner->description);
                $banner->description= $description;
            }
            $b_image = Image::where(['imageable_id'=>$banner->id,'imageable_type'=>'App\Slider'])->first();
            if($b_image){
                $banner->image=$b_image->path;
            }else{
                $banner->image=null;
            }


        }

        $categories=Category::where('active',1)->get();
        foreach ($categories as $category) {
            if (json_decode($category->name , true )) {
                if (isset($category->name)) {
                    $names = json_decode($category->name);
                    $name = $names->$ln;
                    unset($category->name);
                    $category->name = $name;
                }
            }

            if (json_decode($category->description , true )) {
                if (isset($category->description)) {
                    $descriptions = json_decode($category->description);
                    $description = $descriptions->$ln;
                    unset($category->description);
                    $category->description = $description;
                }
            }


            $c_image = Image::where(['imageable_id'=>$category->id,'imageable_type'=>'App\Category'])->first();
            if($c_image){
                $category->image=$c_image->path;
            }else{
                $category->image=null;
            }
        }
        $response = array(
            'banner' => $banners,
            'categories' => $categories,
        );
        return response()->json(['status' => true, 'responseMessage' => "Successfully", "responseData" => $response]);
    }
}
