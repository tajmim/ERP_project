<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\admin;
use App\Models\manager;
use App\Models\developer;
use App\Models\User;
use App\Models\Project_Technology;
use App\Models\Project_Type;
use App\Models\Project;

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


// create manager
    public function create_manager(Request $request)
    {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:managers',
    ]);

    $password = str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);

    $user = manager::create([
        'name' => $request->input('name'),
        'email' => $request->input('email'),
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



// admin create technology
    public function add_project_technology(Request $request){
      
      $technology = new Project_Technology; 

      $technology->project_tech_name = $request->tech_name;
      $technology->project_tech_workable_person = $request->workable_person;


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







    public function add_project_type(Request $request){
      $type = new Project_Type; 
      $type->service_title = $request->service_name; 
      $type->service_experience = $request->service_experience; 
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


















}