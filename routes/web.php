<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\LupaPasswordController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\DokterController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/artikel', function () {
    return view('Admin.artikel');
});

Route::get('/dashboard', [DashboardController::class, 'index']);
Route::get('/login', [LoginController::class, 'index']);
Route::get('/lupapassword', [LupaPasswordController::class, 'index']);
Route::get('/profile', [ProfileController::class, 'index']);
Route::get('admin/dokter', [DokterController::class, 'index']);