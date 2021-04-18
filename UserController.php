<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request; 

use App\Http\Controllers\Controller; 


use App\Provider_Model; 
use App\Product_Images;
use App\Product;
use App\Group_product;
use App\User; 
use App\Wallet; 
use App\User_address;
use App\Driver;
use App\Categories;
use App\Coupons;
use App\Deals;
use App\Deal_items;
use App\Reviews;
use App\Order;
use Hash;
use DB;
use DateTime;
use GuzzleHttp;

// use Auth;

use Illuminate\Support\Facades\Auth; 

use Validator;

class UserController extends Controller 

{

public $successStatus = 200;

/** 

     * login api 

     * 

     * @return \Illuminate\Http\Response 

     */ 

    public function login(){ 
        $otp = rand(100000,999999);
        $payload = json_decode(request()->getContent(), true);
        $data = $payload;
        
        $email =  $data['email'];
        $password =  $data['password'];
        $device_id =  $data['device_id'];
        
        
        if(Auth::attempt(['email' => $email, 'password' => $password, 'status' => 'Active'])){
            
            $user_id = Auth::user()->id;
            
            $array_insert =  array(
                'device_id' =>  $device_id,                        
            );
            
            $user = User::find($user_id);    
            $user->update($array_insert);
            
            $user = Auth::user();
            $success['data'] = $user;
            $success['categories'] = $this->fetch_category();
        
            return response()->json(['response_api'=>$success], $this-> successStatus); 

        }else{ 

            $success['error'] =  "Email or Password Incorrect";
            return response()->json(['response_api'=> $success], 401); 
        } 
    }

/** 

     * Register api 

     * 

     * @return \Illuminate\Http\Response 

     */ 

    public function register(Request $request) 
    { 
        $otp = rand(100000,999999);
        $payload = json_decode(request()->getContent(), true);
        $data = $payload;
        $name =  $data['name'];
        $email =  $data['email'];


        if(isset($data['address'])){
            $address =  $data['address'];            
        }else{
            $address =  "";            
        }

        if(isset($data['country'])){
            $country =  $data['country'];            
        }else{
            $country =  "";            
        }

        if(isset($data['city'])){
            $city =  $data['city'];            
        }else{
            $city =  "";            
        }


        
        $mobile = $data['mobile'];

        $password = bcrypt($data['password']); 
        if(isset($data['image'])){
            $image = $data['image'];
        }else{
            $image = "";
        }

        if(empty($data['name']) || empty($data['email']) ||  empty($data['mobile'])  ){
            $success['error'] = "Please Fill All Fields";
            return response()->json(['response_api'=>$success],401); 
        }else{

// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
 

   
        $headers .= "From: no-reply@toyclash.com" . "\r\n";
        $message = "<html>
        <body>
        <p><strong>Hello ".$name."<br/>Welcome to Toysclash</strong></p>
        
        </body>
        </html>";
    // mail($email,"Toysclash","Please Enter this otp code \n".$otp,$headers);
    
    mail($email,"Toysclash",$message,$headers);

      $array_insert =  array(
                'name' =>  $name, 
                'email' =>  $email, 
                'password' =>  $password,                
                'mobile' => $mobile,
                'address' => $address,
                'country' => $country,
                'otp' =>  $otp,
                'city' => $city,
                'status' => 'Active',
      );

          $user = User::where('email', '=', $data['email'])->first();    
          $mobile = User::where('mobile', '=', $mobile)->first();    
        if(empty($user) && empty($mobile)){
        $user =  User::create($array_insert);

        $success['data'] = $user;
        $success['otp'] = $otp;


    //     $headers = "From: copyrightsdetect@copyrightsdetectives.com" . "\r\n" .
    //     "CC:  qa.ms2018@gmail.com";
    // mail($email,"User Confirmation Hadool","Please Enter this otp code \n".$otp,$headers);


        return response()->json(['response_api'=>$success], $this-> successStatus); 

        }elseif(!empty($mobile)){

            $success['error'] = "Mobile Number already exist";
            return response()->json(['response_api'=>$success],401); 

        }else{

            $success['error'] = "email already exist";
            return response()->json(['response_api'=>$success],401); 

        }

    }  // esle finished 


    }



    public function check_otp(Request $request) 
    { 
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        $status = $input['status'];
        $id = $input['id'];
        if($status=="1"){
            $user_provider = User::find($id);
            $user_provider->status = 'Active';
            $user_provider->update();
            $success['status'] =  "success";
            return response()->json(['response_api'=>$success], $this-> successStatus); 

        }else{
            $success['error'] = "1";
           return response()->json(['response_api'=>$success], 401); 
        }
    }

    public function resend_otp($id)
    { 
        $user_provider = User::find($id);
        $email =   $user_provider->email;
            
        $otp = rand(100000,999999);
        $headers = "From: no-reply@fleetchain.net" . "\r\n";
        mail($email,"User Confirmation","Please Enter this otp code \n".$otp,$headers);
        
        $user_provider->otp = $otp;
        $user_provider->update();        

        $success['status'] =  "success";
        $success['otp'] =  $otp;
        return response()->json(['response_api'=>$success], $this-> successStatus); 
    }

    
    public function update_profile(Request $request) 
    {

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        $user_id =  $input['user_id'];
        $name = $input['name'];
        $phone = $input['mobile'];
        $array_insert =  array(
            'name' =>  $name,                        
            'mobile' =>  $phone,                        
        );

            $user = User::find($user_id);    
            $user->update($array_insert);
            $update_user = User::find($user_id);    

            $success['status'] =  "success";
            $success['details'] =  $update_user;

        return response()->json(['response_api'=>$success], $this-> successStatus); 

    }

   public function get_user_by_mobile($mobile){

    $user_mobile = $mobile;

    $user = User::where('mobile', '=', $user_mobile)->get();  
    
    if(!empty($user_mobile)){

        $success['status'] =  "success";
        $success['data'] =  $user;

    return response()->json(['response_api'=>$success], $this-> successStatus); 

    }else{

        $success['error'] = "No user Found";
        return response()->json(['response_api'=>$success],401);

    }
   
}





    public function fetch_category(){

        $categories = Categories::all();

        $new_category = [];

        foreach ($categories as $cat) {
            $path = url('public/uploads')."/".$cat->image;
          $array_insert =  array(
              'name' =>  $cat->name, 
              'image' =>  $path, 
              'category_id' =>  $cat->id,
              'active_index' => "1"
              );
              $new_category []  =  $array_insert; 
        }

    return $new_category; 
    }
    
    // public function get_all_providers($lat, $lon)
    // {
    //     $new_category = array();
    //     $new_products = array();
    //     $providers = Provider_Model::where('status', '=', 'Active')->get();
    //     if ($providers->isNotEmpty()) {
    //         $markers = collect($providers)->map(function ($item) use ($lat, $lon) {
    //             $item->distance = calculateDistanceBetweenTwoAddresses($item->lat, $item->lng, $lat, $lon);
    //         });
    //     }
    //     $provider_array = [];
    //     $new_products = [];
                
    //     foreach ($providers as $provider) {
    //         if ($provider->distance <= $provider->dis_limit) {
    //             if (!empty($provider->image)) {
    //                 $provider_img = $provider->image;
    //             } else {
    //                 $provider_img = "https://chopal.in/wp-content/uploads/2019/10/image-696x392.jpg";
    //             }
                
    //             // $array_inserts =  array(
    //             //         'provider_id' => $provider->id,
    //             //         'name' =>  $products->name,
    //             //         'description' =>  $products->description,
    //             //         'price' => $products->normal_price,
    //             //         'discount' => $products->discount,
    //             //         'main_image'=>$paths,
    //             //         'image' => $insert_images,
    //             //         'video' => $products->video,
    //             //         'discount_quantity' => $products->discount_quantity,
    //             //         'category_name' => $categories[0]->name,
    //             //     );
    //             //     $new_products[]  =  $array_inserts;

    //             // $groups = Group_product::where('provider_id', '=', $provider->id)->where('status', '=', 'Active')->get();
    //             $current_time = date('h:i a');
    //             $provider_gamer = array(
    //                 'provider_id' =>$provider->id,
    //                 'provider_name' => $provider->name,
    //                 'estimate_time' => $provider->estimate_time,
    //                 'current_status' => $provider->current_status,
    //                 'provider_image' => $provider_img,
    //                 'provider_lat' => $provider->lat,
    //                 'provider_lng' => $provider->lng,               
    //                 'provider_address' => $provider->address,
    //                 'provider_food_type' => $provider->food_type,
    //                 'delivery_charges' => $provider->delivery_charges,
    //                 'min_shop_price' => $provider->min_shop_price,
    //                 'current_server_time' => $current_time,
    //                 'server_time' => $current_time,
    //                 'distance' => $provider->distance,
    //                 'dis_limit' => $provider->dis_limit,
    //                 'category_id' => $provider->category_id,
    //             );
    //             $provider_array[] =  $provider_gamer;

               
    //         }
    //     }
    //     $productss = Product::where('provider_id', '=', '1')->get();
    //     foreach($productss as $products){
            
    //     }
    //     //  $success['data'] = $provider_array;
    //     //  $success['data1'] = $new_category;
    //     $success['data2'] = $provider_array;

    //     // $success['data'] = $insert_images;

    //     return response()->json(['response_api' => $success], $this->successStatus);
    // }
    
    public function get_all_provider_products($lat, $lon)
    {
        $new_category = array();
        $new_products = array();
        $providers = Provider_Model::where('status', '=', 'Active')->get();
        if ($providers->isNotEmpty()) {
            $markers = collect($providers)->map(function ($item) use ($lat, $lon) {
                $item->distance = calculateDistanceBetweenTwoAddresses($item->lat, $item->lng, $lat, $lon);
            });
        }
        $provider_array = [];
        $new_products = [];
                
        foreach ($providers as $provider) {
            if ($provider->distance <= $provider->dis_limit) {
                if (!empty($provider->image)) {
                    $provider_img = $provider->image;
                } else {
                    $provider_img = "https://chopal.in/wp-content/uploads/2019/10/image-696x392.jpg";
                }
                $product = Product::where('provider_id', '=', $provider->id)->where('status', '=', 'Active')->get();
                $categories = Categories::where('id', '=', $provider->category_id)->get();
                $array_inserts = array();
                $insert = array();
                foreach($product as $products){
                    
                }
                
                foreach ($product as $products) {
                   
                    $imagess = Product_Images::where('product_id', '=', $products->id)->get();
                    $insert_images=[];
                    foreach ($imagess as $images) {
                        
                        $pathss = url('public/uploads') . "/" . $images->image;
                        // echo $images->image;
                        $image_insert =  array(
                            'name' =>  $pathss,
                        );                        
                    $insert_images[] = $image_insert;
                    }

                    $paths = url('public/uploads') . "/" . $products->image;
                    $array_inserts =  array(
                        'provider_id' => $provider->id,
                        'name' =>  $products->name,
                        'description' =>  $products->description,
                        'price' => $products->normal_price,
                        'discount' => $products->discount,
                        'main_image'=>$paths,
                        'image' => $insert_images,
                        'video' => $products->video,
                        'discount_quantity' => $products->discount_quantity,
                        'category_name' => $categories[0]->name,
                    );
                    $new_products[]  =  $array_inserts;
                    
                }

                $groups = Group_product::where('provider_id', '=', $provider->id)->where('status', '=', 'Active')->get();
                $current_time = date('h:i a');
                // $provider_gamer = array(

                //     'provider_name' => $provider->name,
                //     'estimate_time' => $provider->estimate_time,
                //     'current_status' => $provider->current_status,
                //     'provider_image' => $provider_img,
                //     'provider_lat' => $provider->lat,
                //     'provider_lng' => $provider->lng,               
                //     'provider_address' => $provider->address,
                //     'provider_food_type' => $provider->food_type,
                //     'delivery_charges' => $provider->delivery_charges,
                //     'min_shop_price' => $provider->min_shop_price,
                //     'current_server_time' => $current_time,
                //     'server_time' => $current_time,
                //     'distance' => $provider->distance,
                //     'dis_limit' => $provider->dis_limit,
                //     'category_id' => $provider->category_id,
                //     'product'=>$new_products,
                // );
                // $provider_array[] =  $provider_gamer;

                $categories = Categories::where('id', '=', $provider->category_id)->get();
                foreach ($categories as $cat) {
                    $path = url('public/uploads/Categories') . "/" . $cat->image;
                    $array_insert =  array(
                        'name' =>  $cat->name,
                        'image' =>  $path,
                        'category_id' =>  $cat->id,

                    );

                    $new_category[]  =  $array_insert;
                }
            }
        }
        $productss = Product::where('provider_id', '=', '1')->get();
        foreach($productss as $products){
            
        }
        //  $success['data'] = $provider_array;
        //  $success['data1'] = $new_category;
        $success['data2'] = $new_products;

        // $success['data'] = $insert_images;

        return response()->json(['response_api' => $success], $this->successStatus);
    }
    
    public function get_all_product_by_category($id,$lat,$lon){

    $new_category =array();
    $new_products =array();
    $providers = Provider_Model::where('category_id','=', $id)->where('status','=','Active')->get();
          if ($providers->isNotEmpty()) {
            $markers = collect($providers)->map(function ($item) use ($lat, $lon) {
                $item->distance = calculateDistanceBetweenTwoAddresses($item->lat, $item->lng, $lat, $lon);
                
            });
        }
        
         $provider_array = [];
         
                     $new_products = [];
             foreach ($providers as $provider) {
                 if($provider->distance <= $provider->dis_limit){
                     
                    if(!empty($provider->image)){
                $provider_img = $provider->image;
            }else{
                $provider_img = "https://chopal.in/wp-content/uploads/2019/10/image-696x392.jpg";
            }
              $product = Product::where('provider_id','=',$provider->id )->where('status','=','Active')->get();
              $categories = Categories::where('id', '=', $provider->category_id)->get();
               
              $array_inserts=array();
                 foreach ($product as $products) {
                     $imagess = Product_Images::where('product_id', '=', $products->id)->get();
                    $insert_images=[];
                    foreach ($imagess as $images) {
                        $pathss = url('public/uploads') . "/" . $images->image;
                        // echo $images->image;
                        $image_insert =  array(
                            'name' =>  $pathss,
                            'id'=>$images->id,
                        );                        
                    $insert_images[] = $image_insert;
                    }
                     $paths = url('public/uploads')."/".$products->image;
                     
            $array_inserts =  array(
                'provider_id'=>$provider->id,
                'name' =>  $products->name, 
                'description' =>  $products->description,
                'price'=> $products->normal_price,
                'discount' => $products->discount,
                'main_image'=>$paths,
                'image' => $insert_images,
                'video' => $products->video,
                'category_id'=>$products->category_id,
                'discount_quantity'=>$products->discount_quantity,
                'category_name' => $categories[0]->name,
                );
            
                $new_products[]  =  $array_inserts;
          }

            $groups = Group_product::where('provider_id','=',$provider->id)->where('status','=', 'Active')->get(); 
                   $current_time = date('h:i a');
                $provider_gamer = array(
                    
                    'provider_name' => $provider->name,
                    'estimate_time' => $provider->estimate_time,
                    'current_status' => $provider->current_status,
                    'provider_image' => $provider_img,
                    'provider_lat' => $provider->lat,
                    'provider_lng' => $provider->lng,               
                    'provider_address' => $provider->address,
                    'provider_food_type' => $provider->food_type,
                    'delivery_charges' => $provider->delivery_charges,
                    'min_shop_price' => $provider->min_shop_price,
                    'current_server_time' => $current_time,
                    'server_time' => $current_time,
                    'distance' => $provider->distance,
                    'dis_limit' => $provider->dis_limit,
                    'category_id' => $provider->category_id,
                    'product'=>$new_products,
                );
                $provider_array[] =  $provider_gamer;
             }
             }

        //  $success['data'] = $provider_array;
        //  $success['data1'] = $new_category;
         $success['data2'] = $new_products;
         
         
              return response()->json(['response_api'=>$success], $this-> successStatus); 
}
    
    public function get_category_provider($id, $lat, $lon){

        // if(!empty($city)){
        $provider_array_send = [];
        $product = Product::where('category_id','=',$id)->get();
        foreach($product as $products){
            $providers = Provider_Model::where('id','=',$products->provider_id)
            // ->where('city','=',$city)
            ->where('status','=', 'Active')
            ->get();
            
            if ($providers->isNotEmpty()) {
            $markers = collect($providers)->map(function ($item) use ($lat, $lon) {
                $item->distance = calculateDistanceBetweenTwoAddresses($item->lat, $item->lng, $lat, $lon);
            });
        }
        // $provider_array_send = [];
         foreach ($providers as $provider) {
            // echo count($providers);
            // die();
            
            $provider_img = "";
            $provider_img = "";
            $delivery_charges =0;
            $pro_dis = $provider->distance/1000;
            $delivery_time = "";
            if($pro_dis>10){
                $delivery_charges=120;   
                $delivery_time="30-40 mins";
            }
            else{
                 $delivery_charges=60;
                 $delivery_time="1-1:30 hours";
            }
            // if($provider->distance <= $provider->dis_limit){}
            
            if(!empty($provider->image)){
                $provider_img = url('public/uploads')."/".$provider->image;
                // $provider_img = $provider->image;
            }else{
                $provider_img = "https://chopal.in/wp-content/uploads/2019/10/image-696x392.jpg";
            }

            $groups = Group_product::where('provider_id','=',$provider->id)->where('status','=', 'Active')->get(); 

                $current_time = date('h:i a');
                $provider_gamer = array(
                    'provider_name' => $provider->name,
                    'estimate_time' => $provider->estimate_time,
                    'current_status' => $provider->current_status,
                    'provider_id' => $provider->id,
                    'provider_image' => $provider_img,
                    'provider_lat' => $provider->lat,
                    'provider_lng' => $provider->lng,               
                    'provider_address' => $provider->address,
                    'provider_food_type' => $provider->food_type,
                    'delivery_charges' => $provider->delivery_charges,
                    'min_shop_price' => $provider->min_shop_price,
                    'current_server_time' => $current_time,
                    'delivery_time'=>$delivery_time,
                    'delivery_charges' =>$delivery_charges,
                    'server_time' => $current_time,
                );
                $provider_array_send[] =  $provider_gamer;
            
        }
        }
            // $providers = Provider_Model::where('category_id','=',$id)
            // // ->where('city','=',$city)
            // ->where('status','=', 'Active')
            // ->get();
        // }
        // else{
        //     $providers = Provider_Model::where('category_id','=',$id)->where('status','=', 'Active')->get();
        // }
        // dd($providers);
        
        
        
        // $providers = $providers->where('distance', '<=', 3);
        // dd($providers);
        
        
         
          $food_types = Provider_Model::distinct()->select('food_type')->where('category_id','=',$id)->get();

         $success['data'] = $provider_array_send;
         $success['date_two'] = $food_types;
              return response()->json(['response_api'=>$success], $this-> successStatus); 
    }

    public function get_provider($lat, $lon){

        // if(!empty($city)){
            $providers = Provider_Model::where('status','=', 'Active')
            // ->where('city','=',$city)
            ->get();
        // }
        // else{
        //     $providers = Provider_Model::where('category_id','=',$id)->where('status','=', 'Active')->get();
        // }
        // dd($providers);
        
        if ($providers->isNotEmpty()) {
            $markers = collect($providers)->map(function ($item) use ($lat, $lon) {
                // var_dump($item->lat);
                $item->distance = calculateDistanceBetweenTwoAddresses($item->lat, $item->lng, $lat, $lon);
            });
          
        }
       
        
        // $providers = $providers->where('distance', '<=', 3);
        // dd($providers);
        
        
         $provider_array_send = [];
         foreach ($providers as $provider) {
            // echo count($providers);
            // die();
            
            $provider_img = "";
            $delivery_charges =0;
            $pro_dis = $provider->distance/1000;
            $delivery_time="";
            if($pro_dis>10){
                $delivery_charges=120;  
                $delivery_time="1-1:30 hour";
            }
            else{
                 $delivery_charges=60;
                 $delivery_time="30-40 mins";
            }
            // if($provider->distance <= $provider->dis_limit){
            //     dd($provider->distance);
            //     if($provider->distance>"10000"){
            //         $delivery_charges=120;
            //     }
            //     else{
            //         $delivery_charges=60;
            //     }
            // }
            // dd($provider->distance);
            
                // dd($provider->dis_limit);
            if(!empty($provider->image)){
                $provider_img = url('public/uploads')."/".$provider->image;
                // $provider_img = $provider->image;
            }else{
                $provider_img = "https://chopal.in/wp-content/uploads/2019/10/image-696x392.jpg";
            }
            

            $groups = Group_product::where('provider_id','=',$provider->id)->where('status','=', 'Active')->get(); 
            $products = Product::where('provider_id','=',$provider->id)->where('status','=','Active')->get();
            $product_data=[];
         
           foreach($products as $product){
               $imagess=[];
               $image = Product_Images::where('product_id','=',$product->id)->get();
               
               $categories = Categories::where('id','=',$product->category_id)->get();
               $category_name="";
               foreach($categories as $category){
                   $category_name=$category->name;
               }
            //   $new_category [] = $category;
               foreach($image as $images){
                   $images_of_product= url('public/uploads')."/".$images->image;
                   $image_array = array(
                       'image'=>$images_of_product,
                       );
                       $imagess[]=$image_array;
               }
               $product_image = url('public/uploads')."/".$product->image;
               
               $image_array = array(
                    'image'=>$imagess,
                    'provider_id'=>$product->provider_id,
                    'category_id'=>$product->category_id,
                    'name'=>$product->name,
                    'normal_price'=>$product->normal_price,
                    'discount'=>$product->discount,
                    'main_image'=>$product_image,
                    'video'=>$product->video,
                    'description'=>$product->description,
                    'discount_quantity'=>$product->discount_quantity,
                    'category_name'=>$category_name,
                    
                );
                $product_data[]=$image_array;
           }
                $current_time = date('h:i a');
                $provider_gamer = array(
                    'provider_name' => $provider->name,
                    'estimate_time' => $provider->estimate_time,
                    'current_status' => $provider->current_status,
                    'provider_id' => $provider->id,
                    'provider_image' => $provider_img,
                    'provider_lat' => $provider->lat,
                    'provider_lng' => $provider->lng,               
                    'provider_address' => $provider->address,
                    'provider_food_type' => $provider->food_type,
                    'delivery_charges' => $provider->delivery_charges,
                    'min_shop_price' => $provider->min_shop_price,
                    'current_server_time' => $current_time,
                    'server_time' => $current_time,
                    
                    'delivery_time'=>$delivery_time,
                    'delivery_charges'=>$delivery_charges,
                    'products'=>$product_data,
                    // 'imagesss'=>$image,
                );
                $provider_array_send[] =  $provider_gamer;
            
        }
        //   $food_types = Provider_Model::distinct()->select('food_type')->where('category_id','=',$id)->get();

         $success['data'] = $provider_array_send;
        //  $success['date_two'] = $food_types;
              return response()->json(['response_api'=>$success], $this-> successStatus); 
    }


    public function get_category_city_provider($id,$city){

        $providers = Provider_Model::where('category_id','=',$id)->where('city','=',$city)->get();

        $provider_array_send = [];

        foreach ($providers as $provider) {

           $provider_img = "";

           if(!empty($provider->image)){
               $provider_img = url('public/uploads')."/".$provider->image;
           }else{
               $provider_img = "https://chopal.in/wp-content/uploads/2019/10/image-696x392.jpg";
           }

           $groups = Group_product::where('provider_id','=',$provider->id)->get(); 

           if(!empty($groups)){

           $provider_gamer = array(
               'provider_name' => $provider->name,
               'provider_id' => $provider->id,
               'provider_image' => $provider_img,                
               'provider_lat' => $provider->lat,
               'provider_lng' => $provider->lng,                                
               'provider_address' => $provider->address,                                

           );

            $provider_array_send[] =  $provider_gamer;
        }

            
        }

        $success['data'] = $provider_array_send;
             return response()->json(['response_api'=>$success], $this-> successStatus); 


   }






    public function user_orders(Request $request) {

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $user_id = $input['user_id'];

         $orders = Order::where('user_id','=',$user_id)->orderBy('id','DESC')->get();

         $array_to_send = [];

         foreach($orders as $value) {

                $array_orginal_price = [];
            
                $user_product = explode(",",$value->product_ids);
                $user_deal = explode(",",$value->deals_ids);
                $order_product_price = explode(",",$value->prices);
                $order_product_quantity = explode(",",$value->quantity);

                $product_name = Product::whereIn('id', $user_product)->get();
                $deal_name = Deal_items::whereIn('deal_id', $user_deal)->get();
                
                $p_name_arr = array();
                $d_name_arr = array();
                $original_price_arr = array();
                
                foreach ($product_name as $value_name) {
                    $p_name_arr[] = $value_name->name;                      
                }
                
                foreach ($deal_name as $value_name) {
                    $p_name_arr[] = $value_name->item_name;                      
                }
                
                //   $pro_array =  array(
                //             'p_name_arr' =>  $p_name_arr, 
                //             'd_name_arr' =>  $d_name_arr, 
                //   );
                
                

                $count = 0;
                foreach ($order_product_price as $value_p) {
                    // $orginal_price =  $value_p/$order_product_quantity[$count];
                    $array_orginal_price[] = $value_p;
                    $array_orginal_price[] = $order_product_quantity[$count];
                    $count = $count+1;                           
                }

                $driver_info = Driver::find($value->driver_id);
                $driver_num = "";
                $driver_id = "";
                $driver_name = "";
                
                if(!empty($driver_info)){
                    $driver_num = $driver_info->phone;
                    $driver_id = $driver_info->id;
                    $driver_name = $driver_info->name;
                }

                $provider_info = Provider_Model::find($value->provider_id);

                $provider_image = "";
                $provider_name = "";

                if(!empty($provider_info)){
                    $provider_image = $provider_info->image;

                    if(empty($provider_image)){
                        $provider_image = "https://fleetchain.net/fc_food_api/public/uploads/tikka_02.jpg";
                    }

                    $provider_name  = $provider_info->name;
                }

               // dd($array_orginal_price);

               $converted = date('d M Y h.i.s A', strtotime($value->created_at));
                $reversed_time = date('h.i A, d M Y', strtotime($converted));

                $pickup_time = "";
                $delivery_time = "";

                if(!empty($value->pickup_time_date)){

                    $converted = date('d M Y h.i.s A', strtotime($value->pickup_time_date));
                    $pickup_time = date('h.i A, d M Y', strtotime($converted));

                }

                if(!empty($value->delivery_time_date)){
                    $converted = date('d M Y h.i.s A', strtotime($value->delivery_time_date));
                    $delivery_time = date('h.i A, d M Y', strtotime($converted));
                }

            $array_to_response = array(
                'order_id' => $value->id,
                'created_at' => $reversed_time,
                'pickup_at' => $pickup_time,
                'delivery_at' => $delivery_time,
                'guest_user_name' => $value->guest_user_name,
                'driver_phone' =>  $driver_num,
                'driver_id' =>  $driver_id,
                'driver_name'=>$driver_name,
                'provider_name' => $provider_name,
                'provider_image' => $provider_image,
                'order_status' =>  $value->order_status,
                'user_type' => $value->user_type,
                'provider_id' => $value->provider_id,
                'lat' => $value->lat,
                'lng' => $value->lng,
                'phone' => $value->phone,
                'user_id' => $value->user_id,
                'user_type' => $value->user_type,
                // 'product_ids' => $value->product_ids, 
                'prices' => $value->prices,
                'product_name' => $p_name_arr,
                'original_price' => $array_orginal_price,
                'variants' => $value->variants, 
                'product_ids' => $value->product_ids, 
                'deals_ids' => $value->deals_ids,
                'order_quantity' => $value->quantity,
                'description' => $value->description, 
                'address' => $value->address, 
                'country' => $value->country, 
                'city' => $value->city,
                'delivery_charges' => $value->delivery_charges,
                'payment_type' => $value->payment_type, 
                'total_price' => $value->total_price,
                'discount_price' => $value->discount_price,
                
            );

            $array_to_send[] = $array_to_response;
         }

         $success['status'] =  "success";
         $success['data'] = $array_to_send;

    return response()->json(['response_api'=>$success], $this-> successStatus); 
    

}


 public function change_password(Request $request){

               
    $payload = json_decode(request()->getContent(), true);
    $input = $payload;

    $user_id = $input['user_id'];
    $password = $input['old_password'];
    $new_password = $input['new_password'];

    if(!empty($user_id) &&  !empty($password) && !empty($new_password)){
                $old_password = false;
                if(Auth::attempt(['id' => $user_id, 'password' => $password])){
                    $old_password = true;
                }

                if($old_password){

                    $new_password = bcrypt($new_password);

                    $user = User::find($user_id);                    
                    $user->password = $new_password;
                    $user->update();


                    $success['status'] =  "success";
                    $success['data'] =  "password changed";
                    return response()->json(['response_api'=>$success], $this-> successStatus); 
                }else{
                    $success['error'] =  "current passowrd is not correct";
                    return response()->json(['response_api'=> $success], 401); 
                }
    

    }else{

        $success['error'] =  "there is problem please contact admin";
        return response()->json(['response_api'=> $success], 401); 


    }
   

 }


    

  




/** 
     * details api 

     * 

     * @return \Illuminate\Http\Response 

     */ 

    public function details() 
    { 
        $user = Auth::user(); 
        return response()->json(['success' => $user], $this-> successStatus); 

    } 


    public function forgot_password(Request $request) 
    { 
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $email = $input['email'];

        // $user = User::where(['email' => $email])->where(['status' => 'Active'])->first();  
        $user = User::where(['email' => $email])->first();  

        if(!empty($user)){
            // $data = array('name'=>"Ashir here");
            $otp = rand(100000,999999);
            // $password = bcrypt($new_password);

            $update_array =  array(
                'otp' =>  $otp,                        
            );
                $user = User::where('email','=',$email)->first();    
                $user->update($update_array);

            $headers = "From: no-reply@fleetchain.net" . "\r\n";
            mail($email,"Forgot Password","Use this for update password: \n".$otp,$headers);
            
            $success['success'] = 'Success';
            $success['data'] = $user->id;
            return response()->json(['response_api'=>$success], $this-> successStatus);   

        }else{
            $success['error'] = "Email not Exist";
            return response()->json(['response_api'=>$success], 401);   
        }
    }
    
    public function check_password_otp(Request $request) 
    { 
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        $otp = $input['otp'];

        $user = User::where(['otp' => $otp])->where(['status' => 'Active'])->first();  
        

        if(!empty($user)){
            
        //     $data = array('name'=>"Ashir here");
        //     $new_password = rand(100000,999999);
        //     $password = bcrypt($new_password);

        //     $update_array =  array(
        //         'password' =>  $password,                        
        //     );
    
        //         $user = Provider_Model::where('email','=',$email)->first();    
        //         $user->update($update_array);

        //     $headers = "From: no-reply@cheaperfleet.com" . "\r\n" .
        //     "CC:  qa.ms2018@gmail.com";
        // mail($email,"Forgot Password","Your New Password: \n".$new_password,$headers);


            $success['success'] = 'Success';
            $success['data'] = $user->id;
            return response()->json(['response_api'=>$success], $this-> successStatus);   

        }else{

            $success['error'] = "otp is not valid";
            return response()->json(['response_api'=>$success], 401);
        }
    }
    
    public function update_password(Request $request) 
    { 
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $user_id = $input['user_id'];
        $password = $input['password'];

        $pass = bcrypt($password);

        // $user = User::where(['id' => $user_id])->where(['status' => 'Active'])->first();
        $array_update =  array(
            'password' =>  $pass, 
        );
        
        $user_data = User::find($user_id);
        $user_data->update($array_update);
        $user = User::find($user_id);

        $success['status'] =  "success";
        $success['data'] =  $user;
  
        return response()->json(['response_api'=>$success], $this-> successStatus); 
    }


     public function save_user_address(Request $request){

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $lat = "";
        $lng = "";
        $mobile = "";

        if(isset($input['lat'])){
            $lat = $input['lat'];
        }

        if(isset($input['lng'])){
            $lng = $input['lng'];
        }

        if(isset($input['mobile'])){
            $mobile = $input['mobile'];
        }
    

        

        $address = $input['address'];
        $title  = $input['title'];
        $user_id = $input['user_id'];

                        $array_insert =  array(
                            'user_id' =>  $user_id, 
                            'address' =>  $address,  
                            'title' => $title,
                            'mobile' => $mobile,
                            'lat' => $lat,
                            'lng' => $lng
                        );

                        User_address::create($array_insert);

                        $success['status'] = "success";
        
     return response()->json(['response_api'=>$success], $this-> successStatus); 
        

     }

     public function edit_user_address(Request $request){

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $address_id = "";

        if(!empty($input['address_id'])){

            $address_id  = $input['address_id'];
            $lat = "";
            $lng = "";
            $mobile = "";
    
            if(isset($input['lat'])){
                $lat = $input['lat'];
            }
    
            if(isset($input['lng'])){
                $lng = $input['lng'];
            }
    
            if(isset($input['mobile'])){
                $mobile = $input['mobile'];
            }         
            $address = $input['address'];
            $title  = $input['title'];
            $user_id = $input['user_id'];
    
                            $array_update =  array(
                                'user_id' =>  $user_id, 
                                'address' =>  $address,  
                                'title' => $title,
                                'mobile' => $mobile,
                                'lat' => $lat,
                                'lng' => $lng
                            );
    
    
                             User_address::find($address_id)->update($array_update);

                            $user_addresses = User_address::find($address_id)->get();
        $success['status'] = "success";
        $success['data'] = $user_addresses;
        return response()->json(['response_api'=>$success], $this-> successStatus); 

        }else{

            $success['error'] = "Address Id Is Not Found";
            return response()->json(['response_api'=>$success], 401);   

        }

      
     }

     public function delete_user_addresses($id){


        $user_addresses = User_address::find($id);
        $user_addresses->delete();

       
        $success['status'] = "success";
       return response()->json(['response_api'=>$success], $this-> successStatus); 

    }

    public function set_default_addresses($id,$user_id){

        $user_addresses = User_address::find($id);
        $user_addresses->default_address = "1";
        $user_addresses->update();

        $success['status'] = "success";

        User_address::where('user_id','=',$user_id)->where('id','!=',$id)->update(['default_address'=>'0']);

        return response()->json(['response_api'=>$success], $this-> successStatus); 
    }



     public function get_user_addresses($id){
        $user_addresses = User_address::where('user_id','=',$id)->get();

        $success['status'] = "success";
        $success['data'] = $user_addresses;

        return response()->json(['response_api'=>$success], $this-> successStatus); 

     }
     
     
     
    public function save_user_reviews(Request $request){

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        
        $order_id = $input['order_id']; 
        $provider_id = $input['provider_id'];
        $user_id = $input['user_id'];
        $rating = $input['rating'];
        $reviews  = $input['reviews'];
        
        if($rating <= 5 && $rating > 0){
            $array_insert =  array(
                'order_id' =>  $order_id,
                'provider_id' =>  $provider_id,
                'user_id' =>  $user_id,
                'rating' => $rating,
                'reviews' => $reviews,
                'status' => 'Active'
            );
            
            Reviews::create($array_insert);
            $success['status'] = "success";
            return response()->json(['response_api'=>$success], $this-> successStatus); 
        }
        else
        {
            $success['status'] = "Rating number should be correct";
            return response()->json(['response_api'=>$success], $this-> successStatus);    
        }
     }  
     
     
    public function get_user_reviews($order_id){

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $review = Reviews::where('order_id', '=', $order_id)->get();
        
        if(!empty($review[0]->rating)){
            $success['data'] = $review;
            return response()->json(['response_api'=>$success], $this-> successStatus);   
        }
        else{
            $success['status'] = "Error";
            return response()->json(['response_api'=>$success], $this-> successStatus);        
        }
     }  
     
    public function get_coupon_discount(Request $request){
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        
        // $user_id = $input['user_id'];
        
        $provider_id = $input['provider_id'];
        $c_number = $input['c_number'];
        $current_date = date("Y-m-d");
        
        // $coupons = Coupons::where('provider_id','=',$provider_id)->where('c_number','=',$c_number)->where('status','=','Active')->first();
        $coupons = Coupons::where('provider_id','=',$provider_id)->where('status','=','Active')->first();
        $end_date = $coupons->end_date;
        $c_number_org = $coupons->c_number;
        // echo $end_date;
        // die();
        
        if($c_number == $c_number_org)
        {
             if($current_date <= $end_date)
            {
                $array_insert =  array(
                    'c_price' =>  $coupons->c_price,
                );
                $success['data'] =    $array_insert;
                return response()->json(['response_api'=>$success], $this-> successStatus); 
            }
            else
            {
                $success['data'] =    "Your coupon is not valid";
                return response()->json(['response_api'=>$success], $this-> successStatus);
            }
        }
        else
        {
            $success['data'] =    "Your coupon is not valid";
            return response()->json(['response_api'=>$success], $this-> successStatus);
        }
       
    }
     
     
    public function cancel_order(Request $request) 
    {
        // $user_id = Auth::user()->id;
        // echo $user_id;
        
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        $user_id =  $input['user_id'];
        $order_id =  $input['order_id'];
        $status = $input['order_status'];
        
        $provider_id =  $input['provider_id'];
        
        $device_id = DB::table('provider_records as p')
                        ->select('device_id')
                        ->where('p.id', '=' ,$provider_id)
                        ->first();
        
        
        $access_token = 'AAAA6zM-jhM:APA91bEhsArZtdCZhdsY3EuabN2AMA2ZLtgXsOYPXOE09OugZ4y03GisEycgFXqrZb1R4VSK44TEvgzJw-JMH7x2UcY08igoHY11fPZfyRbwwHupTLhuGGSciGj4BQrE_cjDrAqp2Rs2';
        $reg_id = $device_id->device_id;

        $array_insert =  array(
            'order_status' =>  $status,                        
        );
        
        
        $message = [
        'notification' => [
        'title' => 'Order Status',
        'body' => $status
        ],
        'to' => $reg_id
        ];

            $order = Order::find($order_id);   
            $order->update($array_insert);
        //     $update_user = Provider_Model::find($order_id);    

        //     $success['status'] =  "success";
        //     $success['details'] =  $update_user;

        // return response()->json(['response_api'=>$success], $this-> successStatus); 

         
        $client = new GuzzleHttp\Client([
        'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => 'key='.$access_token,
        ]
        ]);
        $response = $client->post('https://fcm.googleapis.com/fcm/send',
        ['body' => json_encode($message)]
        );
        echo $response->getBody();
        


    }

    // public function save_to_wallet(Request $request){
        
    //     $payload = json_decode(request()->getContent(), true);
    //     $input = $payload;

    //     $order_id =  $input['order_id'];
    //     // $trans_status = $input['trans_status'];
    //     $amount = $input['amount'];
        
    //     $array_insert =  array(
    //         'order_id' =>  $order_id, 
    //         'trans_status' => 'DB',
    //         'amount' => $amount,
    //     );
        
    //     $wallet = Wallet::create($array_insert);

    //     $success['status'] =  "success";
    //     $success['data'] =  $wallet;
  
    //     return response()->json(['response_api'=>$success], $this-> successStatus); 
    // }
    
    
    public function order_payment(Request $request){
        
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $order_id =  $input['order_id'];
        $amount = $input['amount'];
        
        $orders = Order::where('id','=',$order_id)->first();
        $payment_type = $orders->payment_type;
        $t_price = $orders->total_price;
        
        // print_r($payment_type);
        // die();
        
        if($payment_type != 'credit')
        {
            $array_insert =  array(
                'order_id' =>  $order_id, 
                'trans_status' => 'CR',
                'amount' => $t_price,
            );
            $wallet = Wallet::create($array_insert);
            
            $array_insert2 =  array(
                'order_id' =>  $order_id, 
                'trans_status' => 'DB',
                'amount' => $amount,
            );
            $wallet = Wallet::create($array_insert2);
    
            $success['status'] =  "success";
            // $success['data'] =  $wallet;
            return response()->json(['response_api'=>$success], $this-> successStatus); 
        }
    }
    
    public function wallet_amount(Request $request)
    {
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        $user_id =  $input['user_id'];
        
        $data = DB::table('wallets as w')
                        ->leftjoin('orders as o', 'o.id', '=', 'w.order_id')
                        ->leftjoin('users as u', 'u.id', '=', 'o.user_id')
                        ->select('u.id', 'trans_status', 'amount')
                        ->where('u.id', '=' ,$user_id)
                        ->get();
        $ttl_cr = 0;
        $ttl_db = 0;

        foreach ($data as $value) 
        {
            if($value->trans_status == 'CR')
            {
                $ttl_cr += $value->amount;
            }
            
            if($value->trans_status == 'DB')
            {
                $ttl_db += $value->amount;
            }
        }
        
        $wa = $ttl_db - $ttl_cr;

        $success['status'] =  'Success';
        $success['data'] =  $wa;
        return response()->json(['response_api'=>$success], $this-> successStatus); 
    }
    
    
    public function test() 
    {
            $device_id = DB::table('orders as o')
                            ->leftjoin('users as u', 'u.id', '=', 'o.user_id')
                            ->select('device_id')
                            ->where('o.id', '=' ,86)
                            ->get();  

            $success['status'] =  $device_id;
            // $success['details'] =  $update_user;

        return response()->json(['response_api'=>$success], $this-> successStatus); 

    }
    

    
}