<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\manager;
use App\Models\developer;
use App\Models\User;

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

        $user = manager::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password) ]
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
}
