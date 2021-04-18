            // public function all_orders_count_datewise(Request $request) {

            //         $payload = json_decode(request()->getContent(), true);
            //         $input = $payload;

            //         $provider_id = $input['provider_id'];
            //         $date = $input['date'];

            //          $orders = Order::where('provider_id','=',$provider_id)->whereDate('created_at', '=', $date)->get()->Count();

            //          $array_to_send = ['total_orders' => $orders];

            //          $success['status'] =  "success";
            //          $success['data'] = $array_to_send;

            //     return response()->json(['response_api'=>$success], $this-> successStatus);


            // }

            // public function all_orders_count_monthwise(Request $request) {

            //         $payload = json_decode(request()->getContent(), true);
            //         $input = $payload;

            //         $provider_id = $input['provider_id'];
            //         $date = $input['date'];

            //          $orders = Order::where('provider_id','=',$provider_id)->whereMonth('created_at', '=', $date)->get()->Count();

            //          $array_to_send = ['total_orders' => $orders];

            //          $success['status'] =  "success";
            //          $success['data'] = $array_to_send;

            //     return response()->json(['response_api'=>$success], $this-> successStatus);


            // }
