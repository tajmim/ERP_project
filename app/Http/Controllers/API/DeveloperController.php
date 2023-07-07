<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\developer;

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

        $user = developer::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password) ]
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
    public function as_developer(Request $request)
    {

        $credential = $request->only('email');

        
            if (!$token = Auth::attempt($credential)) {
                // Invalid credentials
                return response()->json(['error' => 'Unauthorized'], 401);
            }
    

        // Login successful, return the generated token
        return $this->createNewToken($token);
        

    }
}
