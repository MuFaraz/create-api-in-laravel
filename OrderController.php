<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\Provider_Model;
use App\Countries;
use App\Product;
use App\Order;
use App\Driver;
use App\Categories;
use App\Deal_items;
use App\Group_product;
use Hash;
use Validator;
use DB;
use Artisan;
use Redirect;
use Mail;
use GuzzleHttp;

class OrderController extends Controller 
{

public $successStatus = 200;

  public function save_order(Request $request){

    $payload = json_decode(request()->getContent(), true);
    $input = $payload;
  

        // if($input['user_type']=="guest"){

        //     $guest_user_name =  $input['guest_user_name'];
        //     $provider_id =  $input['provider_id'];
        //     $prices =  implode(',',$input['prices']);
        //     $variants =  implode(',',$input['variants']);
        //     $product_ids = implode(',',$input['product_ids']);
        //     $order_quantity = implode(',',$input['quantity']);
        //     $delivery_charges = $input['delivery_charges'];
        //     $description = $input['description'];
        //     $address = $input['address'];
        //     $country = $input['country'];
        //     $city = $input['city'];
        //     $lat = $input['lat'];
        //     $lng = $input['lng'];
        //     $order_status = "submitted";
        //     $payment_status = "pending";
        //     $payment_type = $input['payment_type'];
        //     $total_quantity = $input['total_quantity'];

        //     $phone = "";

        //     if(isset($input['phone'])){

        //         $phone = $input['phone'];

        //     }else{
        //         $phone = "";
        //     }

        //     $sum_p  = explode(",",$prices);

        //      $total_price = array_sum($sum_p);

        //     $ab = array(
        //         'product_ids' => $product_ids,
        //         'provider_id' => $provider_id,
        //         'guest_user_name' => $guest_user_name,
        //         'user_type' => $input['user_type'],
        //         'prices' => $prices,
        //         'variants' => $variants,
        //         'description' => $description,
        //         'address' => $address,
        //         'country' => $country,
        //         'city' => $city,
        //         'lat' => $lat,
        //         'lng' => $lng,
        //         'quantity' =>  $order_quantity,
        //         'order_status' => $order_status,
        //         'payment_status' => $payment_status,
        //         'payment_type' => $payment_type,
        //         'total_price' =>  $total_price,
        //         'phone' => $phone,
        //         'total_quantity' => $total_quantity,
        //     ); 

        //     $order =  Order::create($ab);
        //     $user_product = explode(",",$order->product_ids);
        //     $product_name = Product::whereIn('id', $user_product)->get();
        //     $p_name_arr = array();
        //     foreach ($product_name as $value) {
        //         $p_name_arr[] = $value->name;
        //     }
        //     $prices = explode(',',$order->prices);
        //     $variants = explode(',',$order->variants);
        //     $product_ids = explode(',',$order->product_ids);
        //     $order_quantity = explode(',',$order->quantity);
        //     $array_to_response = array(
        //             'provider_id' => $order->provider_id,
        //             'guest_user_name' => $order->guest_user_name,
        //             'user_type' => $order->user_type,
        //             'product_ids' => $order->product_ids, 
        //             'prices' => $prices,
        //             'product_name' => $p_name_arr,
        //             'variants' => $variants, 
        //             'product_ids' => $product_ids, 
        //             'order_quantity' => $order_quantity,
        //             'description' => $order->description, 
        //             'address' => $order->address, 
        //             'country' => $order->country, 
        //             'city' => $order->city,
        //             'payment_type' => $order->payment_type, 
        //             'total_price' => $order->total_price,
        //             'total_quantity' => $total_quantity,
        //         );

        //      $success['status'] =  "success";
        //      $success['data'] = $array_to_response;

        // return response()->json(['response_api'=>$success], $this-> successStatus); 
                
        // }
        // elseif($input['user_type']=="normal_user"){
            $user_id =  $input['user_id'];
            $provider_id =  $input['provider_id'];
            $prices =  implode(',', $input['prices']);
            $variants =  implode(',', $input['variants']);
            $product_ids = implode(',', $input['product_ids']);
            $deals_ids = implode(',', $input['deals_ids']);
            $order_quantity = implode(',', $input['quantity']);
            $description = $input['description'];
            $address = $input['address'];
            $country = $input['country'];
            $city = $input['city'];
            $lat = $input['lat'];
            $lng = $input['lng'];
            $order_status = "Pending";
            $payment_status = "pending";
            $payment_type = $input['payment_type'];
            $total_quantity = $input['total_quantity'];
            $discount_price = $input['discount_price'];
            $sum_p  = explode(",",$prices);
            $total_price = array_sum($sum_p);
            
             $street = "";
             $jaddah = "";
             $home = "";
             $floor = "";
             $flat = "";


             if(isset($input['street'])){
                $street = $input['street'];
             }

             if(isset($input['jaddah'])){
                $jaddah = $input['jaddah'];
             }

             if(isset($input['home'])){
                $home = $input['home'];
             }

             if(isset($input['floor'])){
                $floor = $input['floor'];
             }

             if(isset($input['flat'])){
                $flat = $input['flat'];
             }

             if(isset($input['phone'])){

                $phone = $input['phone'];

            }else{
                $phone = "";
            }


            $ab = array(
                'product_ids' => $product_ids,
                'deals_ids' => $deals_ids,
                'provider_id' => $provider_id,
                'user_id' => $user_id,
                'user_type' => $input['user_type'],
                'guest_user_name' => $input['guest_user_name'],
                'prices' => $prices,
                'variants' => $variants,
                'description' => $description,
                'address' => $address,
                'country' => $country,
                'city' => $city,
                'lat' => $lat,
                'lng' => $lng,
                'quantity' =>  $order_quantity,
                'order_status' => $order_status,
                'payment_status' => $payment_status,
                'payment_type' => $payment_type,
                'total_price' =>  $total_price,
                'phone' => $phone,
                'total_quantity' => $total_quantity,
                'discount_price' => $discount_price,
                'street' => $street,
                'home' => $home,
                'floor' => $floor,
                'flat' => $flat,
                'jaddah' => $jaddah,
            ); 

            $order =  Order::create($ab);
            $user_product = explode(",",$order->product_ids);

            $product_name = Product::whereIn('id', $user_product)->get();
            $p_name_arr = array();
            foreach ($product_name as $value) {
                $p_name_arr[] = $value->name;
            }

            $prices = explode(',',$order->prices);
            $variants = explode(',',$order->variants);
            $product_ids = explode(',',$order->product_ids);
            $deals_ids = explode(',',$order->deals_ids);
            $order_quantity = explode(',',$order->quantity);

            $array_to_response = array(
                    'provider_id' => $order->provider_id,
                    'user_id' => $order->user_id,
                    'product_ids' => $order->product_ids, 
                    'prices' => $prices,
                    'product_name' => $p_name_arr,
                    'variants' => $variants, 
                    'product_ids' => $product_ids, 
                    'deals_ids' => $deals_ids, 
                    'order_quantity' => $order_quantity,
                    'description' => $order->description, 
                    'address' => $order->address, 
                    'country' => $order->country, 
                    'city' => $order->city,
                    'payment_type' => $order->payment_type, 
                    'total_price' => $order->total_price,
                    'total_quantity' => $total_quantity,
                );
                
                
        //     $device_id = DB::table('provider_records as p')
        //                     ->select('device_id')
        //                     ->where('p.id', '=' ,$provider_id)
        //                     ->first();
        
        
        // $access_token = 'AAAA6zM-jhM:APA91bEhsArZtdCZhdsY3EuabN2AMA2ZLtgXsOYPXOE09OugZ4y03GisEycgFXqrZb1R4VSK44TEvgzJw-JMH7x2UcY08igoHY11fPZfyRbwwHupTLhuGGSciGj4BQrE_cjDrAqp2Rs2';
        // $reg_id = $device_id->device_id;
        
        // $message = [
        // 'notification' => [
        // 'title' => 'Order Status',
        // 'body' => 'You have new order'
        // ],
        // 'to' => $reg_id
        // ];
           
        //     // $success['status'] =  "success";
        //     // $success['data'] = $array_to_response;
        //     // return response()->json(['response_api'=>$success], $this-> successStatus); 
            
            
        // $client = new GuzzleHttp\Client([
        // 'headers' => [
        // 'Content-Type' => 'application/json',
        // 'Authorization' => 'key='.$access_token,
        // ]
        // ]);
        
        // $response = $client->post('https://fcm.googleapis.com/fcm/send',
        // ['body' => json_encode($message)]
        // );
         
        // echo $response->getBody();
        // }
        $success['data']="success";
         return response()->json(['response_api'=>$success], $this-> successStatus);
}

            public function all_orders(Request $request) {
                
                    $payload = json_decode(request()->getContent(), true);
                    $input = $payload;

                    $provider_id = $input['provider_id'];

                     $orders = Order::where('provider_id','=',$provider_id)->orderBy('id', 'DESC')->get();
                     $orders_pick= Order::where('provider_id','=',$provider_id)->where('order_type','=','pickup')->orderBy('id', 'DESC')->get();
                     $orders_deli= Order::where('provider_id','=',$provider_id)->where('order_type','=','delivery')->orderBy('id', 'DESC')->get();
                     $array_to_send = [];
                     $array_pickup=[];
                     $array_delivery=[];
                    
                     foreach($orders as $value) {

                        $array_orginal_price = [];
                        
                            $user_product = explode(",",$value->product_ids);
                            $user_deal = explode(",",$value->deals_ids);
                            $order_product_price = explode(",",$value->prices);
                            $order_product_quantity = explode(",",$value->quantity);

                            $product_name = Product::whereIn('id', $user_product)->get();
                            $deal_name = Deal_items::whereIn('deal_id', $user_deal)->get();

                            $driver_obj = Driver::find($value->driver_id);
                            $driver_name = "";
                            if(isset($driver_obj)){
                                $driver_name = $driver_obj->name;  
                            }

                            $p_name_arr = array();
                            $original_price_arr = array();
                            
                            foreach ($product_name as $value_name) {
                                $p_name_arr[] = $value_name->name;                             
                            }
                
                            foreach ($deal_name as $value_name) {
                                $p_name_arr[] = $value_name->item_name;                      
                            }
                            
                            $count = 0;
                            foreach ($order_product_price as $value_p) {
                                $orginal_price = "";
                                    if(!empty($value_p)){
                                        $orginal_price =  $value_p/$order_product_quantity[$count];
                                    }else{
                                        $orginal_price =  0;
                                    }
                                
                                $array_orginal_price[] = $orginal_price;  
                                $count = $count+1;                           
                            }

                        $array_to_response = array(
                            'order_id' => $value->id,
                            'guest_user_name' => $value->guest_user_name,
                            'order_status' => $value->order_status,
                            'user_type' => $value->user_type,
                            'lat' => $value->lat,
                            'lng' => $value->lng,
                            'driver_name' => $driver_name,
                            'phone' => $value->phone,
                            'provider_id' => $value->provider_id,
                            'user_id' => $value->user_id,
                            'user_type' => $value->user_type,
                            'product_ids' => $value->product_ids, 
                            'prices' => $value->prices,
                            'product_name' => $p_name_arr,
                            'original_price' => $array_orginal_price,
                            'variants' => $value->variants, 
                            'product_ids' => $value->product_ids, 
                            'order_quantity' => $value->quantity,
                            'description' => $value->description, 
                            'address' => $value->address, 
                            'country' => $value->country, 
                            'city' => $value->city,
                            'payment_type' => $value->payment_type, 
                            'total_price' => $value->total_price,
                        );

                        $array_to_send[] = $array_to_response;

                         
                     }
                      foreach($orders_pick as $value) {

                        $array_orginal_price = [];
                        
                            $user_product = explode(",",$value->product_ids);
                            $user_deal = explode(",",$value->deals_ids);
                            $order_product_price = explode(",",$value->prices);
                            $order_product_quantity = explode(",",$value->quantity);

                            $product_name = Product::whereIn('id', $user_product)->get();
                            $deal_name = Deal_items::whereIn('deal_id', $user_deal)->get();

                            $driver_obj = Driver::find($value->driver_id);
                            $driver_name = "";
                            if(isset($driver_obj)){
                                $driver_name = $driver_obj->name;  
                            }

                            $p_name_arr = array();
                            $original_price_arr = array();
                            
                            foreach ($product_name as $value_name) {
                                $p_name_arr[] = $value_name->name;                             
                            }
                
                            foreach ($deal_name as $value_name) {
                                $p_name_arr[] = $value_name->item_name;                      
                            }
                            
                            $count = 0;
                            foreach ($order_product_price as $value_p) {
                                $orginal_price = "";
                                    if(!empty($value_p)){
                                        $orginal_price =  $value_p/$order_product_quantity[$count];
                                    }else{
                                        $orginal_price =  0;
                                    }
                                
                                $array_orginal_price[] = $orginal_price;  
                                $count = $count+1;                           
                            }

                        // $array_to_response = array(
                        //     'order_id' => $value->id,
                        //     'guest_user_name' => $value->guest_user_name,
                        //     'order_status' => $value->order_status,
                        //     'user_type' => $value->user_type,
                        //     'lat' => $value->lat,
                        //     'lng' => $value->lng,
                        //     'driver_name' => $driver_name,
                        //     'phone' => $value->phone,
                        //     'provider_id' => $value->provider_id,
                        //     'user_id' => $value->user_id,
                        //     'user_type' => $value->user_type,
                        //     'product_ids' => $value->product_ids, 
                        //     'prices' => $value->prices,
                        //     'product_name' => $p_name_arr,
                        //     'original_price' => $array_orginal_price,
                        //     'variants' => $value->variants, 
                        //     'product_ids' => $value->product_ids, 
                        //     'order_quantity' => $value->quantity,
                        //     'description' => $value->description, 
                        //     'delivery_charges' => $value->delivery_charges,
                        //     'address' => $value->address, 
                        //     'country' => $value->country, 
                        //     'city' => $value->city,
                        //     'payment_type' => $value->payment_type, 
                        //     'total_price' => $value->total_price,
                        // );

                        // $array_pickup[] = $array_to_response;

                         
                     }
                     foreach($orders_deli as $value) {

                        $array_orginal_price = [];
                        
                            $user_product = explode(",",$value->product_ids);
                            $user_deal = explode(",",$value->deals_ids);
                            $order_product_price = explode(",",$value->prices);
                            $order_product_quantity = explode(",",$value->quantity);

                            $product_name = Product::whereIn('id', $user_product)->get();
                            $deal_name = Deal_items::whereIn('deal_id', $user_deal)->get();

                            $driver_obj = Driver::find($value->driver_id);
                            $driver_name = "";
                            if(isset($driver_obj)){
                                $driver_name = $driver_obj->name;  
                            }

                            $p_name_arr = array();
                            $original_price_arr = array();
                            
                            foreach ($product_name as $value_name) {
                                $p_name_arr[] = $value_name->name;                             
                            }
                
                            foreach ($deal_name as $value_name) {
                                $p_name_arr[] = $value_name->item_name;                      
                            }
                            
                            $count = 0;
                            foreach ($order_product_price as $value_p) {
                                $orginal_price = "";
                                    if(!empty($value_p)){
                                        $orginal_price =  $value_p/$order_product_quantity[$count];
                                    }else{
                                        $orginal_price =  0;
                                    }
                                
                                $array_orginal_price[] = $orginal_price;  
                                $count = $count+1;                           
                            }

                        // $array_to_response = array(
                        //     'order_id' => $value->id,
                        //     'guest_user_name' => $value->guest_user_name,
                        //     'order_status' => $value->order_status,
                        //     'user_type' => $value->user_type,
                        //     'lat' => $value->lat,
                        //     'lng' => $value->lng,
                        //     'driver_name' => $driver_name,
                        //     'phone' => $value->phone,
                        //     'provider_id' => $value->provider_id,
                        //     'user_id' => $value->user_id,
                        //     'user_type' => $value->user_type,
                        //     'product_ids' => $value->product_ids, 
                        //     'prices' => $value->prices,
                        //     'product_name' => $p_name_arr,
                        //     'original_price' => $array_orginal_price,
                        //     'variants' => $value->variants, 
                        //     'product_ids' => $value->product_ids, 
                        //     'order_quantity' => $value->quantity,
                        //     'description' => $value->description, 
                        //     'delivery_charges' => $value->delivery_charges,
                        //     'address' => $value->address, 
                        //     'country' => $value->country, 
                        //     'city' => $value->city,
                        //     'payment_type' => $value->payment_type, 
                        //     'total_price' => $value->total_price,
                        // );

                        // $array_delivery[] = $array_to_response;

                         
                     }

                     $success['status'] =  "success";
                     $success['data'] = $array_to_send;
                    //  $success['datap'] = $array_pickup;
                    //  $success['datad'] = $array_delivery;
        
                return response()->json(['response_api'=>$success], $this-> successStatus); 
            }
            
    public function delete_order($id){
        // $driver = Driver::find($id);
        // $driver->status = 'InActive';
        // $driver->update();

        Order::where('id', $id)->delete();
        $success['status'] =  "success";
        return response()->json(['response_api'=>$success], $this-> successStatus);
    }
    
        public function all_orders_count_datewise(Request $request) {
                $payload = json_decode(request()->getContent(), true);
                $input = $payload;

                // $provider_id = $input['provider_id'];
                $date = $input['date'];

                $orders = Order::whereDate('created_at', '=', $date)->get()->Count();
                // $orders = Order::where('provider_id','=',$provider_id)->whereDate('created_at', '=', $date)->get()->Count();

                $array_to_send = ['total_orders' => $orders];
                 
                $success['status'] =  "success";
                $success['data'] = $array_to_send;
    
            return response()->json(['response_api'=>$success], $this-> successStatus); 
        }


        public function all_orders_count_monthwise(Request $request) {
            
                $payload = json_decode(request()->getContent(), true);
                $input = $payload;

                // $provider_id = $input['provider_id'];
                $date = $input['date'];

                $orders = Order::whereMonth('created_at', '=', $date)->get()->Count();
                // $orders = Order::where('provider_id','=',$provider_id)->whereMonth('created_at', '=', $date)->get()->Count();

                $array_to_send = ['total_orders' => $orders];
                 
                $success['status'] =  "success";
                $success['data'] = $array_to_send;
    
            return response()->json(['response_api'=>$success], $this-> successStatus); 
            

        }
        
}
