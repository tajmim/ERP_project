<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

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
}
