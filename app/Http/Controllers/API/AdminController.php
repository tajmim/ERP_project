<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Mail;
use App\Mail\SendMeetingMail;
use App\Mail\otp_send;
use App\Models\admin;
use App\Models\manager;
use App\Models\developer;
use App\Models\User;
use App\Models\Project_Technology;
use App\Models\Project_Type;
use App\Models\Project;
use App\Models\Meeting;
use App\Models\Blogsite;
use App\Models\like;
use App\Models\comment;
use App\Models\Dailywork_details;

class AdminController extends Controller
{
    public function __construct()
    {
        config(['auth.defaults.guard' => 'api-admin']);
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'barier',
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
            'email' => 'required|email|unique:admins',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([$validator->errors()], 422);
        }

        // create username
        $username = substr($request->email, 0, strpos($request->email, '@')).str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);

        $user = admin::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password),'username'=>$username ]
        ));

        return response()->json([
            'message' => 'admin successfully registered',
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


    public function forget_password(Request $request){
        $email = $request->email;

        $user = admin::where('email',$email)->first();

        if($user){
            $otp = str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT);

            $data = [
                'email' => $user->email,
                'name' => $user->name,
                'otp' => $otp
            ];
            $user->otp = $otp;
            $user->save();
            Mail::to($user->email)->send( new otp_send($data));
            return response()->json(['message'=>'otp send successfully']);
        }
        return response()->json(['message'=>'user not found']);

    }

    public function verify_forget_password(Request $request)
    {
        $user = admin::where('email',$request->email)->first();
        if($user){
            if($request->otp = $user->otp && $user->otp !=null){
                if($request->password == $request->confirm_password){
                    $user->password = bcrypt($request->password);
                    $user->otp = null;
                    $user->save();
                    return response()->json(['message'=>'password changed successfully']);
                }
                return response()->json(['message'=>'password does not match with confirm password']);
            }
            return response()->json(['message'=>'wrong otp']);
        }
        return response()->json(['message'=>'user not found']);
    }

// create manager
    public function create_manager(Request $request)
    {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:managers',
    ]);
    // create username
        $username = substr($request->email, 0, strpos($request->email, '@')).str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);

    $password = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);

    $user = manager::create([
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'username'=>$username,
        'password' => Hash::make($password),
    ]);


    return response()->json(['message'=>'manager created successfully','manager'=>$user,'password'=>$password]);
    }


// create client
    public function create_client(Request $request)
    {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:managers',
    ]);
    // create username
        $username = substr($request->email, 0, strpos($request->email, '@')).str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);

    $password = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);

    $user = User::create([
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'username' =>$username,
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
    // create username
        $username = substr($request->email, 0, strpos($request->email, '@')).str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);

    $password = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);

    $user = developer::create([
        'name' => $request->input('name'),
        'email'=> $request->input('email'),
        'username'=> $username,
        'password' => Hash::make($password),
    ]);


    return response()->json(['message'=>'developer created successfully','developer'=>$user,'password'=>$password]);
    }



// admin create technology
    public function add_project_technology(Request $request){
      
      $technology = new Project_Technology; 

      $technology->project_tech_name = $request->tech_name;
      $technology->project_tech_workable_person = $request->workable_person;
    // image
        $file = $request->file('image');
        $uniqueName = uniqid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('uploads', $uniqueName);
        $technology->project_tech_image = $uniqueName;
        $technology->save();
        return response()->json(['message'=>'technology created successfully','tech'=>$technology]);
     
    }

// admin show technology
    public function show_technology()
    {
        $technologies = Project_technology::all();
        return response()->json(['technologies'=>$technologies]);
    }

// admin update technology
    public function edit_technology(Request $request , $id)
    {
        
      $technology = Project_Technology::find($id); 
      if($request->tech_name)
        {
            $technology->project_tech_name = $request->tech_name;
        }
        if($request->workable_person){
            $technology->project_tech_workable_person = $request->workable_person;
        }

        if($request->file('image')){
            $file = $request->file('image');
        $uniqueName = uniqid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('uploads', $uniqueName);
        $technology->project_tech_image = $uniqueName;
        }
        

      $technology->save();
      return response()->json(['message'=>'technology updated successfully','tech'=>$technology]);
      
      
    }


    // admin delete technology
    public function delete_technology($id)
    {
        $technology = Project_Technology::find($id);
        $technology->delete();
        return response()->json(['message'=>'technology deleted successfully']);
    }

// admin create project type

    public function add_project_type(Request $request){
      $type = new Project_Type; 
      $type->service_title = $request->service_name; 
      $type->service_experience = $request->service_experience;
// image
      $file = $request->file('image');
      $uniqueName = uniqid().'.'.$file->getClientOriginalExtension();
      $path = $file->storeAs('uploads', $uniqueName);
      $type->service_image = $uniqueName;
      
      $type->save();
      return response()->json(['message'=>'type created successfully','tech'=>$type]);
      
      
    }
    public function add_project(Request $request){
      
      $project = new Project; 
      $project->project_name = $request->project_name; 
      $project->project_client = $request->project_client; 
      $project->project_budget = $request->project_budget; 
      $project->project_technology = $request->project_technology; 
      $project->project_type = $request->project_type; 
      $project->project_developer = $request->project_developer; 
      $project->project_manager = $request->project_manager; 
      $project->project_documents = $request->project_documents; 
      $project->project_contact = $request->project_contact; 
      $project->save();
      return response()->json(['message'=>'project created successfully','tech'=>$project]);
 
    }

// create_meeting
    public function create_meeting(Request $request){
        $meeting = new Meeting;
        $meeting->meeting_time = $request->meeting_time; 
        $meeting->meeting_title = $request->meeting_title; 
        $meeting->meeting_link = $request->meeting_link; 
        $meeting->meeting_developer = $request->meeting_developer;
        $meeting->meeting_manager = $request->meeting_manager;
        $meeting->save();
        return response()->json(['message'=>'meeting create successfully','meeting'=>$meeting]); 

    }
// meeting share
    public function meeting_share($id){
        $meeting = Meeting::find($id);
        $meeting_time = $meeting->meeting_time; 
        $meeting_title = $meeting->meeting_title; 
        $meeting_link = $meeting->meeting_link; 

         // meeting mail to developer
        $meeting_developer = $meeting->meeting_developer;
        $developer = developer::find($meeting_developer);
        $developer_email = $developer->email;

        $meetingData = [
            'meeting_time'=>$meeting_time,
            'meeting_title'=>$meeting_title,
            'meeting_link'=>$meeting_link,
        ];

        Mail::to($developer_email)->send( new SendMeetingMail($meetingData));
        // meeting mail to manager
        $meeting_manager = $meeting->meeting_manager;
        $manager = manager::find($meeting_manager);
        $manager_email = $manager->email;

        $meetingData = [
            'meeting_time'=>$meeting_time,
            'meeting_title'=>$meeting_title,
            'meeting_link'=>$meeting_link,
        ];

        Mail::to($manager_email)->send( new SendMeetingMail($meetingData));


        return response()->json(['message'=>'success']); 

    }
//admin crt blogsite             
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
        $blog->user_type = "admin";
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

    // view daily works
    public function show_daily_work($type=null,$id=null){
        if($type==null && $id==null){
            $daily_works = Dailywork_details::all();
        }
        if($type!=null && $id==null){
            $daily_works = Dailywork_details::where('user_type',$type)->get();
        }
        if ($type != null && $id != null) {
            $daily_works = Dailywork_details::where('user_type', $type)->where('user_id', $id)->get();
        }

        
        
        return response()->json(['message'=>'here are daily works successfully','daily_work' => $daily_works]);
    }
    // view daily works
    public function add_off_day(){
        $developers = developer::all();
        foreach ($developers as $developer) {
            $daily_work = new Dailywork_details;
            $daily_work->user_id = $developer->id;
            $daily_work->name = $developer->name;
            $daily_work->user_type = 'developer';
            date_default_timezone_set('Asia/Dhaka');
            $daily_work->start_time = date("Y-m-d H:i:s");
            $daily_work->work_status = "offday";
            $daily_work->save();
        }
        $managers = manager::all();
        foreach ($managers as $manager) {
            $daily_work = new Dailywork_details;
            $daily_work->user_id = $manager->id;
            $daily_work->name = $manager->name;
            $daily_work->user_type = 'manager';
            date_default_timezone_set('Asia/Dhaka');
            $daily_work->start_time = date("Y-m-d H:i:s");
            $daily_work->work_status = "offday";
            $daily_work->save();
        }

            return response()->json(['message'=>'off day added']);
    }


}