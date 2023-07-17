<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\manager;
use App\Models\developer;
use App\Models\User;
use App\Models\Blogsite;
use App\Models\like;
use App\Models\comment;
use App\Models\Dailywork_details;


class ManagerController extends Controller
{
    public function __construct()
    {
        config(['auth.defaults.guard' => 'api-manager']);
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
            'email' => 'required|email|unique:managers',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([$validator->errors()], 422);
        }
        // create username
        $username = substr($request->email, 0, strpos($request->email, '@')).str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);

        $user = manager::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password),'username'=>$username ]
        ));

        return response()->json([
            'message' => 'manager successfully registered',
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

// create client
    public function create_client(Request $request)
    {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:managers',
    ]);

    $password = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);

    $user = User::create([
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'password' => Hash::make($password),
    ]);


    return response()->json(['message'=>'client created successfully','client'=>$user,'password'=>$password]);
    }

    // create developer
    public function create_developer(Request $request)
    {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:managers',
    ]);

    $password = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);

    $user = developer::create([
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'password' => Hash::make($password),
    ]);


    return response()->json(['message'=>'developer created successfully','manager'=>$user,'password'=>$password]);
    }

    public function edit_profile(Request $request)
    {
        $user = manager::find(auth()->user()->id);

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
        
        $user->save();

        return response()->json(['message'=>'profile updated succesfully']);

    }
//manager crt blogsite             
    public function create_blog(Request $request){
        $blog =new Blogsite;
        $blog->blog_title = $request->title;

    // image
          $file = $request->file('image');
          $uniqueName = uniqid().'.'.$file->getClientOriginalExtension();
          $path = $file->storeAs('uploads', $uniqueName);
          $blog->blog_image = $uniqueName;

        $blog->blog_description = $request->description; 
 
        $blog->author_id = Auth::user()->id;
        $blog->user_type = "manager";

        $blog->save();
        return response()->json(['message'=>'created successfully','blog'=>$blog]);

    }

// show blogsite
    public function show_blog(){
        $blogs = Blogsite::all();
        return response()->json(['blog'=>$blogs]);
    }
// upadte blogsite
    public function edit_blog(Request $request, $id){
        $blog = Blogsite::find($id);
        if($request->blog_title){
            $blog->blog_title = $request->title;
        }
        if($request->file('image')){
            $file = $request->file('image');
            $uniqueName = uniqid().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('uploads', $uniqueName);
            $blog->blog_image = $uniqueName;
        }
        if($request->description){
            $blog->blog_description = $request->description;
        }
        $blog->save();
        return response()->json(['message'=>' updated successfully','blog'=>$blog]);
    }
// delete blogsite
    public function delete_blog($id){
        $blog = Blogsite::find($id);
        $blog->delete();
        return response()->json(['message'=>'deleted successfully']);
    }

//daily work details.....

    public function start_time(){
        $daily_work = new Dailywork_details;
        $daily_work->user_id = Auth::user()->id;
        $daily_work->name = Auth::user()->name;
        $daily_work->user_type = 'manager';
        date_default_timezone_set('Asia/Dhaka');
        $daily_work->start_time = date("Y-m-d H:i:s");
        $daily_work->work_status = "started";
        $daily_work->total_work_time = 0;
        $daily_work->save();
        return response()->json(['message'=>'work started at '.$daily_work->start_time]);
    }

    public function pause_time(){
        $daily_work = Dailywork_details::where('user_id', Auth::user()->id)->where('user_type','manager')->latest()->first();

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
        $daily_work = Dailywork_details::where('user_id', Auth::user()->id)->where('user_type','manager')->latest()->first();

        if($daily_work->work_status == 'paused'){
            date_default_timezone_set('Asia/Dhaka');
            $daily_work->resume_time = date("Y-m-d H:i:s");

            $daily_work->work_status = 'resumed';
        }
        $daily_work->save();
        return response()->json(['message'=>'you are resumed']);
    }



    public function end_time(){
        $daily_work = Dailywork_details::where('user_id', Auth::user()->id)->where('user_type','manager')->latest()->first();

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
        $daily_work = Dailywork_details::where('user_id', Auth::user()->id)->where('user_type','manager')->latest()->first();
        if($daily_work->work_status == 'ended'){
            $daily_work->work_note = $request->work_note;
        }
        $daily_work->save();
        return response()->json(['message'=>'work note add successfully']);

    }


}


