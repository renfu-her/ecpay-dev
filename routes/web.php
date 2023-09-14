<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\IndexController;

Route::get('/ecpay', [IndexController::class, 'ecpay']);
