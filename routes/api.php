<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\DeveloperController;
use App\Http\Controllers\API\ManagerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// for admin
Route::group(['prefix'=>'admin'],function($router){
    Route::post('login',[AdminController::class, 'login']);
    Route::post('register',[AdminController::class, 'register']);
});

// protected routes
Route::group(['middleware'=>['jwt.role:admin','jwt.auth'],'prefix'=>'admin'],function($router){
    Route::post('logout',[AdminController::class, 'logout']);
    Route::get('profile_details',[AdminController::class, 'profile_details']);


    // crud technology
    Route::post('add_project_technology',[AdminController::class, 'add_project_technology']);
    Route::get('show_technology',[AdminController::class, 'show_technology']);
    Route::post('edit_technology/{id}',[AdminController::class, 'edit_technology']);
    Route::get('delete_technology/{id}',[AdminController::class, 'delete_technology']);



    Route::post('add_project_type',[AdminController::class, 'add_project_type']);
    Route::post('add_project',[AdminController::class, 'add_project']);

    // admin create manager and developer
    Route::post('create_manager',[AdminController::class, 'create_manager']);
    Route::post('create_developer',[AdminController::class, 'create_developer']); 
    Route::post('create_client',[AdminController::class, 'create_client']);
    Route::post('as_developer',[DeveloperController::class, 'as_developer']);

                                              
});


// for client
Route::group(['prefix'=>'client'],function($router){
    Route::post('login',[ClientController::class, 'login']);
    Route::post('register',[ClientController::class, 'register']);
});

// protected routes
Route::group(['middleware'=>['jwt.role:client','jwt.auth'],'prefix'=>'client'],function($router){
    Route::post('logout',[ClientController::class, 'logout']);
    Route::get('profile_details',[ClientController::class, 'profile_details']);
    Route::post('edit_profile',[ClientController::class, 'edit_profile']);
});


// for developer
Route::group(['prefix'=>'developer'],function($router){
    Route::post('login',[DeveloperController::class, 'login']);
    Route::post('register',[DeveloperController::class, 'register']);
    Route::post('edit_profile',[DeveloperController::class, 'edit_profile']);


});

// protected routes
Route::group(['middleware'=>['jwt.role:developer','jwt.auth'],'prefix'=>'developer'],function($router){
    Route::post('logout',[DeveloperController::class, 'logout']);
    Route::get('profile_details',[DeveloperController::class, 'profile_details']);
});





// for manager
Route::group(['prefix'=>'manager'],function($router){
    Route::post('login',[ManagerController::class, 'login']);
    Route::post('register',[ManagerController::class, 'register']);
});
// manager create devloper and client
    Route::post('create_developer',[ManagerController::class,'create_developer']); 
    Route::post('create_client',[ManagerController::class, 'create_client']);

// protected routes
Route::group(['middleware'=>['jwt.role:manager','jwt.auth'],'prefix'=>'manager'],function($router){
    Route::post('logout',[ManagerController::class, 'logout']);
    Route::get('profile_details',[ManagerController::class, 'profile_details']);
    Route::post('edit_profile',[ManagerController::class, 'edit_profile']);
});
