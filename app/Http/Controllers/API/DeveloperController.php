<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\developer;
use App\Models\admin;
use App\Models\manager;
use App\Models\like;
use App\Models\comment;
use App\Models\Blogsite;
use App\Models\Dailywork_details;


class DeveloperController extends Controller
{
    public function __construct()
    {
        config(['auth.defaults.guard' => 'api-developer']);
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
            'email' => 'required|email|unique:developers',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([$validator->errors()], 422);
        }
        // create username
        $username = substr($request->email, 0, strpos($request->email, '@')).str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);

        $user = developer::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password),'username'=>$username ]
        ));

        return response()->json([
            'message' => 'developer successfully registered',
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
        $user = developer::find(auth()->user()->id);

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
        if($request->github){
            $git = $request->github;
            $user->github = $git;
        }
        if($request->linkedin){
            $linkedin = $request->linkedin;
            $user->linkedin = $linkedin;
        }
        if($request->skill){
            $skill = $request->skill;
            $user->skill = $user->skill.','.$skill;
        }
        $user->save();

        return response()->json(['message'=>'profile updated succesfully']);

    }

// admin as developer mood
    public function login_as_developer($id)
    {

        $user = developer::find($id);
        if (! $token = Auth::login($user)) {
            return response()->json(['errors' => 'unauthorized'], 401);
        }

        $token = $this->createNewToken($token);

        $token = $token->original;

        $msg = [
            'id'=>$user->id,
            'username' => $user->username,
            'email' => $user->email,
            'usertype' => 'admin',
            'token'=> $token['access_token'],
        ];
        return $msg;
    }
// show blog
    public function show_blog(){
        $blogs = Blogsite::all();
        return response()->json(['blogs'=>$blogs]);
    }


    public function add_like($id){
        $post = Blogsite::find($id);
        $like = new like;
        $post->like_count += 1;
        $like->post_id = $id;
        $like->user_id = Auth::user()->id;
        $like->user_type = "developer";
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
         $comment->user_type = "developer";
         $comment->content = $request->input('content');
         $comment->save();
         $post->save();
        return response()->json(['message'=>'commented successfully']);
       }
//daily work details.....

    public function start_time(){
        $daily_work = new Dailywork_details;
        $daily_work->user_id = Auth::user()->id;
        $daily_work->name = Auth::user()->name;
        $daily_work->user_type = 'developer';
        date_default_timezone_set('Asia/Dhaka');
        $daily_work->start_time = date("Y-m-d H:i:s");
        $daily_work->work_status = "started";
        $daily_work->total_work_time = 0;
        $daily_work->save();
        return response()->json(['message'=>'work started at '.$daily_work->start_time]);
    }

    public function pause_time(){
        $daily_work = Dailywork_details::where('user_id', Auth::user()->id)->where('user_type','developer')->latest()->first();

        if($daily_work->work_status == 'started'){
            date_default_timezone_set('Asia/Dhaka');
            $daily_work->pause_time = date("Y-m-d H:i:s");

            $from_timestamp = strtotime($daily_work->start_time);
            $to_timestamp = strtotime($daily_work->pause_time);

            $daily_work->total_work_time += $to_timestamp - $from_timestamp;
            $daily_work->work_status = 'paused';
        }
        $daily_work->save();
        return response()->json(['message'=>'you are paused']);
    }

    public function resume_time(){
        $daily_work = Dailywork_details::where('user_id', Auth::user()->id)->where('user_type','developer')->latest()->first();

        if($daily_work->work_status == 'paused'){
            date_default_timezone_set('Asia/Dhaka');
            $daily_work->resume_time = date("Y-m-d H:i:s");

            $daily_work->work_status = 'resumed';
        }
        $daily_work->save();
        return response()->json(['message'=>'you are resumed']);
    }



    public function end_time(){
        $daily_work = Dailywork_details::where('user_id', Auth::user()->id)->where('user_type','developer')->latest()->first();

        if($daily_work->work_status == 'started'){
            date_default_timezone_set('Asia/Dhaka');
            $daily_work->end_time = date("Y-m-d H:i:s");

            $from_timestamp = strtotime($daily_work->start_time);
            $to_timestamp = strtotime($daily_work->end_time);

            $daily_work->total_work_time += $to_timestamp - $from_timestamp;
            $daily_work->work_status = 'ended';
        }
        if($daily_work->work_status == 'resumed'){
            date_default_timezone_set('Asia/Dhaka');
            $daily_work->end_time = date("Y-m-d H:i:s");

            $from_timestamp = strtotime($daily_work->resume_time);
            $to_timestamp = strtotime($daily_work->end_time);

            $daily_work->total_work_time += $to_timestamp - $from_timestamp;
            $daily_work->work_status = 'ended';
        }
        $daily_work->save();
        return response()->json(['message'=>'you are ended']);
    }
    public function add_work_note(Request $request){
        $daily_work = Dailywork_details::where('user_id', Auth::user()->id)->where('user_type','developer')->latest()->first();
        if($daily_work->work_status == 'ended'){
            $daily_work->work_note = $request->work_note;
        }
        $daily_work->save();
        return response()->json(['message'=>'work note add successfully']);

    }

        
}
