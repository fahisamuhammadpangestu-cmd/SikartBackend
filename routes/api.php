<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminWargaController;
use App\Http\Controllers\AdminKasController;
use App\Http\Controllers\AdminVerifikasiController;
use App\Http\Controllers\AdminTagihanController;
use App\Http\Controllers\AdminLaporanController;
use App\Http\Controllers\WargaDashboardController;
use App\Http\Controllers\WargaPembayaranController;
use App\Http\Controllers\WargaRiwayatController;
use App\Http\Controllers\WargaLaporanController;
use App\Http\Controllers\ProfileController;

Route::post('/register', [AuthController::class, 'register']);

// URL Pintu Admin
Route::post('/admin/login', [AuthController::class, 'loginAdmin']);

// URL Pintu Warga
Route::post('/warga/login', [AuthController::class, 'loginWarga']);


// Group route khusus untuk yang sudah Login
Route::middleware('auth:sanctum')->group(function () {
    
    // API khusus Dashboard Admin
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);

    // API Manajemen Warga (BARU)
    Route::get('/admin/warga', [AdminWargaController::class, 'index']);          // Read
    Route::post('/admin/warga', [AdminWargaController::class, 'store']);         // Create
    Route::put('/admin/warga/{id}', [AdminWargaController::class, 'update']);    // Update
    Route::delete('/admin/warga/{id}', [AdminWargaController::class, 'destroy']);// Delete

    // API Manajemen Kas (BARU)
    Route::get('/admin/kas', [AdminKasController::class, 'index']);  // Ambil Data Halaman
    Route::post('/admin/kas', [AdminKasController::class, 'store']); // Simpan Transaksi Baru

    // API Verifikasi Pembayaran
    Route::get('/admin/verifikasi', [AdminVerifikasiController::class, 'index']);      // Lihat daftar pending
    Route::put('/admin/verifikasi/{id}', [AdminVerifikasiController::class, 'verify']);// Tombol verifikasi
    
    // API Kelola Tagihan Iuran
    Route::get('/admin/tagihan', [AdminTagihanController::class, 'index']);  // Lihat daftar tagihan
    Route::post('/admin/tagihan', [AdminTagihanController::class, 'store']); // Buat tagihan baru
    Route::delete('/admin/tagihan/{id}', [AdminTagihanController::class, 'destroy']);

    // API Laporan Keuangan (Rekap Bulanan)
    Route::get('/admin/laporan', [AdminLaporanController::class, 'index']);

    // Profil Admin
    Route::get('/admin/profile', [ProfileController::class, 'show']);
    Route::put('/admin/profile', [ProfileController::class, 'update']);

    // ==========================================
    // API KHUSUS WARGA / USER
    // ==========================================
    Route::get('/warga/dashboard', [WargaDashboardController::class, 'index']);

    // API Pembayaran Warga
    Route::post('/warga/pembayaran', [WargaPembayaranController::class, 'store']);

    // API Riwayat Pembayaran Warga
    Route::get('/warga/riwayat', [WargaRiwayatController::class, 'index']);

    // API Laporan Transparansi Kas untuk Warga
    Route::get('/warga/laporan', [WargaLaporanController::class, 'index']);

    // Profil Warga
    Route::get('/warga/profile', [ProfileController::class, 'show']);
    Route::put('/warga/profile', [ProfileController::class, 'update']);

});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
