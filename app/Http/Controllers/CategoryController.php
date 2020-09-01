<?php

namespace App\Http\Controllers;

use App\Banner;
use App\Cart;
use App\Category;
use App\CategoryGroup;
use App\CategorySubGroup;
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
                $banner->title= ($title == null) ? "" : $title;
            }
            if(isset($banner->link_label)){
                $link_labels = json_decode($banner->link_label);
                $link_label=$link_labels->$ln;
                unset($banner->link_label);
                $banner->link_label= ($link_label == null) ? "" : $link_label;
            }
            if(isset($banner->description)){
                $descriptions = json_decode($banner->description);
                $description=$descriptions->$ln;
                unset($banner->description);
                $banner->description= ($description == null) ? "" : $description;
            }
            $b_image = Image::where(['imageable_id'=>$banner->id,'imageable_type'=>'App\Slider'])->first();
            if($b_image){
                $banner->image=$b_image->path;
            }else{
                $banner->image="";
            }
        }

        $categories=CategoryGroup::where('active',1)->get()->take(6);
        foreach ($categories as $category) {
            if(isset($category->description) || $category->description == null){
                if (json_decode($category->description , true )) {
                    $descriptions = json_decode($category->description);
                    $description = $descriptions->$ln;
                    unset($category->description);
                    $category->description = ($description == null) ? "" : $description;
                }else{
                    if(isset($category->description) || $category->description == null){
                        $category->description =   ($category->description == null) ? "" : $category->description;
                    }
                }
            }
            if (json_decode($category->name , true )) {
                if (isset($category->name)) {
                    $names = json_decode($category->name);
                    $name = $names->$ln;
                    unset($category->name);
                    $category->name = ($name == null) ? "" : $name;
                }
            }
            if(isset($category->featured) || $category->featured == null){
                $category->featured =   ($category->featured == null) ? "" : $category->featured;
            }


            $c_image = Image::where(['imageable_id'=>$category->id,'imageable_type'=>'App\CategoryGroup'])->first();
            if($c_image){
                $category->image=$c_image->path;
            }else{
                $category->image="";
            }
        }
        $response = array(
            'banner' => $banners,
            'categories' => $categories,
        );
        return response()->json(['status' => true, 'responseMessage' => "Successfully", "responseData" => $response]);
    }
}
