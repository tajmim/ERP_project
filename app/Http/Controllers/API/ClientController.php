<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\admin;
use App\Models\manager;
use App\Models\like;
use App\Models\comment;
use App\Models\Blogsite;


class ClientController extends Controller
{
    public function __construct()
    {
        config(['auth.defaults.guard' => 'api-client']);
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'barrier',
            'user' => auth()->user(),
        ]);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([$validator->errors()], 422);
        }

        if (! $token = Auth::attempt($validator->validated())) {
            return response()->json(['errors' => 'unauthorized'], 401);
        }

        return $this->createNewToken($token);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([$validator->errors()], 422);
        }

    // create username
        $username = substr($request->email, 0, strpos($request->email, '@')).str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password), 'username'=>$username]
        ));

        return response()->json([
            'message' => 'user successfully registered',
            'user' => $user,
        ]);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message'=>'you are signed out']);
    }

    public function profile_details()
    {
        return response()->json(['user'=>auth()->user()]);
    }

    public function edit_profile(Request $request)
    {
        $user = User::find(auth()->user()->id);

        if($request->name){
            $name = $request->name;
            $user->name = $name;
        }
        if($request->email){
            $email = $request->email;
            $user->email = $email;
        }
        if($request->address){
            $address = $request->address;
            $user->address = $address;
        }
        if($request->mobile){
            $mobile = $request->mobile;
            $user->mobile = $mobile;
        }
        $user->save();

        return response()->json(['message'=>'profile updated succesfully']);

    }
// show blog
    public function show_blog(){
        $blogs = Blogsite::all();
        return response()->json(['blog'=>$blogs]);
    }


        public function add_like($id){
        $post = Blogsite::find($id);
        $like = new like;
        $post->like_count += 1;
        $like->post_id = $id;
        $like->user_id = Auth::user()->id;
        $like->user_type = "client";
        $post->save();
        $like->save();
        return response()->json(['message'=>'liked successfully']);
    }
    public function blog_details($id){
        $post = Blogsite::find($id);
        $post->viewer += 1;
        $post->save();
        $comments = comment::where('post_id',$id)->get();
        if($post->user_type == 'admin'){
            $created_by = admin::find($post->author_id);
        }
        if($post->user_type == 'manager'){
            $created_by = manager::find($post->author_id);
        }
        return response()->json(['blog'=>$post, 'created_by' => $created_by , 'comments' => $comments]);
    }
       public function add_Comment(Request $request, $id){
         $post = Blogsite::find($id);
         $comment = new comment();
         $post->comment_count +=1 ;
         $comment->post_id = $id;
         $comment->user_id = Auth::user()->id;
         $comment->user_type = "client";
         $comment->content = $request->input('content');
         $comment->save();
         $post->save();
        return response()->json(['message'=>'commented successfully']);
       }

}
