<?php

namespace App\Http\Controllers;

use App\Category;
use App\CategorySubGroup;
use App\Image;
use Illuminate\Http\Request;

class ChildCategoryController extends Controller
{
    public function index(Request $request){
        $response =[];
        $ln=$request['ln'];
        $category_id = $request['category_sub_group_id'];
        if(empty($ln) || $ln ==''){
            return response()->json(['status' => false, 'responseMessage' => "Ln field is required"]);
        }elseif (empty($category_id) || $category_id == ''){
            return response()->json(['status' => false, 'responseMessage' => "Category sub group id field is required"]);
        }
        $categories=Category::where(['category_sub_group_id'=>$category_id,'active'=>1])->get();
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

            $c_image = Image::where(['imageable_id'=>$category->id,'imageable_type'=>'App\Category'])->first();
            if($c_image){
                $category->image=$c_image->path;
            }else{
                $category->image="";
            }
        }
        $response = array(
            'childcategories' => $categories,
        );
        return response()->json(['status' => true, 'responseMessage' => "Successfully", "responseData" => $response]);
    }
}
