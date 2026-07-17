<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'login');

Route::view('/dashboard', 'admin-absensi');
Route::view('/admin-absensi', 'admin-absensi');

Route::view('/absensi', 'absensi');

Route::view('/sppd', 'sppd.index')->name('sppd.index');
Route::view('/sppd/{id}', 'sppd.show')->name('sppd.show');
