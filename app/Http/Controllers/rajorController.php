<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class rajorController extends Controller
{
    public function payment(){
        return view('form');
    }
}
