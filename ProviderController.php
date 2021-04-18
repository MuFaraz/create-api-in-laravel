<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller; 
use App\Provider_Model; 
use App\Countries;
use App\Driver;
use App\Product;
use App\Categories;
use App\Order;
use App\User;
use App\Chat;
use App\Group_product;
use App\Spin_conditions;
use App\Dealy_deals;
use App\Deals;
use App\Deal_items;
use App\Reviews;
use App\Coupons;
use App\Route_categories;
use App\Routes;
use Hash;
use Validator;
use DB;
use Artisan;
use Redirect;
use Mail;
use GuzzleHttp;
use Image;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Charge;
use App\Product_Images;

class ProviderController extends Controller 
{

public $successStatus = 200;



    public function login(){ 
        
       
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        
        $email = $input['email'];
        $password_u = $input['password'];
        
        // $device_id =  $input['device_id'];
        
        $password_right = false;
        
        
        $user_provider = Provider_Model::where(['email' => $email])->first(); 
     
       
        
        //  if ($user){
        //      $success['error'] = 'Email or Password Incorrect';
        //     return response()->json(['response_api'=> $success], 401);
        //  }
            
        if(empty($user_provider)){
            
            $success['error'] = 'Email or Password Incorrect';
            return response()->json(['response_api'=> $success], 401); 
        }
        
        $provider_id = $user_provider->id;
        $hashedPassword =   $user_provider->password;
        $user_password = $password_u;
        if (Hash::check($user_password, $hashedPassword, [])) {
            $password_right = true;
          }

        if(!empty($user_provider) && $password_right){ 

            if($user_provider->status=="Active"){

                // $product = Product::where('provider_id','=',$user_provider->id)->get();

                if(!empty($user_provider->category_id)){
                    $user_pro = "true";
                }else{
                    $user_pro = "false";
                }
                
                $array_insert =  array(
                    'device_id' =>  $input['device_id'],                        
                );
                
                $provdr = Provider_Model::find($provider_id);    
                $provdr->update($array_insert);
                
                
                $success['prodduct'] =  $user_pro;
                $success['success'] =  "Active";
                $success['data'] = $user_provider;
                return response()->json(['response_api' => $success], $this-> successStatus); 

            }else{

                $success['error'] =  "Your Account is InActive";
                return response()->json(['response_api' => $success], 401); 

            }
           

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
     
     public function getProduts($id)
     {
        $array_gamer = array();
         if(isset($id)){
        $product = Product::where('provider_id','=', $id)->get(); 
        $array_gamer = $product;
        $success['status'] =  "success";
        $success['data'] =  $array_gamer;
         }
         else
         {
          $success['status'] =  "error";
          $success['data'] =  $array_gamer;   
         }
        
         
      return response()->json(['response_api'=>$success], $this-> successStatus); 
     }
       public function getHighestRantProvider($lat,$lon)
    {
        $array_gamer = array();
        $review = Reviews::groupBy('provider_id')->where('rating', '>=', '4')->get();

        // $array_gamer = $review;
        $provider_detail = [];
        $new_provider_detail = [];
        foreach ($review as $reviews) {

            $provider_detail = Provider_Model::where('id', '=', $reviews->provider_id)->get();
            if ($provider_detail->isNotEmpty()) {
                $markers = collect($provider_detail)->map(function ($item) use ($lat, $lon) {
                    $item->distance = calculateDistanceBetweenTwoAddresses($item->lat, $item->lng, $lat, $lon);
                });
            }

            foreach ($provider_detail as $provider_details) {

                if ($provider_details->distance <= $provider_details->dis_limit) {
                    
                        $pathss = url('public/uploads') . "/" . $provider_details->image;
                    // dd($provider_details->distance);
                    $array_inserts =  array(
                        'name' => $provider_details->name,
                        'phone' =>  $provider_details->phone,
                        'email' =>  $provider_details->email,
                        'address' =>  $provider_details->address,
                        
                        'country' =>  $provider_details->country,
                        
                        'city' =>  $provider_details->city,
                        'image' =>  $pathss,
                    );

                    $new_provider_detail[]  =  $array_inserts;
                }
            }
        }



        $success['status'] =  "success";
        $success['data'] =  $new_provider_detail;

        //   $success['status'] =  "error";
        //   $success['data'] =  $array_gamer;
        return response()->json(['response_api' => $success], $this->successStatus);
    }
    public function topRatedProvider($lat,$lon)
    {
        $array_gamer = array();
        $review = Reviews::groupBy('provider_id')->where('rating','>=','4')->get();
        // $review = Reviews::where("rating", ">=", "4")->get();
        
        // $array_gamer = $review;
        $provider_detail = [];
$new_provider_detail=[];
        foreach ($review as $reviews) {

            $provider_detail = Provider_Model::where('id', '=', $reviews->provider_id)->get();
                 if ($provider_detail->isNotEmpty()) {
            $markers = collect($provider_detail)->map(function ($item) use ($lat, $lon) {
                $item->distance = calculateDistanceBetweenTwoAddresses($item->lat, $item->lng, $lat, $lon);
            });
        }

            foreach ($provider_detail as $provider_details) {
                
            if ($provider_details->distance <= $provider_details->dis_limit) {
                // dd($provider_details->distance);
                 $array_inserts =  array(
                    'name' => $provider_details->name,
                    'phone' =>  $provider_details->phone,
                    'email' =>  $provider_details->email,
                );
                
            $new_provider_detail[]  =  $array_inserts;
            }


                
            }
               
        }



        $success['status'] =  "success";
        $success['data'] =  $new_provider_detail;

        //   $success['status'] =  "error";
        //   $success['data'] =  $array_gamer;
        return response()->json(['response_api' => $success], $this->successStatus);
    }
     
     public function productsDiscount(Request $request)
     {
        $payload = json_decode(request()->getContent(), true);
        $data = $payload;
        $user_provider = Product::find($data["product_id"]);
        $user_provider->discount = $data["discount"];
        $user_provider->discount_price = $data["price"];
        $user_provider->discount_quantity = $data["items"];
        $user_provider->update();
        $success['status'] = 'success';
        return response()->json(['response_api'=>$success], $this-> successStatus); 
     }
     

    public function register(Request $request) 
    { 

        $otp = rand(100000,999999);
        $payload = json_decode(request()->getContent(), true);
        $data = $payload;
        $name =  $data['name'];
        $email =  $data['email'];
        $address =  $data['address'];
        $country =  $data['country'];
        $city =  $data['city'];
        $lat = $data['lat'];
        $long = $data['long'];
        $password = bcrypt($data['password']); 
        if(isset($data['image'])){
            $image = $data['image'];
            
        }else{
            $image = "";
        }
        
        // if(isset($data['food_type'])){
        //     $food_type = $data['food_type'];
        // }

        if(empty($data['name']) || empty($data['email']) ||  empty($data['address']) ||  empty($data['country']) || empty($data['city']) || empty($data['lat']) || empty($data['long']) ){
           
            $success['error'] = "Please Fill All Fields";
            return response()->json(['response_api'=>$success],401); 
        }else{


        $mobile = $data['mobile'];
        
        

        $headers = "From: no-reply@fleetchain.net" . "\r\n";
    mail($email,"User Confirmation","Please Enter this otp code \n".$otp,$headers);
    
    
      $array_insert =  array(
                'name' =>  $name, 
                'email' =>  $email, 
                'password' =>  $password,                
                'phone' => $mobile,
                'address' => $address,
                'country' => $country,
                'city' => $city,
                'lat' => $lat,
                'lng' => $long,
                'otp' =>  $otp,
                // 'food_type' => $food_type
      );


          $user = Provider_Model::where('email', '=', $data['email'])->first();    
        if(empty($user)){
        $user =  Provider_Model::create($array_insert);

        $success['name'] =  $user->name;
        $success['id'] =  $user->id;
        $success['otp'] = $otp;




        return response()->json(['response_api'=>$success], $this-> successStatus); 

        }else{

            $success['error'] = "email already exist";
            return response()->json(['response_api'=>$success],401); 

        }

    }  // else finished 


    }



    public function check_otp(Request $request) 
    { 
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        $status = $input['status'];
        $id = $input['id'];
        if($status=="1"){
            $user_provider = Provider_Model::find($id);
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
        $user_provider = Provider_Model::find($id);
        $email =   $user_provider->email;
            
        $otp = rand(100000,999999);
        $headers = "From: no-reply@fleetchain.net" . "\r\n";
        mail($email,"User Confirmation","Please Enter this otp code \n".$otp,$headers);
        
            $user_provider->otp = $otp;
            $user_provider->update();        
    
            $success['status'] =  "success";
            $success['otp'] =  $user_provider->otp;
            return response()->json(['response_api'=>$success], $this-> successStatus); 
    }


    public function details() 
    { 
        $user = array();
        return response()->json(['success' => $user], $this-> successStatus); 
    }

    public function forgot_password(Request $request) 
    { 
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        $email = $input['email'];

        $user = Provider_Model::where(['email' => $email])->where(['status' => 'Active'])->first();  

        if(!empty($user)){
            
            // $data = array('name'=>"Ashir here");
            $otp = rand(100000,999999);
            // $password = bcrypt($new_password);

            $update_array =  array(
                'otp' =>  $otp,                        
            );
                $user = Provider_Model::where('email','=',$email)->first();    
                $user->update($update_array);

            $headers = "From: no-reply@fleetchain.net" . "\r\n";
            mail($email,"Forgot Password","Use this for update password: \n".$otp,$headers);
            
            $success['success'] = 'Success';
            $success['data'] = $user->otp;
            return response()->json(['response_api'=>$success], $this-> successStatus);   

        }else{

            $success['error'] = "Email not Exist abcd";
            return response()->json(['response_api'=>$success], 401);
        }
    }
    
    
  public function check_password_otp(Request $request) 
    { 
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        $otp = $input['otp'];

        $user = Provider_Model::where(['otp' => $otp])->where(['status' => 'Active'])->first();  
        

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
    
            $user_id = $input['provider_id'];
            $password = $input['password'];
            
            $pass = bcrypt($password);

            $array_update =  array(
                'password' =>  $pass, 
            );
            
            $user_data = Provider_Model::find($user_id);
            $user_data->update($array_update);
            $user = Provider_Model::find($user_id);
    
            $success['status'] =  "success";
            $success['data'] =  $user;
      
            return response()->json(['response_api'=>$success], $this-> successStatus); 
        }    



    public function change_password(Request $request){

               
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
    
        $user_id = $input['provider_id'];
        $password = $input['old_password'];
        $new_password = $input['new_password'];
    
        if(!empty($user_id) &&  !empty($password) && !empty($new_password)){

            $user_provider =  Provider_Model::find($user_id);
            $old_password = false;

            $hashedPassword =   $user_provider->password;
            $user_password = $password;
            if (Hash::check($user_password, $hashedPassword, [])) {
                $old_password = true;
              }
                
                    if($old_password){
    
                        $new_password = bcrypt($new_password);
    
                        $user = Provider_Model::find($user_id);                    
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

     public function bulk_update_order_status(Request $request){
         
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        
        $status = $input['status'];
        
        
        $order_id = $input['order_id'];
         $order = Order::find($order_id);
            $device_id = DB::table('orders as o')
                            ->leftjoin('users as u', 'u.id', '=', 'o.user_id')
                            ->select('device_id', 'email')
                            ->where('o.id', '=' ,$order_id)
                            ->first();

        $reg_id = $device_id->device_id;
        $email = $device_id->email;
        
        if(!empty($reg_id)){
        
        // $access_token = 'AAAA6zM-jhM:APA91bEhsArZtdCZhdsY3EuabN2AMA2ZLtgXsOYPXOE09OugZ4y03GisEycgFXqrZb1R4VSK44TEvgzJw-JMH7x2UcY08igoHY11fPZfyRbwwHupTLhuGGSciGj4BQrE_cjDrAqp2Rs2';
            $access_token = 'AAAATdlFyfY:APA91bGNQe6m0uzFXYhzk2TDlikfZQF888vGjjyIi-wbKcWKtQdpr1zKgmuFJNOjDSDJWCNaks_XDAKTugKMpkGUV0EHWtaw9MhZRGLtRIb8x_1vaMmp-r5Udl3qLSwCqXkSsc7RknY-';
        
        // $reg_id = 'dy7wDPd5zKw:APA91bHROWid-DyZ3tJK_-CYhAzAYyYgvPQyHaMnXccg54eKfD9-19tBqF5VIv4qw6hcVt9wVMUuQkPhXwYBT5VdOTL9bRGK2KVac1kbtBom-xhwKtSpDarvvpw4TZjRwlyh_95uHVgh';
            if(isset($status)){
    
                if(!empty($status) && !empty($order_id)){
                       
                    $orders = Order::whereIn('id', $order_id)->update(['order_status' => $status]);
                    $message = [
                    'notification' => [
                    'title' => 'Order Status',
                    'body' => $status
                    ],
                    'to' => $reg_id
                    ];
                    
                    $headers = "From: no-reply@fleetchain.net" . "\r\n";
                    mail($email,"Order Status","Your order is now: \n".$status,$headers);
                    
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
                    
                    // $success['status'] =  "Status Updated Successfully";
                    // return response()->json(['response_api'=>$success], $this-> successStatus); 
                }
    
            }
            else{
    
                $success['error'] =  "there is problem please contact admin";
                return response()->json(['response_api'=> $success], 401); 
            }
        }
        else
        {
                $success['error'] = "Error Occured";
                return response()->json(['response_api'=>$success], 201);
        }

     }


     public function bulk_update_driver_assign(Request $request){
         
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        if(isset($input['order_id'])){

            if(!empty($input['order_id']) && !empty($input['driver_id'])){
                    $drivers_id  = $input['driver_id'];
                
                    $orders = Order::whereIn('id', $input['order_id'])->update(['driver_id' => $drivers_id]);

                $success['status'] =  "Driver Updated Successfully";
                return response()->json(['response_api'=>$success], $this-> successStatus); 
            }

        }else{

            $success['error'] =  "there is problem please contact admin";
            return response()->json(['response_api'=> $success], 401); 

        }


     }




    public function get_setting($id) 
    {

        if (empty($id)) { 

            return response()->json(['error'=>'id is not found'], 401);            

         }

            $user = Provider_Model::find($id);     
            $success['status'] =  "success";
            $success['details'] =  $user;
            return response()->json(['response_api'=>$success], $this-> successStatus); 

    } //gamer





    public function update_setting(Request $request) 
    {

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        $user_id =  $input['user_id'];
        $name = $input['name'];
        $array_insert =  array(
            'name' =>  $name,                        
        );

            $user = Provider_Model::find($user_id);    
            $user->update($array_insert);
            $update_user = Provider_Model::find($user_id);    

            $success['status'] =  "success";
            $success['details'] =  $update_user;

        return response()->json(['response_api'=>$success], $this-> successStatus); 

    }

    public function fetch_category(){

          $categories = Categories::all();

          $new_category = [];

          foreach ($categories as $cat) {
              $product = Product::where('category_id','=',$cat->id)->where('status','=','Active')->get();
              $new_product=[];
              foreach($product as $products){
                  $imagess=url('public/uploads/Categories')."/".$products->image;
                  $product_insert =  array(
                      'product_id'=>$products->id,
                    'name'=>$products->name,
                    'normal_price'=>$products->normal_price,
                    'discount'=>$products->discount,
                    'main_image'=>$products->image,
                    'video'=>$products->video,
                    'description'=>$products->description,
                    'discount_quantity'=>$products->discount_quantity,
                );
                $new_product[]=$product_insert;
              }
              
              $path = url('public/uploads/Categories')."/".$cat->image;
            $array_insert =  array(
                'category_name' =>  $cat->name, 
                'image' =>  $path, 
                'category_id' =>  $cat->id,
                'products'=>$new_product,
                );

                $new_category []  =  $array_insert;
          }

          $success['status'] =  "success";
          $success['data'] =  $new_category;

      return response()->json(['response_api'=>$success], $this-> successStatus); 


    }

    public function save_group_product(){

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;


    if(isset($input['name']) && isset($input['position']) && isset($input['user_id'])){
        $provider_id =  $input['user_id'];
        $name = $input['name'];
        $position = $input['position'];
        $product_data=[];
      
        if(empty($name)  ||  empty($position) && !empty($provider_id)){
            
            $provider = Provider_Model::where('id','=',$provider_id);
            foreach($provider as $providers){
                
            }
            $products_array=[];
            $array=[];
            $new_product=[];
            $products = Product::where('provider_id','=',$provider_id)->get();
            // $products = Group_product::where('provider_id','=',$provider_id)->orderBy('position','asc')->get(); 
// new added
           foreach($products as $product){
                 
              $imagess=[];
              $image = Product_Images::where('product_id','=',$product->id)->get();
              $category = Categories::where('id','=',$product->category_id)->get();
              foreach($image as $images){
                  $images_of_product= url('public/uploads')."/".$images->image;
                  $image_arrays = array(
                      'image'=>$images_of_product,
                      );
                      $imagess[]=$image_arrays;
              }
              $product_image = url('public/uploads')."/".$product->image;
              $products_array = array(
                    'image'=>$imagess,
                    'name'=>$product->name,
                    'normal_price'=>$product->normal_price,
                    'discount'=>$product->discount,
                    'main_image'=>$product_image,
                    'video'=>$product->video,
                    'description'=>$product->description,
                    'discount_quantity'=>$product->discount_quantity,
                );
                $new_product[]=$products_array;
                $array = array(
                    
                    'category_name'=>$category[0]->name,
                     'provider_id'=>$product->provider_id,
                    'category_id'=>$product->category_id,
                    'products'=>$new_product,
                    );
                $product_data[]=$array;
           }


            $success['status'] =  "success";
            $success['data'] =  $product_data;
      
            return response()->json(['response_api'=>$success], $this-> successStatus);

        }else{

        $array_insert =  array(
            'provider_id' =>  $provider_id, 
            'name' =>  $name, 
            'position' =>  $position,   
        );    
      $group_p =  Group_product::create($array_insert);

      $products = Group_product::where('provider_id','=',$provider_id)->orderBy('position','asc')->get();

        $array_gamer = array();

         foreach ($products as $value) {

            $product = Product::where('provider_id','=', $value->provider_id)->where('group_id','=', $value->id)->get(); 

            $ab = array(
                'provider_id' => $value->provider_id,
                'name' => $value->name,
                'position' => $value->position,
                'group_id' => $value->id,
                'products' => $product
            ); 

            $array_gamer[]  = $ab;
         }

        //  dd($array_gamer);

      $success['status'] =  "success";
      $success['data'] =  $array_gamer;

      return response()->json(['response_api'=>$success], $this-> successStatus);


        }

 
    }
    elseif(isset($input['user_id'])){

        $provider_id =  $input['user_id'];
        $product_data =[];
        
        $productss = Group_product::where('provider_id','=',$provider_id)->orderBy('position','asc')->get(); 
        //Add new Line
        $products = Product::where('provider_id','=',$provider_id)->get(); 
        $image_array=[];
        $new_product =[];
        
            foreach($products as $product){
              $imagess=[];
              $image = Product_Images::where('product_id','=',$product->id)->get();
              $category = Categories::where('id','=',$product->category_id)->get();
            $new_product=[];
              foreach($image as $images){
                  $images_of_product= url('public/uploads')."/".$images->image;
                  $image_arrays = array(
                      'image'=>$images_of_product,
                      );
                      $imagess[]=$image_arrays;
              }
              $new_category="";
              foreach($category as $categories){
                  $new_category = $categories->name;
              }
              $product_image = url('public/uploads')."/".$product->image;
              $image_array = array(
                    'image'=>$imagess,
                    'name'=>$product->name,
                    'normal_price'=>$product->normal_price,
                    'discount'=>$product->discount,
                    'main_image'=>$product_image,
                    'video'=>$product->video,
                    'description'=>$product->description,
                    'discount_quantity'=>$product->discount_quantity,
                );
                
                $new_product[]=$image_array;
                $array = array(
                    
                    'category_name'=>$new_category,
                     'provider_id'=>$product->provider_id,
                    'category_id'=>$product->category_id,
                    'products'=>$new_product,
                    );
                $product_data[]=$array;
                
           }
           
        //   //End
          
        $array_gamer = array();

        if(empty($products)){

            $success['error'] =  "No Group Found For This Provider";
            return response()->json(['response_api' => $success], 401);   
        }

         foreach ($products as $value) {

            $product = Product::where('provider_id','=', $value->provider_id)->where('group_id','=', $value->id)->get(); 

            $ab = array(
                'provider_id' => $value->provider_id,
                'name' => $value->name,
                'position' => $value->position,
                'group_id' => $value->id,
                'products' => $product
            ); 

            $array_gamer[]  = $ab;
              
         }

        //  dd($array_gamer);

      $success['status'] =  "success";
      $success['data'] =  $product_data;

      return response()->json(['response_api'=>$success], $this-> successStatus); 

    }


    }

    public function get_provider_food_type(){
        
    }


    public function edit_group_product(Request $request){

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $group_id =  $input['group_id'];
        $name = $input['name'];
        $position = $input['position'];

        $array_insert =  array(
            'name' =>  $name, 
            'position' =>  $position,
        );  

            $product_data = Group_product::find($group_id);
            $product_data->update($array_insert);
          $product = Group_product::find($group_id);

        $success['status'] =  "success";
        $success['data'] =  $product;
  
        return response()->json(['response_api'=>$success], $this-> successStatus); 
    }



     public function delete_group_product($group_id){

        Group_product::where('id', $group_id)->delete();

        $success['status'] =  "success";  
        return response()->json(['response_api'=>$success], $this-> successStatus); 
        
     }
       public function delete_product($product_id){

        $product = Product::where('id', '=',$product_id)->delete();
        if($product){
           $success['status'] =  "success";  
                return response()->json(['response_api'=>$success], $this-> successStatus); 
        }else{
             $success['error'] =  "error in deleting data";  
                return response()->json(['response_api'=>$success], $this-> successStatus); 
        }
        
        
     }



    public function save_product(Request $request){
        
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $provider_id =  $input['provider_id'];
        $group_id = $input['group_id'];
        $name = $input['name'];
        
        $discount = $input['discount'];

        if(isset($input['image'])){
           
        //     $file = base64_decode($input['image']);
        // $folderName = 'public/uploads/';
        // $safeName = rand(1000,9999).'.'.'png';
        // $destinationPath = public_path() . $folderName;
        // $abc = file_put_contents(public_path().'/uploads/'.$safeName, $file);

        // $image = $safeName;
        
        $image = $input['image'];  // your base64 encoded
        $image1=explode("base64,",$image)[1];
        $file = base64_decode($image1);
         
        $safeName =uniqid().'_'.time().'_'.uniqid().'.png';
        $successsss =  file_put_contents(public_path().'/uploads/'.$safeName, $file);
        
        // $image = str_replace('data:image/png;base64,', '', $image);
        // $image = str_replace(' ', '+', $image);
        // $imageName = str_random(10).'.'.'png';
        
        // $img = preg_replace('/^data:image\/\w+;base64,/', '', $image);
        // $type = explode(';', $image)[0];
        // $type = explode('/', $type)[1]; // png or jpg etc

        }else{
            $image = "";
        }
        
        // print_r($image);
        // dd();
        $description = $input['description'];
        
        if(isset($input['variation'])){       
            if(!empty($input['variation'])){
                $variation = implode(',',$input['variation']);
            }else{
                $variation = ""; 
            }
        }else{
          $variation = "";
        }

        if(isset($input['price_variation'])){
            if(!empty($input['price_variation'])){
                $price_variation = implode(',',$input['price_variation']);
            }else{
                $price_variation = ""; 
            }            
        }else{
            $price_variation = "";
        }

        if(isset($input['normal_price'])){
            $normal_price = $input['normal_price'];
        }else{
            $normal_price = "";
        }

        if(isset($input['estimate_time'])){
            $estimate_time = $input['estimate_time'];
        }else{
            $estimate_time = "";
        }

        $array_insert =  array(
            'provider_id' =>  $provider_id, 
            'group_id' =>  $group_id, 
            'name' =>  $name,   
            'price_variation' =>  $price_variation,   
            'discount' =>  $discount,   
            'image' =>  $safeName,   
            'description' =>  $description,   
            'variation' =>   $variation,
            'normal_price' =>  $normal_price, 
            'estimate_time' => $estimate_time,
        );  
        
        $product = Product::create($array_insert);

        $success['status'] =  "success";
        $success['data'] =  $product;
  
        return response()->json(['response_api'=>$success], $this-> successStatus); 
  
    }
    public function save_provider_products(Request $request){
        
        $payload = json_decode(request()->getContent(), true);
    
        $input = $payload;
  
        $provider_id =  $input['provider_id'];
       
        $category_id = $input['category_id'];
        // $group_id = $input['group_id'];
        $name = $input['name'];
        
        $discount = $input['discount'];
        $description = $input['description'];
        $price = $input['normal_price'];
        
        if(isset($input['image'])){
        $image = $input['image']; 
        
        // $image = $request->image;  // your base64 encoded
        
        // $image1 = str_replace("data:image/png;base64,","", $image);
        // $success['status'] =  public_path();
  
        //   echo "helo world ";
        //  if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {  
        //      $extra_name    = uniqid().'_'.time().'_'.uniqid().'.'.$type[1];
        //      $encoded_base64_image = substr($image, strpos($image, ',') + 1);
        //      $resized_image = Image::make($encoded_base64_image);
        //      $encoded_base64_image = substr($image, strpos($image, ',') + 1);
        //      $resized_image->save(public_path().'/uploads/'.$extra_name);
        //   echo "hello";
            
        //  }
       
        $image1=explode("base64,",$image)[1];
         $file = base64_decode($image1);
         
        $safeName =uniqid().'_'.time().'_'.uniqid().'.png';
        $successsss =  file_put_contents(public_path().'/uploads/'.$safeName, $file);
     
     
        // $png_url = "product-".time().".png";
        // $path = public_path().'uploads/' . $png_url;
    
        // Image::make(file_get_contents($file))->save($path); 
    
        // // $image1 = str_replace(' ', '+', $image);
        // $imageName = str_random(10).'.'.'png';
        // \File::put(public_path(). '/uploads/' . $imageName, base64_decode($image1));
        }else{
            $image = "";
        }
        
        if (isset($input['videoLink'])){
            $video=$input['videoLink'];
        }
        else{
            $video="";
        }
        
        // $discount_quantity = $input['discount_quantity'];

        if(isset($input['estimate_time'])){
            $estimate_time = $input['estimate_time'];
        }else{
            $estimate_time = "";
        }

        $array_insert =  array(
            'provider_id' =>  $provider_id, 
            'category_id' =>  $category_id,
            'name' =>  $name,   
            'discount' =>  $discount,   
            'image' =>  $safeName,   
            'video'=>$video,
            'description' =>  $description,
            'normal_price' =>  $price, 
            'estimate_time' => $estimate_time,
        );  
        
        $product = Product::create($array_insert);

        $success['status'] =  "success";
        $success['data'] =  $product;
  
        return response()->json(['response_api'=>$success], $this-> successStatus); 
  
    }

    public function provider_edit_product(Request $request){

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $provider_id =  $input['provider_id'];
        $category_id = $input['category_id'];
        $name = $input['name'];
        $product_id = $input['product_id'];
        $discount = $input['discount'];
        
        
        if(isset($input['image'])){
        $image = $input['image'];  // your base64 encoded
        $image1=explode("base64,",$image)[1];
        $file = base64_decode($image1);
         
        $safeName =uniqid().'_'.time().'_'.uniqid().'.png';
        $successsss =  file_put_contents(public_path().'/uploads/'.$safeName, $file);
        }else{
            $image = "";
        }
           if(isset($input['videoLink'])){
        $video = $input['videoLink'];  // your base64 encoded
        }else{
            $video = "";
        }
       
        $description = $input['description'];
        
        if(isset($input['normal_price'])){
            $normal_price = $input['normal_price'];
        }else{
            $normal_price = "";
        }
 

        if(isset($input['estimate_time'])){
            $estimate_time = $input['estimate_time'];
        }else{
            $estimate_time = "";
        }

        $array_insert =  array(
            'provider_id' =>  $provider_id, 
            'category_id' =>  $category_id, 
            'name' =>  $name,   
            'discount' =>  $discount,   
            'image' =>  $safeName,   
            'video'=>$video,
            'description' =>  $description,  
            'normal_price' =>  $normal_price, 
            'estimate_time' => $estimate_time,
        );  

            $product_data = Product::find($product_id);
            $product_data->update($array_insert);
          $product = Product::find($product_id);

        $success['status'] =  "success";
        $success['data'] =  $product;
  
        return response()->json(['response_api'=>$success], $this-> successStatus); 
  


    }


    public function update_time_management(Request $request){

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        $provider_id = "";

        if(!empty($input['provider_id'])){
            $provider_id = $input['provider_id'];

            if(isset($input['estimate_time'])){
                $estimate_time = $input['estimate_time'];
                $user = Provider_Model::find($provider_id)->update(['estimate_time'=>$estimate_time]);    
              
            }

            if(isset($input['current_status'])){
                $current_status = $input['current_status'];
                $user = Provider_Model::find($provider_id)->update(['current_status'=>$current_status]);    
              
            }
            if(isset($input['under_radius'])){
                $under_radius = $input['under_radius'];
                $user = Provider_Model::find($provider_id)->update(['under_radius'=>$under_radius]);    
              
            }
            if (isset($input['above_radius'])){
                $above_radius = $input['above_radius'];
                 $user = Provider_Model::find($provider_id)->update(['above_radius'=>$above_radius]);  
            }

            if(isset($input['open_time'])){
                $open_time = $input['open_time'];
                $user = Provider_Model::find($provider_id)->update(['open_time'=>$open_time]);    
              
            }
            
            if(isset($input['close_time'])){
                $close_time = $input['close_time'];
                $user = Provider_Model::find($provider_id)->update(['close_time'=>$close_time]);    
              
            }
    
            $update_user = Provider_Model::find($provider_id); 
            $success['status'] =  "success";
            $success['details'] =  $update_user;
            return response()->json(['response_api'=>$success], $this-> successStatus); 
    

        }else{

            $success['error'] =  "Error Occured";
            return response()->json(['response_api' => $success], 401);

        }


   
    }



    public function update_image(Request $request) 
    {
        //         $payload = json_decode(request()->getContent(), true);
        // $input = $payload;
        
        //     $success['status'] =  $input['image'];
        //     $success['status2'] =  $input['provider_id'];
        //     return response()->json(['response_api'=>$success], $this-> successStatus);
        //     die();

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        // $image = "";
        if(isset($input['image'])){
           
        //     $file = base64_decode($input['image']);
        // $folderName = 'public/uploads/';
        // $safeName = rand(1000,9999).'.'.'png';
        // $destinationPath = public_path() . $folderName;
        // $abc = file_put_contents(public_path().'/uploads/'.$safeName, $file);

        // $image = $safeName;
        
        $image = $input['image'];  // your base64 encoded
         $image1=explode("base64,",$image)[1];
        $file = base64_decode($image1);
         
        $safeName =uniqid().'_'.time().'_'.uniqid().'.png';
        $successsss =  file_put_contents(public_path().'/uploads/'.$safeName, $file);
        // $image = str_replace('data:image/png;base64,', '', $image);
        // $image = str_replace(' ', '+', $image);
        // $imageName = str_random(10).'.'.'png';
        
        // $img = preg_replace('/^data:image\/\w+;base64,/', '', $image);
        // $type = explode(';', $image)[0];
        // $type = explode('/', $type)[1]; // png or jpg etc

        }else{
            $image = "";
        }
        
        $provider_id =  $input['provider_id'];
        
        $array_insert =  array(
            'image' =>  $safeName                        
        );

            $user = Provider_Model::find($provider_id);    
            $user->update($array_insert);
            $update_user = Provider_Model::find($provider_id);    

            $success['status'] =  "success";
            $success['details'] =  $update_user;

        return response()->json(['response_api'=>$success], $this-> successStatus);

    }

    public function update_profile(Request $request) 
    {

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        $user_id =  $input['provider_id'];
        $name = $input['name'];
        $phone = $input['mobile'];
        $address = $input['address'];
        $city = $input['city'];
        $country = $input['country'];
        $dis_limit = $input['dis_limit'];
        $lat = $input['lat'];
        $lng = $input['lng'];
        
        $delivery_charges = $input['delivery_charges'];
        $min_shop_price = $input['min_shop_price'];

        if(empty($user_id) || empty($name) ||  empty($phone)  || empty($address) ){
            $success['error'] =  "All Fields Required";

        return response()->json(['response_api'=>$success], 401); 

        }else{

            $array_insert =  array(
                'name' =>  $name,                        
                'phone' =>  $phone,   
                'address' => $address,
                'city' => $city,
                'country' => $country,
                'dis_limit' => $dis_limit,
                'delivery_charges' =>  $delivery_charges,   
                'min_shop_price' => $min_shop_price, 
                'lat' => $lat, 
                'lng' => $lng, 
                
            );
    
                $user = Provider_Model::find($user_id);    
                $user->update($array_insert);
                $update_user = Provider_Model::find($user_id);    
    
                $success['status'] =  "success";
                $success['details'] =  $update_user;
    
            return response()->json(['response_api'=>$success], $this-> successStatus); 

        }
    }



    public function proceed_category(){

        
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $provider_id =  $input['provider_id'];
        $category_id = $input['category_id'];

        $user_provider = Provider_Model::find($provider_id); 

        Provider_Model::find($provider_id)->update(['category_id'=>$category_id]);


        $user_provider = Provider_Model::find($provider_id);

        $success['status'] =  "success";
        $success['data'] =  $user_provider;
  
        return response()->json(['response_api'=>$success], $this-> successStatus); 


    }



    public function save_spin_condition(){

        
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $provider_id = "";
        $end_date = "";
        $from_date = "";
        $min_quantity = "";
        $max_quantity = "";
        $country = "";
        $end_prize = "";
        $from_prize = "";

        if(isset($input['provider_id'])){
            $provider_id = $input['provider_id'];
        }
        if(isset($input['end_date'])){
            $end_date = $input['end_date'];
        }
        if(isset($input['from_date'])){
            $from_date = $input['from_date'];  
        }
        if(isset($input['min_quantity'])){
            $min_quantity = $input['min_quantity'];  
        }
        if(isset($input['max_quantity'])){
            $max_quantity = $input['max_quantity'];  
        }
        if(isset($input['country'])){
            $country = $input['country'];  
        }
        if(isset($input['end_prize'])){
            $end_prize = $input['end_prize']; 
        }
        if(isset($input['from_prize'])){
            $from_prize = $input['from_prize']; 
        }
        $array_insert =  array(
            'provider_id' =>  $provider_id,                        
            'end_date' =>  $end_date,
            'from_date' =>  $from_date,   
            'country' => $country,
            'min_quantity' => $min_quantity,
            'max_quantity' => $max_quantity,
            'end_prize' => $end_prize,                     
            'from_prize' => $from_prize,                     
        );
        

        $provider_condition = Spin_conditions::create($array_insert);
        $success['status'] =  "success";
        $success['data'] =  $provider_condition;  
        return response()->json(['response_api'=>$success], $this-> successStatus); 

    }


     public function get_condition_user(){

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $provider_id = "";
        $condition_id = "";
      

        if(isset($input['provider_id'])){
            $provider_id = $input['provider_id'];
        }
        if(isset($input['condition_id'])){
            $condition_id = $input['condition_id'];
        }

        $spin_condition = "";

        if(empty($condition_id) &&  !empty($provider_id)){
         $spin_condition = Spin_conditions::where('provider_id','=',$input['provider_id'])->first();
        }elseif(!empty($condition_id) &&  !empty($provider_id)){
            $spin_condition = Spin_conditions::where('provider_id','=',$input['provider_id'])->where('id','=',$condition_id)->first();
        }elseif(empty($condition_id) && empty($provider_id)){

            $success['error'] = 'Error Occured, Please Try Again.';
            return response()->json(['response_api'=> $success], 401); 
        }

        $orders = "";
        
        if(!empty($spin_condition->from_date) &&  !empty($spin_condition->end_date) && !empty($spin_condition->country) && !empty($spin_condition->min_quantity) && !empty($spin_condition->max_quantity)  ){

            $orders = Order::whereBetween('created_at', [$spin_condition->from_date, $spin_condition->end_date])->whereBetween('total_quantity', [$spin_condition->min_quantity, $spin_condition->max_quantity])->where('country','=',$spin_condition->country)->where('provider_id','=',$provider_id)->get();
           
        }elseif(empty($spin_condition->from_date) &&  empty($spin_condition->end_date) && !empty($spin_condition->country) && !empty($spin_condition->min_quantity) && !empty($spin_condition->max_quantity) ){

            $orders = Order::whereBetween('total_quantity', [$spin_condition->min_quantity, $spin_condition->max_quantity])->where('country','=',$spin_condition->country)->where('provider_id','=',$provider_id)->get();
            
        }elseif(empty($spin_condition->from_date) &&  empty($spin_condition->end_date) && empty($spin_condition->country) && !empty($spin_condition->min_quantity) && !empty($spin_condition->max_quantity)  ){

            $orders = Order::whereBetween('total_quantity', [$spin_condition->min_quantity, $spin_condition->max_quantity])->where('provider_id','=',$provider_id)->get();
          
        }elseif(empty($spin_condition->from_date) &&  empty($spin_condition->end_date) && empty($spin_condition->country) && empty($spin_condition->min_quantity) && empty($spin_condition->max_quantity) ){
          
            $orders = Order::where('provider_id','=',$provider_id)->get();   
        }elseif(empty($spin_condition->from_date) &&  empty($spin_condition->end_date) && !empty($spin_condition->country) && empty($spin_condition->min_quantity) && empty($spin_condition->max_quantity) ){
           
            $orders = Order::where('country','=',$spin_condition->country)->where('provider_id','=',$provider_id)->get();   
        }elseif(!empty($spin_condition->from_date) &&  !empty($spin_condition->end_date) && empty($spin_condition->country) && empty($spin_condition->min_quantity) && empty($spin_condition->max_quantity)){
          
            $orders = Order::whereBetween('created_at', [$spin_condition->from_date, $spin_condition->end_date])->where('provider_id','=',$provider_id)->get();
        }
        
        
        

        $array_to_send = array();

        if(!empty($orders)){
                        foreach ($orders as $order) {
                            if($order->user_type=="normal_user"){
                                        $user = User::find($order->user_id);
                                            $array_gamer = array(
                                                'user_type' => "normal_user",
                                                'user_name' => $user->name,
                                                'user_id' =>  $user->id,
                                        );
                                        $array_to_send [] = $array_gamer;
                            }else{
                                        $array_gamer = array(
                                            'user_type' => 'guest',
                                            'user_name' => $order->guest_user_name,
                                            'user_id' =>   "",
                                    );
                                    $array_to_send [] = $array_gamer;
                            }            
                        }         
        }

          $success['status'] =  "success";
          $success['data'] =  $array_to_send;
    
          return response()->json(['response_api'=>$success], $this-> successStatus); 

     }


     public function remove_spin_condition($id){

        Spin_conditions::where('provider_id', $id)->delete();

        $success['status'] =  "success";  
        return response()->json(['response_api'=>$success], $this-> successStatus); 
        
     }

     public function provider_group($id){

        $groups = Group_product::where('provider_id', $id)->get();

        $success['status'] =  "success"; 
        $success['data']  = $groups;
        return response()->json(['response_api'=>$success], $this-> successStatus); 

     }

     public function provider_products_group($id,$provider_id){

        $products = Product::where('provider_id', $provider_id)->where('group_id',$id)->get();
        $success['status'] =  "success";
        $success['data']  = $products;
        return response()->json(['response_api'=>$success], $this-> successStatus);
     }



     public function today_report(){

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        $current_date = date("Y-m-d");
        
        // $current_date = $request->date;
        // echo $current_date;
        // die();

        $id = $input['provider_id'];
        $orders = Order::where('provider_id','=',$id)->where("created_at","LIKE","%{$current_date}%")->get();
        // $orders = Order::where('provider_id','=',$id)->where("created_at","=", $current_date)->get();

        $total_cash_delivery_order = 0;
        $total_paid_order = 0;
        $total_orders = 0;

        $total_cash_delivery_order_price = 0;
        $total_paid_order_order_price = 0;
        $all_order_price_total = 0;


        $delivery_cost_cash = 0;
        $delivery_cost_paid = 0;
        $total_delivery_cost = 0;


        $management_cost_cash = 0;
        $management_cost_paid = 0;
        $total_management_cost = 0;


        $net_profit_cash  = 0;
        $net_profit_paid  = 0;
        $all_net_profit = 0;
        $total_item_prodcut = 0;

       
          if(!empty($orders)){
           
            
                foreach($orders as $order){
                    $total_orders = $total_orders+1;
                    $all_order_price_total = $all_order_price_total+$order->total_price;

                    if($order->payment_type=="cash"){
                        $total_cash_delivery_order = $total_cash_delivery_order+1;
                        $total_cash_delivery_order_price = $total_cash_delivery_order_price+$order->total_price;
                        
                        $total_item_prodcut = $total_item_prodcut+$order->total_quantity;
                    }else{
                        $total_paid_order = $total_paid_order+1;
                        $total_paid_order_order_price = $total_paid_order_order_price+$order->total_price;

                        $total_item_prodcut = $total_item_prodcut+$order->total_quantity;
                        
                    }

                }

                $delivery_cost_paid  = $total_paid_order*3;
                $management_cost_paid  = $total_paid_order*1;


                $delivery_cost_cash  = $total_cash_delivery_order*3;
                $management_cost_cash  = $total_cash_delivery_order*1; 


                
                $total_delivery_cost =  $delivery_cost_paid+$delivery_cost_cash;                
                $total_management_cost = $management_cost_paid+$management_cost_cash;


                $net_profit_paid = $total_paid_order_order_price-$delivery_cost_paid-$management_cost_paid;
                $net_profit_cash = $total_cash_delivery_order_price-$delivery_cost_cash-$management_cost_cash;

                $all_net_profit = $net_profit_paid+$net_profit_cash;

          }

          $array_to_send = array(
                'quantity_order_paid' => $total_paid_order,
                'total_price_order_paid' => $total_paid_order_order_price,
                'delivery_price_order_paid' => $delivery_cost_paid,
                'management_price_order_paid' => $management_cost_paid,
                'net_profit_paid' => $net_profit_paid,

                'quantity_order_cash' => $total_cash_delivery_order,
                'total_price_order_cash' => $total_cash_delivery_order_price,
                'delivery_price_order_cash' => $delivery_cost_cash,
                'management_price_order_cash' => $management_cost_cash,
                'net_profit_cash' => $net_profit_cash,


                'total_orders' => $total_orders,
                'all_orders_price' => $all_order_price_total,
                'all_delivery_price' => $total_delivery_cost,
                'all_management_price' => $total_management_cost,
                'all_net_profit' => $all_net_profit,

                'total_item_prodcut' => $total_item_prodcut,
          );
          



        
        $success['status'] =  "success";
        $success['data']  = $array_to_send;
        return response()->json(['response_api'=>$success], $this-> successStatus); 
        
     }



    public function get_countries(){
        
            $countries = Countries::selectRaw('country')                                                  
                                   ->groupBy('country')->get();

            $success['countries'] = $countries;

            return response()->json(['response_api'=>$success], $this-> successStatus); 
    }

    public function get_city($name){

        $cities = Countries::where('country','=',$name)->get();

        $success['cities'] =    $cities;

        return response()->json(['response_api'=>$success], $this-> successStatus); 

    }



    public function clear_cache(){

          Artisan::call('config:cache');
           Artisan::call('route:clear');
           Artisan::call('config:clear ');
         

           echo "<h2>Cache Cleared now</h2>";
           exit;

    }


    public function get_deals($lat, $lon){
    
        if(!empty($lat) && !empty($lon) ){
            // $providers = Provider_Model::where('city','=',$city)->get();
        $providers = DB::table('deals as d')
                            ->leftjoin('provider_records as pr', 'pr.id', '=', 'd.provider_id')
                            ->select('pr.*')
                            // ->where('pr.city', '=' ,$city)
                            ->where('pr.status', '=' , "Active")
                            ->where('d.status', '=' , "Active")
                            ->groupBy('d.provider_id')
                            ->get();
        }
    
        // $t2 = 0;
        if ($providers->isNotEmpty()) {
            $markers = collect($providers)->map(function ($item) use ($lat, $lon) {
                $item->distance = calculateDistanceBetweenTwoAddresses($item->lat, $item->lng, $lat, $lon);
            });
        }

        // $providers = $providers->where('distance', '<=', 'dis_limit');
        // $t1= [];
        // foreach ($providers as $provider) {
        //     $provider_gamer = array(
        //         'id' => $provider->id,
        //         'dis_limit' => $provider->dis_limit,
        //     );
        //     $t1[] =  $provider_gamer;
        // }
        
        // $success['data'] = $t1;
        //  return response()->json(['response_api'=>$success], $this-> successStatus); 
        // die();

         $provider_array_send = [];
$new_product = [];

         foreach ($providers as $provider) {
            // print_r($provider->distance . " " . $provider->dis_limit . " &    ");
            $provider_img = "";
            if($provider->distance <= $provider->dis_limit){
                $prducts = Product::where('provider_id','=',$provider->id)->get();
                foreach($prducts as $product){
                    $product_image = url('public/uploads')."/".$product->image;
                    $image_array = array(
                    // 'image'=>$imagess,
                    'name'=>$product->name,
                    'normal_price'=>$product->normal_price,
                    'discount'=>$product->discount,
                    'main_image'=>$product_image,
                    'video'=>$product->video,
                    'description'=>$product->description,
                    'discount_quantity'=>$product->discount_quantity,
                );
                
                $new_product []=$image_array;
                }
                 
                $category = Categories::where('id','=',$provider->category_id)->get();
                $category_name="";
                foreach($category as $categories){
                    $category_name=$categories->name;
                }
                if(!empty($provider->image)){
                    $provider_img = url('public/uploads')."/".$provider->image;
                    // $provider_img = $provider->image;
                }else{
                    $provider_img = "https://chopal.in/wp-content/uploads/2019/10/image-696x392.jpg";
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
                        'category_id'=>$provider->category_id,
                        'category_name'=>$category_name,
                        'products'=>$new_product,
                        
                    );
                    $provider_array_send[] =  $provider_gamer;
            }
        }
        // dd($provider_array_send);
            // die();

         $success['data'] = $provider_array_send;
         return response()->json(['response_api'=>$success], $this-> successStatus); 
    }



// 24.9199502/67.1010534

        public function get_curr_loc_data(Request $request){
            $payload = json_decode(request()->getContent(), true);
            $data = $payload;
            
            // $latitude =  $request->latitude;
            // $longitude = $request->longitude;
            
            $latitude =  $data['latitude'];
            $longitude = $data['longitude'];
            
             // $stores = Provider_Model::where('status', 'Active');
            $content = Provider_Model::where('status', 'Active');
            $content = $content->get();
            
            if ($content->isNotEmpty()) {
                $markers = collect($content)->map(function ($item) use ($latitude, $longitude) {
                    $item['distance'] = calculateDistanceBetweenTwoAddresses($item->lat, $item->lng, $latitude, $longitude);
                });
            }
            $content = $content->where('distance', '<=', 3*6371);
            $success['data'] = $content;
            return response()->json(['response_api'=>$success], $this-> successStatus); 
        }


    public function save_deals(Request $request) 
    { 
        // $otp = rand(100000,999999);
        $payload = json_decode(request()->getContent(), true);
        $data = $payload;
        
        $provider_id =  $data['provider_id'];
        
        // $deal_id1 = DB::table('deals')
        //           ->select('id')
        //           ->where('provider_id', '=' ,$provider_id)
        //           ->latest()
        //           ->first();
                   
        // $deal_id =  $deal_id1->id;
        // echo $deal_id;
        
        // die();
        
        $name =  $data['name'];

        if(isset($data['image'])){
            $image = $data['image'];
             $image1=explode("base64,",$image)[1];
        $file = base64_decode($image1);
         
        $safeName =uniqid().'_'.time().'_'.uniqid().'.png';
        $successsss =  file_put_contents(public_path().'/uploads/'.$safeName, $file);
        }else{
            $image = "";
        }
        $price =  $data['price'];
        $end_date =  $data['end_date'];
        
        $array_insert =  array(
                'provider_id' => $provider_id,
                'name' => $name,
                'image' => $safeName,
                'price' => $price,
                'end_date' => $end_date,
                'status' => 'Active'
        );
        
        $deal =  Deals::create($array_insert);
        
        
        
        $deal_id1 = DB::table('deals')
                   ->select('id')
                   ->where('provider_id', '=' ,$provider_id)
                   ->latest()
                   ->first();
                   
        $deal_id =  $deal_id1->id;
        $item_name =  $data['item_name'];
        
        $array_insert2 =  array(
                'deal_id' => $deal_id,
                'item_name' => $item_name,
                'status' => 'Active'
        );

        $deal2 =  Deal_items::create($array_insert2);
        
        $success['data'] = $deal;
        $success['data2'] = $deal2;
        return response()->json(['response_api'=>$success], $this-> successStatus); 
    } 
    
    
    // public function save_deal_items(Request $request) 
    // { 
    //     $payload = json_decode(request()->getContent(), true);
    //     $data = $payload;
        
    //     $deal_id =  $data['deal_id'];
    //     $item_name =  $data['item_name'];

    //     $array_insert =  array(
    //             'deal_id' => $deal_id,
    //             'item_name' => $item_name,
    //             'status' => 'Active'
    //     );

    //     $deal =  Deal_items::create($array_insert);
    //     $success['data'] = $deal;
    //     return response()->json(['response_api'=>$success], $this-> successStatus); 
    // }


    public function provider_edit_deal(Request $request){

        $payload = json_decode(request()->getContent(), true);
        $data = $payload;
        
        $deal_id =  $data['deal_id'];
        // $product_group_id =  $data['product_group_id'];
        // $p_products_id =  $data['p_products_id'];
        // $cat_id =  $data['cat_id'];
        $name =  $data['name'];
        
        if(isset($data['image'])){
            $image = $data['image'];
             $image1=explode("base64,",$image)[1];
        $file = base64_decode($image1);
         
        $safeName =uniqid().'_'.time().'_'.uniqid().'.png';
        $successsss =  file_put_contents(public_path().'/uploads/'.$safeName, $file);
        }else{
            $image = "";
        }
        $price =  $data['price'];
        $end_date =  $data['end_date'];
        
        $status = $data['status'];     
        
        $array_insert =  array(
                'name' => $name,
                'image' => $safeName,
                'price' => $price,
                'end_date' => $end_date,
                'status' => $status
        );

        $deal_data = Deals::find($deal_id);
        $deal_data->update($array_insert);
        $deal = Deals::find($deal_id);

        $success['status'] =  "success";
        $success['data'] =  $deal;
  
        return response()->json(['response_api'=>$success], $this-> successStatus);
    }



    // public function get_deals($city){

    
    //     if(!empty($city)){
    //         // $providers = Provider_Model::where('city','=',$city)->get();
    //     $providers = DB::table('deals as d')
    //                         ->leftjoin('provider_records as pr', 'pr.id', '=', 'd.provider_id')
    //                         ->select('pr.*')
    //                         ->where('pr.city', '=' ,$city)
    //                         ->where('pr.status', '=' , 'Active')
    //                         ->where('d.status', '=' , 'Active')
    //                         ->groupBy('d.provider_id')
    //                         ->get();
    //     }

    //      $provider_array_send = [];

    //      foreach ($providers as $provider) {
    //         // echo count($providers);
    //         // die();
    //         $provider_img = "";

    //         if(!empty($provider->image)){
    //             // $provider_img = url('public/uploads')."/".$provider->image;
    //             $provider_img = $provider->image;
    //         }else{
    //             $provider_img = "https://chopal.in/wp-content/uploads/2019/10/image-696x392.jpg";
    //         }

    //             $current_time = date('h:i a');
    //             $provider_gamer = array(
    //                 'provider_name' => $provider->name,
    //                 'estimate_time' => $provider->estimate_time,
    //                 'current_status' => $provider->current_status,
    //                 'provider_id' => $provider->id,
    //                 'provider_image' => $provider_img,                
    //                 'provider_lat' => $provider->lat,
    //                 'provider_lng' => $provider->lng,                                
    //                 'provider_address' => $provider->address, 
    //                 'provider_food_type' => $provider->food_type, 
    //                 'delivery_charges' => $provider->delivery_charges, 
    //                 'min_shop_price' => $provider->min_shop_price,
    //                 'current_server_time' => $current_time,
    //                 'server_time' => $current_time,
    //             );
    
    //             $provider_array_send[] =  $provider_gamer;
    //     }

    //      $success['data'] = $provider_array_send;
    //      return response()->json(['response_api'=>$success], $this-> successStatus); 
    // }
    
    
    public function delete_deal($id){
        // $driver = Driver::find($id);
        // $driver->status = 'InActive';
        // $driver->update();
        Deals::where('id', $id)->delete();
        $success['status'] =  "success";
        return response()->json(['response_api'=>$success], $this-> successStatus);

    }
    
    
    
 
    
    public function delete_deal_items($id){
        // $driver = Driver::find($id);
        // $driver->status = 'InActive';
        // $driver->update();
        Deal_items::where('id', $id)->delete();
        $success['status'] =  "success";
        return response()->json(['response_api'=>$success], $this-> successStatus);
    }
    
    public function provider_edit_deal_items(Request $request){

        $payload = json_decode(request()->getContent(), true);
        $data = $payload;
        
        $deal_item_id =  $data['deal_item_id'];
        // $deal_id =  $data['deal_id'];
        // $product_group_id =  $data['product_group_id'];
        // $p_products_id =  $data['p_products_id'];
        // $cat_id =  $data['cat_id'];
        $item_name =  $data['item_name'];
        
        // if(isset($data['image'])){
        //     $image = $data['image'];
        // }else{
        //     $image = "";
        // }
        // $price =  $data['price'];
        // $end_date =  $data['end_date'];
        
        $status = $data['status'];        
        
        $array_insert =  array(
                'item_name' => $item_name,
                'status' => $status
        );

        $deal_data = Deal_items::find($deal_item_id);
        $deal_data->update($array_insert);
        $deal = Deal_items::find($deal_item_id);

        $success['status'] =  "success";
        $success['data'] =  $deal;
  
        return response()->json(['response_api'=>$success], $this-> successStatus);
    }

    
    public function get_provider_deals($provider_id){

    //     $deals = DB::table('daily_deals')
    //                         ->leftjoin('categories as cat', 'cat.id', '=', 'daily_deals.cat_id')
    //                         ->leftjoin('product_groups as pg', 'pg.id', '=', 'daily_deals.product_group_id')
    //                         ->leftjoin('provider_records as pr', 'pr.id', '=', 'pg.provider_id')
    //                         ->select('daily_deals.id as daily_deals_id', 'pr.name as pr_name', 'pr.id as pr_id', 'pg.name as pg_name', 'pr.city', 'pr.address', 'cat.name as cat_name', 'daily_deals.name', 'daily_deals.image', 'daily_deals.price', 'daily_deals.del_price', 'daily_deals.status')
    //                         ->where('pr.id', '=' ,$provider_id)
    //                         ->get();
        
    //       $img_path = [];
    
    //       foreach ($deals as $dd) {
    //         $path = url('public/uploads')."/".$dd->image;
    //         $array_insert =  array(
    //             'image' =>  $path, 
    //         );
    //         $img_path []  =  $array_insert;
    //       }

    //   $products = Group_product::where('provider_id','=',$provider_id)->orderBy('position','asc')->get();

    //     $array_gamer = array();

    //      foreach ($products as $value) {

    //         $product = Product::where('provider_id','=', $value->provider_id)->where('group_id','=', $value->id)->get(); 

    //         $ab = array(
    //             'provider_id' => $value->provider_id,
    //             'name' => $value->name,
    //             'position' => $value->position,
    //             'group_id' => $value->id,
    //             'products' => $product
    //         ); 

    //         $array_gamer[]  = $ab;
    //      }

    //   $success['status'] =  "success";
    //   $success['data'] =    $deals;
    //   $success['data2'] =  $array_gamer;
    
    
    $Deals = Deals::where('provider_id','=',$provider_id)->get();
      $success['data'] =  $Deals;
        return response()->json(['response_api'=>$success], $this-> successStatus); 
        
    }
    
    
    public function get_provider_deal_items($deal_id){

    $Deal_items = Deal_items::where('deal_id','=',$deal_id)->get();
      $success['data'] =  $Deal_items;
        return response()->json(['response_api'=>$success], $this-> successStatus); 
        
    }
    
    public function get_deal_products($provider_id){

        $products = DB::table('deals as d')
                            // ->leftjoin('product_groups as pg', 'pg.id', '=', 'daily_deals.product_group_id')
                            ->leftjoin('provider_records as pr', 'pr.id', '=', 'd.provider_id')
                            ->select('d.id as dd_id', 'pr.id as pr_id', 'd.name as dd_name', 'd.image as dd_image', 'd.price as dd_price', 'd.end_date as dd_end_date')
                            ->where('pr.id', '=', $provider_id)
                            ->where('d.status', '=', 'Active')
                            ->get();
        // $products = Group_product::where('provider_id','=',$provider_id)->orderBy('position','asc')->get();
        $array_gamer = array();
        
        $current_date = date("Y-m-d");
        // $end_date = $products->end_date;
        
        // if($current_date >= $end_date)
        // {
             foreach ($products as $value) {
                $end_date = $value->dd_end_date;
                if($current_date <= $end_date)
                {
                    
                    // $product = Product::where('provider_id','=', $value->provider_id)->where('group_id','=', $value->pg_id)->get(); 
                    $product = DB::table('deal_items as di')
                                    ->leftjoin('deals as d', 'd.id', '=', 'di.deal_id')
                                    ->leftjoin('provider_records as pr', 'pr.id', '=', 'd.provider_id')
                                    // ->select('pp.name', 'pp.price_variation', 'pp.normal_price', 'pp.discount', 'pp.image', 'pp.description', 'pp.variation', 'pp.status', 'pp.estimate_time', 'pp.created_at', 'pp.updated_at', 'pp.discount_price', 'pp.discount_quantity')
                                    ->select('di.deal_id as id', 'di.item_name as name', 'd.price as normal_price')
                                    ->where('di.deal_id', '=', $value->dd_id)
                                    ->where('di.status', '=', 'Active')
                                    ->get();
                                    
                    $ab = array(
                        // 'provider_id' => $value->pr_id,
                        'id' => $value->dd_id,
                        'name' => $value->dd_name,
                        'image' => $value->dd_image,
                        'normal_price' => $value->dd_price,
                        'end_date' => $value->dd_end_date,
                        'products' => $product
                    ); 
                    $array_gamer[]  = $ab;
                }
                else{
                    $array_insert =  array(
                        'status' => 'InActive'
                    );
                    $dls = Deals::find($value->dd_id);
                    $dls->update($array_insert);
                }
             }
             
            $success['data'] =    $array_gamer;
            return response()->json(['response_api'=>$success], $this-> successStatus); 
        // }

    }
    
    
    public function get_reported_orders($id){

        $reports = DB::table('orders as or')
                            ->leftjoin('users as u', 'u.id', '=', 'or.user_id')
                            ->leftjoin('drivers as d', 'd.id', '=', 'or.driver_id')
                            ->leftjoin('provider_records as pr', 'pr.id', '=', 'or.provider_id')
                            ->select('or.id', 'report_desc', 'd.name as d_name', 'd.id as d_id', 'u.name as u_name', 'u.mobile as u_mobile','or.order_status as order_status')
                            ->where('pr.id', '=' ,$id)
                            ->get();

        //   $img_path = [];

        //   foreach ($deals as $dd) {
        //     $path = url('public/uploads')."/".$dd->image;
        //     $array_insert =  array(
        //         'image' =>  $path, 
        //     );
        //     $img_path []  =  $array_insert;
        //   }
        //   $success['img_path'] =  $img_path;

        $success['data'] =    $reports;
        return response()->json(['response_api'=>$success], $this-> successStatus);
    }
    
    // public function save_provider_coupon(Request $request){

    //     $payload = json_decode(request()->getContent(), true);
    //     $input = $payload;

    //     $provider_id = $input['provider_id'];
    //     $coupon_num = $input['coupon_num'];
    //     $price  = $input['price'];
    //     $start_date = $input['start_date'];
    //     $end_date = $input['end_date'];

    //     $array_insert =  array(
    //         'provider_id' =>  $provider_id, 
    //         'coupon_num' => $coupon_num,
    //         'price' => $price,
    //         'start_date' => $start_date,
    //         'end_date' => $end_date,
    //         'status' => 'Active' 
    //     );

    //     Reviews::create($array_insert);
    //     $success['status'] = "success";
        
    //  return response()->json(['response_api'=>$success], $this-> successStatus); 
    //  }   
    
    
    public function edit_user_reviews(Request $request){

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        
        $review_id = $input['review_id'];
        // $provider_id = $input['provider_id'];
        // $user_id = $input['user_id'];
        // $rating = $input['rating'];
        // $reviews  = $input['reviews'];
        $status  = $input['status'];

        $array_insert =  array(
            // 'provider_id' =>  $provider_id,
            // 'user_id' =>  $user_id,
            // 'rating' => $rating,
            // 'reviews' => $reviews,
            'status' => $status
        );

        $review = Reviews::find($review_id);
        $review->update($array_insert);
        $review_user = Reviews::find($review_id);

        $success['status'] =  "success";
        $success['details'] =  $review_user;
        return response()->json(['response_api'=>$success], $this-> successStatus); 
     } 
     
     
     public function get_provider_reviews($provider_id){

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $review = Reviews::where('provider_id', '=', $provider_id)->get();
        
        // if(!empty($review[0]->rating)){
            $success['data'] = $review;
            return response()->json(['response_api'=>$success], $this-> successStatus);   
        // }
        // else{
        //     $success['status'] = "Error";
        //     return response()->json(['response_api'=>$success], $this-> successStatus);        
        // }
     }  
     
     public function remove_user_reviews($id){

        Reviews::where('id', $id)->delete();
        $success['status'] =  "success";  
        return response()->json(['response_api'=>$success], $this-> successStatus); 
        
     }
     
  public function save_coupon(Request $request) 
    { 
        // $otp = rand(100000,999999);
        $payload = json_decode(request()->getContent(), true);
        $data = $payload;
        
        $c_number =  $data['c_number'];
        $c_price =  $data['c_price'];
        $provider_id =  $data['provider_id'];
        $start_date =  $data['start_date'];
        $end_date =  $data['end_date'];
        
        // $current_date = date("Y-m-d");

        $array_insert =  array(
                'c_number' =>  $c_number,
                'c_price' =>  $c_price,
                'provider_id' => $provider_id,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'status' => 'Active'
        );
        $coupon =  Coupons::create($array_insert);
        $success['data'] = $coupon;
        return response()->json(['response_api'=>$success], $this-> successStatus); 
    } 
    
    public function get_coupon($provider_id){
        
        $current_date = date("Y-m-d");
        $coupons = Coupons::where('provider_id','=',$provider_id)->get();
       
        
        foreach ($coupons as $cou) {
            $end_date = $cou->end_date;
            if($current_date > $end_date)
            {
                $array_insert =  array(
                    'status' =>  'Expired',
                );
                // $success['data'] =    $array_insert;
                // return response()->json(['response_api'=>$success], $this-> successStatus); 
                
                DB::table('coupons')
                    ->where('provider_id', $provider_id)
                    ->update($array_insert);
                    
                // $Coupon = Coupons::find($provider_id);   
                // $Coupon->update($array_insert);
            }
        }
        $coupons2 = Coupons::where('provider_id','=',$provider_id)->get();

        $success['data'] =    $coupons2;
        return response()->json(['response_api'=>$success], $this-> successStatus); 
    }
     
     public function remove_coupon($id){
        Coupons::where('id', $id)->delete();
        $success['status'] =  "success";
        return response()->json(['response_api'=>$success], $this-> successStatus); 
     }
     
    public function edit_coupon(Request $request){

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        
        $coupon_id = $input['coupon_id'];
        $c_price  = $input['c_price'];
        $end_date  = $input['end_date'];
        $status  = $input['status'];
        
        $array_insert =  array(
            'c_price' => $c_price,
            'end_date' => $end_date,
            'status' => $status
        );

        $coupon = Coupons::find($coupon_id);
        $coupon->update($array_insert);
        $coupon_data = Coupons::find($coupon_id);

        $success['status'] =  "success";
        $success['data'] =  $coupon_data;
        return response()->json(['response_api'=>$success], $this-> successStatus); 
     } 
     
     
    // public function test_noti(){
    //     $access_token = 'AAAA6zM-jhM:APA91bEhsArZtdCZhdsY3EuabN2AMA2ZLtgXsOYPXOE09OugZ4y03GisEycgFXqrZb1R4VSK44TEvgzJw-JMH7x2UcY08igoHY11fPZfyRbwwHupTLhuGGSciGj4BQrE_cjDrAqp2Rs2';
    //     $reg_id = 'dy7wDPd5zKw:APA91bHROWid-DyZ3tJK_-CYhAzAYyYgvPQyHaMnXccg54eKfD9-19tBqF5VIv4qw6hcVt9wVMUuQkPhXwYBT5VdOTL9bRGK2KVac1kbtBom-xhwKtSpDarvvpw4TZjRwlyh_95uHVgh';
    //     $message = [
    //     'notification' => [
    //     'title' => 'Test Message',
    //     'body' => "This is a test!"
    //     ],
    //     'to' => $reg_id
    //     ];
    //     $client = new GuzzleHttp\Client([
    //     'headers' => [
    //     'Content-Type' => 'application/json',
    //     'Authorization' => 'key='.$access_token,
    //     ]
    //     ]);
    //     $response = $client->post('https://fcm.googleapis.com/fcm/send',
    //     ['body' => json_encode($message)]
    //     );
    //     echo $response->getBody();
    //  } 
    
    

// Routes


    public function add_route_name(Request $request){
        
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $provider_id =  $input['provider_id'];
        $name = $input['name'];

        $array_insert =  array(
            'provider_id' =>  $provider_id, 
            'name' =>  $name,   
        );  
        
        $routes_cat = Route_categories::create($array_insert);

        $success['status'] =  "success";
        $success['data'] =  $routes_cat;
  
        return response()->json(['response_api'=>$success], $this-> successStatus); 
    }
    
    public function get_route_name(Request $request){
        
        $payload = json_decode(request()->getContent(), true);
        $data = $payload;
        
        $provider_id =  $data['provider_id'];
            $route_cat = DB::table('route_categories as rc') 
                        ->select('*')
                        ->where('provider_id', '=', $provider_id)
                        ->get();

            $success['data'] = $route_cat;
            return response()->json(['response_api'=>$success], $this-> successStatus); 
    }



     public function edit_route_name(Request $request)
     {
        $payload = json_decode(request()->getContent(), true);
        $data = $payload;
        
        $r_cat = Route_categories::find($data["rc_id"]);
        $r_cat->name = $data["name"];
        $r_cat->update();
        $success['status'] = 'success';
        return response()->json(['response_api'=>$success], $this-> successStatus); 
     }
     
     public function delete_route_name($rc_id){
        Route_categories::where('id', $rc_id)->delete();

        $success['status'] =  "success";  
        return response()->json(['response_api'=>$success], $this-> successStatus); 
     }

     

    public function add_route(Request $request){
        
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $provider_id =  $input['provider_id'];
        $route_category_id =  $input['route_category_id'];
        // $service_type = $input['service_type'];
        // $time =  $input['time'];
        $address = $input['address'];
        // $company_name =  $input['company_name'];
        // $driver_id = $input['driver_id'];
        // $timeout =  $input['timeout'];
        // $sequence = $input['sequence'];
        $location_name = $input['location_name'];


        $array_insert =  array(
            'provider_id' =>  $provider_id, 
            'route_category_id' =>  $route_category_id,
            // 'service_type' =>  $service_type,   
            // 'time' =>  $time, 
            'address' =>  $address,   
            // 'company_name' =>  $company_name, 
            // 'driver_id' =>  $driver_id,   
            // 'timeout' =>  $timeout, 
            // 'sequence' =>  $sequence,   
            'location_name' =>  $location_name,   
        );  
        
        $routes = Routes::create($array_insert);
        $success['status'] =  "success";
        $success['data'] =  $routes;
  
        return response()->json(['response_api'=>$success], $this-> successStatus); 
    }

    public function get_route(Request $request){
        $payload = json_decode(request()->getContent(), true);
        $data = $payload;
        
        // dd($data);
        $provider_id =  $data['provider_id'];
        $route = DB::table('routes as r')
                    ->leftjoin('route_categories as rc', 'rc.id', '=', 'r.route_category_id')
                    ->select('r.*','rc.name')
                    ->where('r.provider_id', '=', $provider_id)
                    ->get();
                    
        // return($provider_id);
        // $t1 = $route_cat[0]->name;
        // print_r($t1);
        // die();

      $array_r = [];

      foreach ($route as $val) {
         $driname = Driver::where('id', '=', $val->driver_id)->select('name')
                    ->first();
            // dd($driname);
        $array_insert =  array(
            'id' =>  $val->id,
            'provider_id' =>  $val->provider_id,
            'service_type' =>  $val->service_type,
            'time' =>  $val->time,
            'address' =>  $val->address,
            'company_name' =>  $val->company_name,
            'driver_id' =>  $val->driver_id,
            'driver_name' =>  $driname['name'],
            'timeout' =>  $val->timeout,
            'sequence' =>  $val->sequence,
            'route_category_id' =>  $val->route_category_id,
            'route_category_name' =>  $val->name,
            'location_name' =>  $val->location_name,
            );
            $array_r[]  =  $array_insert;
      }
        $success['data'] = $array_r;
        // $success['data2'] = $route_cat;
        return response()->json(['response_api'=>$success], $this-> successStatus); 
    }

    //  public function edit_route(Request $request)
    //  {
    //     $payload = json_decode(request()->getContent(), true);
    //     $data = $payload;
        
    //     $c_id =  $input['c_id'];
    //     $provider_id =  $input['provider_id'];
    //     $service_type = $input['service_type'];
    //     $time =  $input['time'];
    //     $address = $input['address'];
    //     $company_name =  $input['company_name'];
    //     $driver_id = $input['driver_id'];
    //     $timeout =  $input['timeout'];
    //     $sequence = $input['sequence'];
    //     $route_category_id =  $input['route_category_id'];
    //     $location_name = $input['location_name'];

    //     $cat = Routes::find($c_id);
    //     $cat->provider_id = $provider_id;
    //     $cat->service_type = $service_type;
    //     $cat->time = $time;
    //     $cat->address = $address;
    //     $cat->company_name = $company_name;
    //     $cat->driver_id = $driver_id;
    //     $cat->timeout = $timeout;
    //     $cat->sequence = $sequence;
    //     $cat->route_category_id = $route_category_id;
    //     $cat->location_name = $location_name;
        
    //     $cat->update();
    //     $success['status'] = 'success';
    //     return response()->json(['response_api'=>$success], $this-> successStatus); 
    //  }
     
     
     public function delete_route($r_id){
        Routes::where('id', $r_id)->delete();
        $success['status'] =  "success";  
        return response()->json(['response_api'=>$success], $this-> successStatus); 
     }
     
     
     public function use_route(Request $request)
     {
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        
        $c_id =  $input['c_id'];
    //     $provider_id =  $input['provider_id'];
        // $route_category_id =  $input['route_category_id'];
        $service_type = $input['service_type'];
        $time =  $input['time'];
        $address = $input['address'];
        // $company_name =  $input['company_name'];
        $driver_id = $input['driver_id'];
        // $timeout =  $input['timeout'];
        $sequence = $input['sequence'];
    //     $location_name = $input['location_name'];
        $status = $input['status'];
    

        $cat = Routes::find($c_id);
    //     $cat->provider_id = $provider_id;
        $cat->service_type = $service_type;
        $cat->time = $time;
        $cat->address = $address;
        // $cat->company_name = $company_name;
        $cat->driver_id = $driver_id;
        // $cat->timeout = $timeout;
        $cat->sequence = $sequence;
        // $cat->route_category_id = $route_category_id;
        $cat->status = $status;
        
        $cat->update();
        $success['status'] = 'success';
        return response()->json(['response_api'=>$success], $this-> successStatus); 
     }
     
    
    
    
    public function stripe_acc(Request $request)
    {
        // echo "test";
        // die();
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        
        $amount = $input['amount'];
        $currency = $input['currency'];
        $s_token = $input['s_token'];
        
        $amountz = $amount * 100;
        
        try {
            Stripe::setApiKey('sk_test_lpu8QKQa2h5XPTNG6SAp0ItJ00oUPx8Anq');
            // $customer = Customer::create(array(
            //     'email' => $request->stripeEmail,
            //     'source' => $request->stripeToken
            // ));
        
            $charge = Charge::create([
                'amount' => $amountz,
                'currency' => $currency,
                'source' => $s_token,
                'description' => 'Order Payment',
                // 'receipt_email' => $request->input('email') ?? '',

            ]);
            
        $success['data'] = "Charge successful, you get the order!";
        return response()->json(['response_api'=>$success], $this-> successStatus); 
        
            // return 'Charge successful, you get the order!';
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }
    
    
    
     
     public function new_chat(Request $request){
        // Chat::where('id', $r_id)->delete();
        // return $request;
        // Chat::create($request->all());
        
        
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        
        // return $request;
        Chat::create([
                'rider_id' => $input['rider_id'],
                'user_id' => $input['user_id'],
                'order_id' => $input['order_id'],
                'rider_message' => $input['rider_message'],
                'user_message' => $input['user_message'],
            ]);
        
        $success['status'] =  "success";  
        return response()->json(['response_api'=>$success], $this-> successStatus); 
     }
     
     
     
     public function get_chat($id){
         
        $chat = Chat::where('order_id','=',$id)->get();
        // dd($chat);
        $success['chat'] =  $chat;  
        $success['status'] =  "success";  
        return response()->json(['response_api'=>$success], $this-> successStatus); 
     }
     
     
     
     public function get_order_of_driver($id){
         
        $order = Order::join('users', 'users.id', '=', 'orders.user_id')->where('driver_id','=',$id)->select('user_id', 'orders.id as order_id', 'mobile')->get();
        // dd($chat);
        $success['order'] =  $order;  
        $success['status'] =  "success";  
        return response()->json(['response_api'=>$success], $this-> successStatus); 
     }
     
    
    
    
    
}