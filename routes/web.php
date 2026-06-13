<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return file_get_contents(public_path('login.html'));
});

Route::get('/dashboard', function () {
    return file_get_contents(public_path('admin-absensi.html'));
});

Route::get('/admin-absensi', function () {
    return file_get_contents(public_path('admin-absensi.html'));
});

Route::get('/absensi', function () {
    return file_get_contents(public_path('absensi.html'));
});