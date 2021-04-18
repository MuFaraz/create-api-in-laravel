<?php

namespace App\Http\Controllers\API;


use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\Provider_Model; 
use App\Countries;
use App\Product;
use App\Driver;
use App\Order;
use App\Categories;
use App\Group_product;
use Hash;
use Validator;
use DB;
use Artisan;
use Redirect;
use Mail;
use GuzzleHttp;

class DriverController extends Controller 
{

public $successStatus = 200;

    public function login(){ 
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        $email = $input['email'];
        $password_u = $input['password'];
        $password_right = false;
        $device_id =  $input['device_id'];
       
        
        $user_provider = Driver::where(['email' => $email])->first();  
        
         $driver_id = $user_provider->id;
        if(empty($user_provider)){
            $success['error'] = 'Email or Password Incorrect';
            return response()->json(['response_api'=> $success], 401); 
        }

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
                    'device_id' =>  $device_id,                        
                );
                
                $driver = Driver::find($driver_id);    
                $driver->update($array_insert);
                
                
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
     

    public function register(Request $request) 
    { 
        $otp = rand(100000,999999);
        $payload = json_decode(request()->getContent(), true);
        $data = $payload;
        $route_category_id =  $data['route_category_id'];
        $name =  $data['name'];
        $email =  $data['email'];

        if(isset($data['provider_id'])){
            $provider_id =  $data['provider_id'];            
        }else{
            $provider_id =  "";            
        }
        
    
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

        $lat = $data['lat'];
        $lng = $data['lng'];
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


      $array_insert =  array(
                'name' =>  $name, 
                'email' =>  $email, 
                'password' =>  $password,                
                'phone' => $mobile,
                'image' => $image,
                'address' => $address,
                'country' => $country,
                'city' => $city,
                'route_category_id' => $route_category_id,
                'lat' => $lat,
                'lng' => $lng,
                'provider_id' => $provider_id, 
                'status' => 'Active'
      );


          $user = Driver::where('email', '=', $data['email'])->first();    
        if(empty($user)){
        $user =  Driver::create($array_insert);

        $success['data'] = $user;
        


    //     $headers = "From: copyrightsdetect@copyrightsdetectives.com" . "\r\n" .
    //     "CC:  qa.ms2018@gmail.com";
    // mail($email,"User Confirmation Hadool","Please Enter this otp code \n".$otp,$headers);


        return response()->json(['response_api'=>$success], $this-> successStatus); 

        }else{

            $success['error'] = "email already exist";
            return response()->json(['response_api'=>$success],401);

        }

    }  // esle finished 


}



 public function provider_edit_driver(Request $request){

        $otp = rand(100000,999999);
        $payload = json_decode(request()->getContent(), true);
        $data = $payload;
        $driver_id =  $data['driver_id'];
        $name =  $data['name'];
        $email =  $data['email'];
        $provider_id =  $data['provider_id'];  
        $address =  $data['address'];
        $country =  $data['country'];
        $city =  $data['city'];

        // if(isset($data['provider_id'])){
        //     $provider_id =  $data['provider_id'];            
        // }else{
        //     $provider_id =  "";            
        // }
        // if(isset($data['address'])){
        //     $address =  $data['address'];            
        // }else{
        //     $address =  "";            
        // }
        // if(isset($data['country'])){
        //     $country =  $data['country'];            
        // }else{
        //     $country =  "";            
        // }
        // if(isset($data['city'])){
        //     $city =  $data['city'];            
        // }else{
        //     $city =  "";            
        // }
        
        if(isset($data['image'])){
            $image = $data['image'];
        }else{
            $image = "";
        }
        
        $mobile = $data['mobile'];
        // $password = $data['password'];
        $lat = $data['lat'];
        $lng = $data['lng'];
        $status = $data['status'];        
        
      $array_insert =  array(
                'name' =>  $name,
                'email' =>  $email,
                'image' => $image,
                // 'password' =>  $password,               
                'phone' => $mobile,
                'address' => $address,
                'country' => $country,
                'city' => $city,
                'lat' => $lat,
                'lng' => $lng,
                'provider_id' => $provider_id, 
                'status' => $status
      );

        $driver_data = Driver::find($driver_id);
        $driver_data->update($array_insert);
        $driver = Driver::find($driver_id);

        $success['status'] =  "success";
        $success['data'] =  $driver;
  
        return response()->json(['response_api'=>$success], $this-> successStatus);
    }


    public function check_otp(Request $request) 
    { 
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        $status = $input['status'];
        $id = $input['id'];
        if($status=="1"){
            $user_provider = Driver::find($id);
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
        $user_provider = Driver::find($id);
        $email =   $user_provider->email;
            
        $otp = rand(100000,999999);
        $headers = "From: no-reply@fleetchain.net" . "\r\n";
        mail($email,"Driver Confirmation","Please Enter this otp code \n".$otp,$headers);
        
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
        $user = Driver::where(['email' => $email])->where(['status' => 'Active'])->first();  

        if(!empty($user)){

            $otp = rand(100000,999999);
            // $password = bcrypt($new_password);

            $update_array =  array(
                'otp' =>  $otp,                        
            );
                $user = Driver::where('email','=',$email)->first();    
                $user->update($update_array);

            $headers = "From: no-reply@fleetchain.net" . "\r\n" .
            "CC:  qa.ms2018@gmail.com";
            mail($email,"Forgot Password","Use this for update password: \n".$otp,$headers);
            
            $success['success'] = 'Success';
            $success['data'] = $user->otp;
            return response()->json(['response_api'=>$success], $this-> successStatus);   
 

        }else{

            $success['error'] = "Email Does Not Exist";
            return response()->json(['response_api'=>$success], 401);   

        }
    }


  public function check_password_otp(Request $request) 
    { 
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
        $otp = $input['otp'];

        $user = Driver::where(['otp' => $otp])->where(['status' => 'Active'])->first();  
        

        if(!empty($user)){

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

        $user_id = $input['driver_id'];
        $password = $input['password'];
        
        $pass = bcrypt($password);

        $array_update =  array(
            'password' =>  $pass, 
        );
        
        $user_data = Driver::find($user_id);
        $user_data->update($array_update);
        $user = Driver::find($user_id);

        $success['status'] =  "success";
        $success['data'] =  $user;
  
        return response()->json(['response_api'=>$success], $this-> successStatus); 
    }    


    public function update_driver_location(Request $request){

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
    
        $driver_id = $input['driver_id'];
        $lat = $input['lat'];
        $lng = $input['lng'];

        if(!empty($driver_id) &&  !empty($lat) && !empty($lng)){

            
            $driver = Driver::find($driver_id);                    
            $driver->lat = $lat;
            $driver->lng = $lng;
            $driver->update();

            $success['status'] =  "success";
            $success['data'] =  "Driver Location Updated";
            return response()->json(['response_api'=>$success], $this-> successStatus); 

        }else{

            $success['error'] =  "there is some please try again";
            return response()->json(['response_api'=> $success], 401); 

        }

    }

    public function get_driver_location($id){

        if(!empty($id)){

            $driver = Driver::find($id); 

            $success['status'] =  "success";
            $success['data'] =  $driver;
            return response()->json(['response_api'=>$success], $this-> successStatus); 


        }else{

            $success['error'] =  "there is some please try again, Driver Id Not Found";
            return response()->json(['response_api'=> $success], 401);

        }

                      

    }



    public function change_password(Request $request){

               
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;
    
        $user_id = $input['driver_id'];
        $password = $input['old_password'];
        $new_password = $input['new_password'];
    
        if(!empty($user_id) &&  !empty($password) && !empty($new_password)){

            $user_provider =  Driver::find($user_id);
            $old_password = false;

            $hashedPassword =   $user_provider->password;
            $user_password = $password;
            if (Hash::check($user_password, $hashedPassword, [])) {
                $old_password = true;
              }
                
                    if($old_password){
    
                        $new_password = bcrypt($new_password);
    
                        $user = Driver::find($user_id);                    
                        $user->password = $new_password;
                        $user->update();
       
                        $success['status'] =  "success";
                        $success['data'] =  "Your password has been reset.";
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


    public function all_orders(Request $request) {
                
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $provider_id = $input['driver_id'];

         $orders = Order::where('driver_id','=',$provider_id)->get();

         $array_to_send = [];
        

         foreach($orders as $value) {

            $prodvier = Provider_Model::find($value->provider_id);
            $provider_img = "";
            $provider_name = "";

            $provider_lat = $prodvier->lat;
            $provider_lng = $prodvier->lng;
             
            if(isset($prodvier)){
                $provider_name  = $prodvier->name;
                if(!empty($prodvier->image)){
                    $provider_img = $prodvier->image;
                    // $provider_img = url('public/uploads')."/".$prodvier->image;
                    // $provider_img = "https://www.discountshah.com/wp-content/uploads/2019/01/maxresdefault-1300x866-960x540.jpeg";
                    
                }else{
                    $provider_img = "https://chopal.in/wp-content/uploads/2019/10/image-696x392.jpg";
                }
                
             }else{
                $provider_img = "https://www.discountshah.com/wp-content/uploads/2019/01/maxresdefault-1300x866-960x540.jpeg";   
             }

            $array_orginal_price = [];
            
                $user_product = explode(",",$value->product_ids);
                $order_product_price = explode(",",$value->prices);
                $order_product_quantity = explode(",",$value->quantity);

                $product_name = Product::whereIn('id', $user_product)->get();
                $p_name_arr = array();
                $original_price_arr = array();
                foreach ($product_name as $value_name) {
                    $p_name_arr[] = $value_name->name;                             
                }

                $count = 0;
                foreach ($order_product_price as $value_p) {

                    $orginal_price =  $value_p/$order_product_quantity[$count];
                    $array_orginal_price[] = $orginal_price;  
                    $count = $count+1;                           
                }


                $start = strtotime("2019-10-01 00:00:00");
                $end =  strtotime("2020-12-31 23:59:59");
                
                $randomDate = date("H:i:s", rand($start, $end));
                
                



            $array_to_response = array(
                'order_id' => $value->id,
                'guest_user_name' => $value->guest_user_name,
                'user_type' => $value->user_type,
                'provider_id' => $value->provider_id,
                'provider_lat' => $provider_lat,
                'provider_lng' => $provider_lng,
                'user_id' => $value->user_id,
                'user_type' => $value->user_type,
                'product_ids' => $value->product_ids, 
                'prices' => $value->prices,
                'delivery_charges' => $value->delivery_charges,
                'lat' => $value->lat,
                'lng' => $value->lng,
                'phone' => $value->phone,
                'address' => $value->address,
                'country' => $value->country,
                'city' => $value->city,
                'product_name' => $p_name_arr,
                'original_price' => $array_orginal_price,
                'variants' => $value->variants, 
                'product_ids' => $value->product_ids, 
                'order_quantity' => $value->quantity,
                'description' => $value->description,
                'provider_image'  => $provider_img,
                'active_time' => $randomDate,
                'provider_name' =>  $provider_name,
                'order_total' => $value->total_price,
                'address' => $value->address, 
                'order_status' => $value->order_status, 
                'country' => $value->country, 
                'city' => $value->city,
                'payment_type' => $value->payment_type, 
                'total_price' => $value->total_price,
            );

            $array_to_send[] = $array_to_response;

             
         }

         $success['status'] =  "success";
         $success['data'] = $array_to_send;

    return response()->json(['response_api'=>$success], $this-> successStatus); 
    

    }

     public function driver_change_status(Request $request){

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $status = $input['order_status'];
        $order_id = $input['order_id'];
        

         $order = Order::find($order_id);
        
         
            $device_id = DB::table('orders as o')
                            ->leftjoin('users as u', 'u.id', '=', 'o.user_id')
                            ->select('device_id', 'email')
                            ->where('o.id', '=' ,$order_id)
                            ->first();
                            
    //   $reg_id = $device_id->device_id;
    //      print_r($device_id);
    //             die();

        $reg_id = $device_id->device_id;
        $email = $device_id->email;
        
        if(!empty($reg_id)){

        // $access_token = 'AAAA6zM-jhM:APA91bEhsArZtdCZhdsY3EuabN2AMA2ZLtgXsOYPXOE09OugZ4y03GisEycgFXqrZb1R4VSK44TEvgzJw-JMH7x2UcY08igoHY11fPZfyRbwwHupTLhuGGSciGj4BQrE_cjDrAqp2Rs2';
        $access_token = 'AAAATdlFyfY:APA91bGNQe6m0uzFXYhzk2TDlikfZQF888vGjjyIi-wbKcWKtQdpr1zKgmuFJNOjDSDJWCNaks_XDAKTugKMpkGUV0EHWtaw9MhZRGLtRIb8x_1vaMmp-r5Udl3qLSwCqXkSsc7RknY-';
        
        // $reg_id = 'fWr7wpGmzXQ:APA91bEC625ZbTNEHHG2qRDcDz1EdyX-lTi2Qd-v1laxJYw1Q-mqWwLTQ0ek_Qa9Dezmrz-AV9ZAPQNW5LKh-pd974HnA_xBRPYf0IGXlRU5qlqMR8p4oCzY83zJ86fyhFYzJs7MMNNY';

            if(empty($order)){
                $success['error'] =  "Order id is not Correct";
                return response()->json(['response_api' => $success], 401);
            }

        $current_time = date('Y-m-d H:i:s');
        
        if($status == "Pickedup"){
            $order->order_status =  $status;
            $order->pickup_time_date = $current_time;
         $order->update();
         
        $message = [
        'notification' => [
        'title' => 'Order Status',
        'body' => $status
        ],
        'to' => $reg_id
        ];

        $headers = "From: no-reply@fleetchain.net" . "\r\n";
    mail($email,"Order Status","Your order is now:  \n".$status,$headers);


        }elseif($status == "Delivered"){
            $order->order_status =  $status;
            $order->delivery_time_date = $current_time;
         $order->update();
         
        $message = [
        'notification' => [
        'title' => 'Order Status',
        'body' => $status
        ],
        'to' => $reg_id
        ];
        
        $headers = "From: no-reply@fleetchain.net" . "\r\n";
    mail($email,"Order Status","Your order is now: \n".$status,$headers);

        }else{

            $order->order_status =  $status;
             $order->update();
             
        $message = [
        'notification' => [
        'title' => 'Order Status',
        'body' => $status
        ],
        'to' => $reg_id
        ];
        
        $headers = "From: no-reply@fleetchain.net" . "\r\n";
    mail($email,"Order Status","Your order is now: \n".$status,$headers);
        }

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
        
        //  $success['status'] =  "success";
        //  $success['order_status'] =  $status;
        // // $success['data'] = $array_to_send;
        // return response()->json(['response_api'=>$success], $this-> successStatus); 
        }
        else
        {
                $success['error'] = "Error Occured";
                return response()->json(['response_api'=>$success], 201);
        }
     }


     public function driver_report_order(){

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $order_id = $input['order_id'];
        $is_report = "true";
        $description = $input['description'];

         $order = Order::find($order_id);


         $order->is_report =  $is_report;
         $order->report_desc =  $description;
         $order->update();

         $order = Order::find($order_id);

         $success['status'] =  "success";
         $success['order_status'] =  $order;
        // $success['data'] = $array_to_send;

    return response()->json(['response_api'=>$success], $this-> successStatus); 

     }

     public function get_report_order(){


        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

         $driver_id = $input['driver_id'];
         $orders = Order::where('driver_id','=',$driver_id)->where('is_report','=','true')->get();



         
         $array_to_send = [];
        

         foreach($orders as $value) {

            $prodvier = Provider_Model::find($value->provider_id);
            $provider_img = "";
            $provider_name = "";
             
            if(isset($prodvier)){
                $provider_name  = $prodvier->name;
                if(!empty($prodvier->image)){
                    $provider_img = url('public/uploads')."/".$prodvier->image;
                }else{
                    $provider_img = "https://chopal.in/wp-content/uploads/2019/10/image-696x392.jpg";
                }
                
             }

            $array_orginal_price = [];
            
                $user_product = explode(",",$value->product_ids);
                $order_product_price = explode(",",$value->prices);
                $order_product_quantity = explode(",",$value->quantity);

                $product_name = Product::whereIn('id', $user_product)->get();
                $p_name_arr = array();
                $original_price_arr = array();
                foreach ($product_name as $value_name) {
                    $p_name_arr[] = $value_name->name;                             
                }

                $count = 0;
                foreach ($order_product_price as $value_p) {

                    $orginal_price =  $value_p/$order_product_quantity[$count];
                    $array_orginal_price[] = $orginal_price;  
                    $count = $count+1;                           
                }


                $start = strtotime("2019-10-01 00:00:00");
                $end =  strtotime("2020-12-31 23:59:59");
                
                $randomDate = date("H:i:s", rand($start, $end));
                
                



            $array_to_response = array(
                'order_id' => $value->id,
                'guest_user_name' => $value->guest_user_name,
                'user_type' => $value->user_type,
                'provider_id' => $value->provider_id,
                'user_id' => $value->user_id,
                'user_type' => $value->user_type,
                'product_ids' => $value->product_ids, 
                'prices' => $value->prices,
                'lat' => $value->lat,
                'lng' => $value->lng,
                'address' => $value->address,
                'country' => $value->country,
                'report_desc' =>  $value->report_desc,
                'is_report' => $value->is_report,
                'city' => $value->city,
                'product_name' => $p_name_arr,
                'original_price' => $array_orginal_price,
                'variants' => $value->variants, 
                'product_ids' => $value->product_ids, 
                'order_quantity' => $value->quantity,
                'description' => $value->description,
                'provider_image'  => $provider_img,
                'active_time' => $randomDate,
                'provider_name' =>  $provider_name,
                'order_total' => $value->total_price,
                'address' => $value->address, 
                'order_status' => $value->order_status, 
                'country' => $value->country, 
                'city' => $value->city,
                'payment_type' => $value->payment_type, 
                'total_price' => $value->total_price,
            );

            $array_to_send[] = $array_to_response;

             
         }

         $success['status'] =  "success";
         $success['data'] = $array_to_send;


         
       
        return response()->json(['response_api'=>$success], $this-> successStatus); 


     }






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


    public function update_image(Request $request) 
    {

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $image = "";
        if(empty($input['driver_id']) ||  empty($input['image']) ){

            $success['status'] =  "error occured";
            return response()->json(['response_api'=>$success], 401); 

        }else{

              
                if(isset($input['image'])){
                    $image   = $input['image'];
            }
       $driver_id =  $input['driver_id'];
       
       $array_insert =  array(
           'image' =>  $image                        
       );

           $user = Driver::find($driver_id);    
           $user->update($array_insert);
           $update_user = Driver::find($driver_id);    

           $success['status'] =  "success";
           $success['details'] =  $update_user;

           return response()->json(['response_api'=>$success], $this-> successStatus); 
        }
    }

    public function get_all_driver_loc($country) 
    {  
        $drivers = Driver::where('country','=', $country)->select('lat', 'lng')->get();
        $provider_array_send = [];
        foreach($drivers as $dri){
            if($dri->lat != null && $dri->lng != null){
                $provider_array_send[] = $dri;
            }
        }
        // dd($provider_array_send);
            $success['status'] =  "success";
            $success['data'] =  $provider_array_send;
        return response()->json(['response_api'=>$success], $this-> successStatus);
    }

    public function all_driver($provider_id, $lat, $lon) 
    {  
        // $drivers = Driver::where('provider_id','=', $provider_id)->get();
        // $drivers = Driver::join('provider_records', 'drivers.provider_id', '=', 'provider_records.id')->where('provider_id','=', $provider_id)->select('drivers.*', 'provider_records.lat as latitude', 'provider_records.lng as longitude')->get();
        // $drivers .= Driver::where('provider_id','=', '')->select('drivers.*', 'drivers.lat as latitude', 'drivers.lng as longitude')->get();
        
        $drivers = Driver::where('provider_id','=', $provider_id)->orwhere('provider_id','=', null)->select('drivers.*', 'drivers.lat as latitude', 'drivers.lng as longitude')->get();
        $provider = Provider_Model::where('id','=', $provider_id)->first();
            
            foreach($drivers as $driver){
                if($driver->provider_id == $provider->id){
                    $driver->latitude = $provider->lat;
                    $driver->longitude = $provider->lng;
                }
            }
        if(!empty($lat) && !empty($lon)){
            if ($drivers->isNotEmpty()) {
                $markers = collect($drivers)->map(function ($item) use ($lat, $lon) {
                    $item->distance = calculateDistanceBetweenTwoAddresses($item->latitude, $item->longitude, $lat, $lon);
                });
            }
            $drivers = $drivers->where('distance', '<=', 10000);
        // dd($drivers);
        }
        
        $provider_array_send = [];
        foreach($drivers as $dri){
            
            
            $provider_array_send[] = $dri;
        }
        
            $success['status'] =  "success";
            $success['data'] =  $provider_array_send;
        return response()->json(['response_api'=>$success], $this-> successStatus); 
    }

    public function delete_driver($id){
        // $driver = Driver::find($id);
        // $driver->status = 'InActive';
        // $driver->update();

        Driver::where('id', $id)->delete();
        $success['status'] =  "success";
        return response()->json(['response_api'=>$success], $this-> successStatus);
    }


    public function assign_driver_order(Request $request){
           
        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $order_id =  $input['order_id'];
        $driver_id = $input['driver_id'];
        
        
        $order  = Order::find($order_id);
        $order->driver_id = $driver_id;
        $order->update();
        
        $device_id = DB::table('orders as o')
                        ->leftjoin('drivers as d', 'd.id', '=', 'o.driver_id')
                        ->select('device_id')
                        ->where('o.id', '=' ,$order_id)
                        ->first();
                        
    //   $reg_id = $device_id->device_id;
    //             echo $reg_id;
    //             die();
    
        $reg_id = $device_id->device_id;
        // $success['success'] = $reg_id;
        //         return response()->json(['response_api'=>$success], 200);
        if(!empty($reg_id))
        {
            // $access_token = 'AAAA6zM-jhM:APA91bEhsArZtdCZhdsY3EuabN2AMA2ZLtgXsOYPXOE09OugZ4y03GisEycgFXqrZb1R4VSK44TEvgzJw-JMH7x2UcY08igoHY11fPZfyRbwwHupTLhuGGSciGj4BQrE_cjDrAqp2Rs2';
            $access_token = 'AAAATdlFyfY:APA91bGNQe6m0uzFXYhzk2TDlikfZQF888vGjjyIi-wbKcWKtQdpr1zKgmuFJNOjDSDJWCNaks_XDAKTugKMpkGUV0EHWtaw9MhZRGLtRIb8x_1vaMmp-r5Udl3qLSwCqXkSsc7RknY-';
            // $reg_id = 'duB7JVhelMU:APA91bEle8yKbJ97vbNXBEQJFRM8ABD_CFk54lfe5NpXJOaul78LCbR9YUQRH1Rwz3eypdwzMXs98UqTP2ccOuI7Hssly-i2qajB9CoScguAgCYEWDN_-8sBe4huDmqi6pre0YLTz6-S';
            
            
            if(empty($order_id) || empty($driver_id)){
                
                $success['error'] = "Error Occured";
                return response()->json(['response_api'=>$success], 201);
    
            }else{
                // $order  = Order::find($order_id);
                // $order->driver_id = $driver_id;
                // $order->update();
                
                $message = [
                'notification' => [
                'title' => 'Order Status',
                'body' => 'You have assigned new order.',
                ],
                'to' => $reg_id
                ];
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
                
            
                
            //   $success['status'] = $response->getBody();
            //   return response()->json(['response_api'=>$success], $this-> successStatus); 
            }
        }
        else
        {
                $success['error'] = "Error Occured";
                return response()->json(['response_api'=>$success], 201);
        }
    }

    public function driver_earning(Request $request){

        $payload = json_decode(request()->getContent(), true);
        $input = $payload;

        $driver_id = $input['driver_id'];


        if(empty($driver_id)){
            
            $success['error'] = "Error Occured";

            return response()->json(['response_api'=>$success], 201); 

        }else{

           $orders  = Order::where('driver_id','=',$driver_id)->get();

           $total_order = 0;
           $total_complete = 0;
           $total_uncompele = 0;
           $total_sum_order = 0;

           if(!empty($orders)){
            $total_order = count($orders);
                    foreach ($orders as $order) {
                        if($order->order_status=="Completed"){
                            $total_complete = $total_complete+1;
                            $total_sum_order = $total_sum_order+$order->total_price;
                        }else{
                            $total_uncompele = $total_uncompele+1;   
                        }

                    }
           }

           $gamer_array = array(
               'total_order' => $total_order,
               'total_complete_order' => $total_complete,
               'total_uncompele' => $total_uncompele,
               'sum_complete_order' => $total_sum_order,
           );


           $success['status'] = "success";
           $success['data'] =  $gamer_array;

            return response()->json(['response_api'=>$success], $this-> successStatus); 
        }

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
    
    
  public function fd_register(Request $request) 
    { 

        $otp = rand(100000,999999);
        $payload = json_decode(request()->getContent(), true);
        $data = $payload;
        $name =  $data['name'];
        $email =  $data['email'];

        // $address =  $data['address'];
        // $country =  $data['country'];
        // $city =  $data['city'];

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
        $lat = $data['lat'];
        $lng = $data['lng'];
        $password = bcrypt($data['password']);
        
        // if(isset($data['image'])){
        //     $image = $data['image'];
        // }else{
        //     $image = "";
        // }

        if(empty($data['name']) || empty($data['email']) ||  empty($data['mobile'])  ){
           
            $success['error'] = "Please Fill All Fields";
            return response()->json(['response_api'=>$success],401); 
        }else{


        $headers = "From: no-reply@fleetchain.net" . "\r\n";
        mail($email,"Freelance Driver Confirmation","Please Enter this otp code \n".$otp,$headers);


      $array_insert =  array(
        'name' =>  $name, 
        'email' =>  $email, 
        'password' =>  $password,                
        'phone' => $mobile,
        // 'image' => $image,
        'address' => $address,
        'country' => $country,
        'city' => $city,
        'lat' => $lat,
        'lng' => $lng,
        'otp' => $otp,
      );

        $user = Driver::where('email', '=', $data['email'])->first();    
        if(empty($user)){
        $user =  Driver::create($array_insert);

        $success['data'] = $user;

        return response()->json(['response_api'=>$success], $this-> successStatus); 

        }
        else
        {
            $success['error'] = "email already exist";
            return response()->json(['response_api'=>$success],401);
        }
    }  // esle finished
}
    
    
    
}