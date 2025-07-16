<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;

//api for register admin
Route::post('/admin/register', [AdminAuthController::class, 'register']);
