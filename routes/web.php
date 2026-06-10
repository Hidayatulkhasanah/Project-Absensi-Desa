<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/login.html');
});


Route::get('/dashboard', function () {
    return redirect('/admin-absensi.html');
});
//admin

Route::get('/absensi', function () {
    return redirect('/absensi.html');
});
//user
