<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\SppdController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PegawaiController;

// ==========================================
// PUBLIC — Tidak perlu token
// ==========================================
Route::post('/login', [AuthController::class, 'login']);

// ==========================================
// SEMUA USER YANG LOGIN
// ==========================================
Route::middleware('auth.custom')->group(function () {
    Route::post('/logout',           [AuthController::class, 'logout']);
    Route::get('/profile',           [AuthController::class, 'profile']);
    Route::post('/change-password',  [AuthController::class, 'changePassword']); // TAMBAH INI
    Route::get('/dashboard',         [DashboardController::class, 'index']);

    // Absensi milik user sendiri
    Route::post('/absensi/checkin',  [AbsensiController::class, 'checkin']);
    Route::post('/absensi/checkout', [AbsensiController::class, 'checkout']);
    Route::get('/absensi/today',     [AbsensiController::class, 'today']);
    Route::get('/absensi/riwayat',   [AbsensiController::class, 'riwayat']);
    Route::get('/absensi/laporan',   [AbsensiController::class, 'laporan']);
});

// ==========================================
// OPERATOR & ADMIN
// ==========================================
Route::middleware('auth.custom:operator')->group(function () {
    // Kelola Absensi
    Route::get('/absensi',           [AbsensiController::class, 'index']);
    Route::put('/absensi/{id}',      [AbsensiController::class, 'update']);
    Route::delete('/absensi/{id}',   [AbsensiController::class, 'destroy']);

    // Kelola SPPD
    Route::get('/sppd',                  [SppdController::class, 'index']);
    Route::post('/sppd',                 [SppdController::class, 'store']);
    Route::put('/sppd/{id}/verifikasi',  [SppdController::class, 'verifikasi']);
    Route::delete('/sppd/{id}',          [SppdController::class, 'destroy']);

    // Laporan
    Route::get('/laporan/bulanan',       [LaporanController::class, 'bulanan']);
    Route::get('/laporan/rekap-pegawai', [LaporanController::class, 'rekapPegawai']);
});

// ==========================================
// ADMIN ONLY
// ==========================================
Route::middleware('auth.custom:admin')->group(function () {
    Route::get('/pegawai',         [PegawaiController::class, 'index']);
    Route::post('/pegawai',        [PegawaiController::class, 'store']);
    Route::put('/pegawai/{id}',    [PegawaiController::class, 'update']);
    Route::delete('/pegawai/{id}', [PegawaiController::class, 'destroy']);
});