<?php

namespace App\Http\Controllers\API;



use App\Http\Controllers\Controller;

use App\Sub_categories;

use Hash;

use Validator;

use DB;

use Artisan;

use Redirect;

use App\Document;
use Illuminate\Http\Request;

class SubCategories extends Controller
{

    public $successStatus = 200;

    public function fetch_subcategory($id)
    {
        $sub_categories = Sub_categories::where('category_id', '=', $id)->get();
          $new__sub_category = [];

          foreach ($sub_categories as $cat) {
              $path = url('public/uploads')."/".$cat->image;

            $array_insert =  array(
                'name' =>  $cat->name,
                'image' =>  $path,
                'category_id' =>  $cat->category_id
                );

                $new__sub_category []  =  $array_insert;
          }

          $success['status'] =  "success";
          $success['data'] =  $new__sub_category;
      return response()->json(['response_api'=>$success], $this-> successStatus);


        // $sub_categories = Sub_categories::where('category_id', '=', $id)->get();

        // $success['details'] =  $sub_categories;
        // // return $success['details'];
        // return response()->json(['response_api' => $success], $this->successStatus);
    }

    public function register(Request $request)
    {
        $input = json_decode(request()->getContent(), true);
        $data = $input;

        $category_id =  $data['category_id'];
        $name =  $data['name'];
        // $image = $data['image'];
        // $status = $data['status'];
        // $imageName = time().'.'.$data['image']->image->extension();

        // $data['image']->image->move(public_path('uploads'), $imageName);
        // $uploadedImageResponse = array(
        //     "image_name" => basename($image_uploaded_path),
        //     "image_url" => $path,
        //     "mime" => $image->getClientMimeType()
        // );
        // return sendCustomResponse('File Uploaded Successfully', 'success', 200, $uploadedImageResponse);

        // $file = $request->file->store('public/uploads');

        if (isset($data['image'])) {
            $image = $data['image'];
        } else {
            $image = "";
        }

        if (isset($data['status'])) {
            $status = $data['status'];
        } else {
            $status = "";
        }
        if (empty($data['name']) || empty($data['category_id'])) {

            $success['error'] = "Please Fill All Fields";
            return response()->json(['response_api' => $success], 401);
        } else {
            $array_insert =  array(
                'name' =>  $name,
                'category_id' =>  $category_id,
                'status' => $status,
                'image' => $image,
            );
            // $input['password'] = bcrypt($input['password']);
            $sub_categories = Sub_Categories::create($array_insert);

            // $success['token'] =  $user->createToken('MyApp')->accessToken;
            // $success['name'] =  $user->name;

            $success['data'] =  $sub_categories;
            return response()->json(['success' => $success], $this->successStatus);
        }
    }
}
