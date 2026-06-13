<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\SppdController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PegawaiController;

Route::prefix('api')->group(function () {

    // ==========================================
    // PUBLIC — Tidak perlu token
    // ==========================================
    Route::post('/login', [AuthController::class, 'login']);

    // ==========================================
    // SEMUA USER YANG LOGIN (admin & user)
    // ==========================================
    Route::middleware('auth.custom')->group(function () {
        Route::post('/logout',          [AuthController::class, 'logout']);
        Route::get('/profile',          [AuthController::class, 'profile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::get('/dashboard',        [DashboardController::class, 'index']);

        // Absensi milik sendiri
        Route::post('/absensi/checkin',  [AbsensiController::class, 'checkin']);
        Route::post('/absensi/checkout', [AbsensiController::class, 'checkout']);
        Route::get('/absensi/today',     [AbsensiController::class, 'today']);
        Route::get('/absensi/riwayat',   [AbsensiController::class, 'riwayat']);
        Route::get('/absensi/laporan',   [AbsensiController::class, 'laporan']);
    });

    // ==========================================
    // ADMIN ONLY
    // ==========================================
    Route::middleware('auth.custom:admin')->group(function () {
        Route::get('/absensi',         [AbsensiController::class, 'index']);
        Route::post('/absensi',        [AbsensiController::class, 'store']);
        Route::get('/absensi/{id}',    [AbsensiController::class, 'show']);
        Route::put('/absensi/{id}',    [AbsensiController::class, 'update']);
        Route::delete('/absensi/{id}', [AbsensiController::class, 'destroy']);

        Route::get('/sppd',                 [SppdController::class, 'index']);
        Route::post('/sppd',                [SppdController::class, 'store']);
        Route::put('/sppd/{id}/verifikasi', [SppdController::class, 'verifikasi']);
        Route::delete('/sppd/{id}',         [SppdController::class, 'destroy']);

        // Laporan — View (JSON)
        Route::get('/laporan/bulanan',       [LaporanController::class, 'bulanan']);
        Route::get('/laporan/rekap-pegawai', [LaporanController::class, 'rekapPegawai']);

        // Laporan — Export (File Excel)
        Route::get('/laporan/export-bulanan',       [LaporanController::class, 'exportBulanan']);
        Route::get('/laporan/export-rekap-pegawai', [LaporanController::class, 'exportRekapPegawai']);

        Route::get('/pegawai',         [PegawaiController::class, 'index']);
        Route::post('/pegawai',        [PegawaiController::class, 'store']);
        Route::put('/pegawai/{id}',    [PegawaiController::class, 'update']);
        Route::delete('/pegawai/{id}', [PegawaiController::class, 'destroy']);
    });

});