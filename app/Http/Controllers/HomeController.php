<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    //

    function index()
    {
//        dd(formatPhoneNumber('01675360166'));
        return view('welcome');
    }
}
