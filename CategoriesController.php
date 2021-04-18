<?php

namespace App\Http\Controllers\API;



use Illuminate\Http\Request; 

use App\Http\Controllers\Controller; 

use App\Categories; 
use App\Product_Images;

use Hash;

use Validator;

use DB;

use Artisan;

use Redirect;
use App\Product;
class CategoriesController extends Controller 


{

public $successStatus = 200;



    public function fetch_category() 
    {
           $categories = Categories::where('status','=','Active')->get();
         
            $success['details'] =  $categories;
        return response()->json(['response_api'=>$success], $this-> successStatus); 
            

    } //gamer
    
    public function fetch_categories($id)
    {
        $categories = Categories::where('id', '=', $id)->get();
          $new_category = [];

          foreach ($categories as $cat) {
              $path = url('public/uploads/Categories')."/".$cat->image;
            $array_insert =  array(
                'name' =>  $cat->name, 
                'image' =>  $path, 
                'category_id' =>  $cat->id
                );

                $new_category []  =  $array_insert;
          }

          $success['status'] =  "success";
          $success['data'] =  $new_category;
      return response()->json(['response_api'=>$success], $this-> successStatus); 
    }
    
        public function fetch_categories_products($id)
    {
        $product = Product::where('category_id', '=', $id)->get();
        $new_category = [];
        $categoryy = Categories::where('id', '=', $id)->get();

        foreach ($product as $products) {
            $imagess = Product_Images::where('product_id', '=', $products->id)->get();
            $insert_images = [];
            foreach ($imagess as $images) {
                $pathss = url('public/uploads') . "/" . $images->image;
                // echo $images->image;
                $image_insert =  array(
                    'name' =>  $pathss,
                );
                $insert_images[] = $image_insert;
            }
            $path = url('public/uploads') . "/" . $products->image;
            $array_insert =  array(
                'provider_id' => $products->provider_id,
                'description' =>  $products->description,
                'price' => $products->normal_price,
                'discount' => $products->discount,
                'name' =>  $products->name,
                'main_image' => $path,
                'image' =>  $insert_images,
                'category_id' =>  $products->id,
                'discount_quantity' => $products->discount_quantity,
                'category_name' => $categoryy[0]->name,
            );

            $new_category[]  =  $array_insert;
        }

        $success['status'] =  "success";
        $success['data'] =  $new_category;
        return response()->json(['response_api' => $success], $this->successStatus);
    }
   
    







    

}

