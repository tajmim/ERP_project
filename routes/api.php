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


// this is a test change



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// for Admin...................................../

Route::group(['prefix'=>'admin'],function($router){
    Route::post('login',[AdminController::class, 'login']);
    Route::post('register',[AdminController::class, 'register']);
    Route::post('forget_password',[AdminController::class, 'forget_password']);
    Route::post('verify_forget_password',[AdminController::class, 'verify_forget_password']);
});

// protected routes
Route::group(['middleware'=>['jwt.role:admin','jwt.auth'],'prefix'=>'admin'],function($router){
    Route::post('logout',[AdminController::class, 'logout']);
    Route::get('profile_details',[AdminController::class, 'profile_details']);
    Route::get('show_daily_works/{type?}/{id?}',[AdminController::class, 'show_daily_work']);
    Route::get('add_off_day',[AdminController::class, 'add_off_day']);


// crud technology
    Route::post('add_project_technology',[AdminController::class, 'add_project_technology']);
    Route::get('show_technology',[AdminController::class, 'show_technology']);
    Route::post('edit_technology/{id}',[AdminController::class, 'edit_technology']);
    Route::get('delete_technology/{id}',[AdminController::class, 'delete_technology']);
// end crud technology
    Route::post('add_project_type',[AdminController::class, 'add_project_type']);
    Route::post('add_project',[AdminController::class, 'add_project']);
// start crud blosite
    Route::post('create_blog',[AdminController::class, 'create_blog']);
    Route::get('show_blog',[AdminController::class, 'show_blog']);
    Route::post('edit_blog/{id}',[AdminController::class, 'edit_blog']);
    Route::get('delete_blog/{id}',[AdminController::class, 'delete_blog']);


    // admin create manager and developer
    Route::post('create_manager',[AdminController::class, 'create_manager']);
    Route::post('create_developer',[AdminController::class, 'create_developer']); 
    Route::post('create_client',[AdminController::class, 'create_client']);
    Route::get('login_as_developer/{id}',[DeveloperController::class, 'login_as_developer']);
    Route::post('create_meeting',[AdminController::class, 'create_meeting']);
    Route::get('meeting_share/{id}',[AdminController::class, 'meeting_share']);                               
});

// start manager...................................//

Route::group(['prefix'=>'manager'],function($router){
    Route::post('login',[ManagerController::class, 'login']);
    Route::post('register',[ManagerController::class, 'register']);
});
//manager create devloper and client
    Route::post('create_developer',[ManagerController::class,'create_developer']); 
    Route::post('create_client',[ManagerController::class, 'create_client']);

// protected routes
Route::group(['middleware'=>['jwt.role:manager','jwt.auth'],'prefix'=>'manager'],function($router){
    Route::post('logout',[ManagerController::class, 'logout']);
    Route::get('profile_details',[ManagerController::class, 'profile_details']);
    Route::post('edit_profile',[ManagerController::class, 'edit_profile']);
    Route::get('start_time',[ManagerController::class, 'start_time']);
    Route::get('pause_time',[ManagerController::class, 'pause_time']);
    Route::get('resume_time',[ManagerController::class, 'resume_time']);
    Route::get('end_time',[ManagerController::class, 'end_time']);
    Route::post('add_work_note',[ManagerController::class, 'add_work_note']);

    //crud blosite
    
    Route::post('create_blog',[ManagerController::class, 'create_blog']);
    Route::get('show_blog',[ManagerController::class, 'show_blog']);
    Route::post('edit_blog/{id}',[ManagerController::class, 'edit_blog']);
    Route::get('delete_blog/{id}',[ManagerController::class, 'delete_blog']);
});



//start client........................................../

Route::group(['prefix'=>'client'],function($router){
    Route::post('login',[ClientController::class, 'login']);
    Route::post('register',[ClientController::class, 'register']);
});

// protected routes
Route::group(['middleware'=>['jwt.role:client','jwt.auth'],'prefix'=>'client'],function($router){
    Route::post('logout',[ClientController::class, 'logout']);
    Route::get('profile_details',[ClientController::class, 'profile_details']);
    Route::post('edit_profile',[ClientController::class, 'edit_profile']);
    Route::get('show_blog',[ClientController::class, 'show_blog']);
    Route::get('like/{id}',[ClientController::class, 'add_like']);
    Route::post('comment/{id}',[ClientController::class, 'add_comment']);
    Route::get('blog_details/{id}',[ClientController::class, 'add_viewer']);
});


// developer start............................/

Route::group(['prefix'=>'developer'],function($router){
    Route::post('login',[DeveloperController::class, 'login']);
    Route::post('register',[DeveloperController::class, 'register']);
    Route::post('edit_profile',[DeveloperController::class, 'edit_profile']);

});
// protected routes
Route::group(['middleware'=>['jwt.role:developer','jwt.auth'],'prefix'=>'developer'],function($router){
    Route::post('logout',[DeveloperController::class, 'logout']);
    Route::get('profile_details',[DeveloperController::class, 'profile_details']);
    Route::get('show_blog',[DeveloperController::class, 'show_blog']);
    Route::get('like/{id}',[DeveloperController::class, 'add_like']);
    Route::post('comment/{id}',[DeveloperController::class, 'add_comment']);
    Route::get('blog_details/{id}',[DeveloperController::class, 'blog_details']);
    Route::get('start_time',[DeveloperController::class, 'start_time']);
    Route::get('pause_time',[DeveloperController::class, 'pause_time']);
    Route::get('resume_time',[DeveloperController::class, 'resume_time']);
    Route::get('end_time',[DeveloperController::class, 'end_time']);
    Route::post('add_work_note',[DeveloperController::class, 'add_work_note']);

});

// developer end

