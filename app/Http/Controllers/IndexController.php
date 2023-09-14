<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IndexController extends Controller
{

    public function ecpay()
    {
        return view('ecpay');
    }
}
