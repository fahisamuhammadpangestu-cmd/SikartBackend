<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Warga;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // 1. Mengecek kelengkapan data (Validasi ditambahkan NIK, Username, Blok)
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'username' => 'required|string|unique:warga,username', 
            'nik' => 'required|string|unique:warga,nik',
            'blok' => 'required|string',
            'email' => 'required|string|email|unique:warga,email',
            'no_hp' => 'required|string|max:20',
            'password' => 'required|string|min:8',
            'status_warga' => 'nullable|in:Warga Tetap,Warga Kontrak,Kos'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak valid',
                'errors' => $validator->errors()
            ], 400); 
        }

        // 2. Menyimpan data ke database
        $user = Warga::create([
            'nama_lengkap' => $request->nama_lengkap,
            'username' => $request->username,
            'nik' => $request->nik,
            'blok' => $request->blok,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'password' => Hash::make($request->password), 
            'status_warga' => $request->status_warga ?? 'Warga Tetap',
            'role' => 'warga'
        ]);

        // 3. Membuat Kunci Tiket
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi warga berhasil!',
            'data' => $user,
            'access_token' => $token
        ], 201);
    }

    // ==========================================
    // PINTU MASUK KHUSUS ADMIN
    // ==========================================
    public function loginAdmin(Request $request)
    {
        $request->validate([
            'login_id' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = Warga::where('email', $request->login_id)
                     ->orWhere('username', $request->login_id)
                     ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email/Username atau Kata Sandi salah!'
            ], 401);
        }

        // --- INI KUNCI KEAMANANNYA ---
        // Jika yang login BUKAN admin, tolak mentah-mentah!
        if ($user->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak! Portal ini khusus Pengurus RT.'
            ], 403); // 403 adalah kode error Forbidden (Dilarang masuk)
        }

        $token = $user->createToken('admin_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Selamat datang Admin!',
            'data' => $user,
            'access_token' => $token
        ], 200);
    }

    // ==========================================
    // PINTU MASUK KHUSUS WARGA
    // ==========================================
    public function loginWarga(Request $request)
    {
        $request->validate([
            'login_id' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = Warga::where('email', $request->login_id)
                     ->orWhere('username', $request->login_id)
                     ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email/Username atau Kata Sandi salah!'
            ], 401);
        }

        // --- INI KUNCI KEAMANANNYA ---
        // Jika yang login BUKAN warga (misal admin nyasar), tolak!
        if ($user->role !== 'warga') {
            return response()->json([
                'status' => 'error',
                'message' => 'Akses ditolak! Ini adalah portal khusus Warga.'
            ], 403);
        }

        $token = $user->createToken('warga_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Selamat datang Warga!',
            'data' => $user,
            'access_token' => $token
        ], 200);
    }

    // ==========================================
    // PINTU MASUK UNIVERSAL (SATU UNTUK SEMUA)
    // ==========================================
    public function loginUniversal(Request $request)
    {
        $request->validate([
            'login_id' => 'required|string',
            'password' => 'required|string',
        ]);

        // Cari user berdasarkan email atau username
        $user = Warga::where('email', $request->login_id)
                     ->orWhere('username', $request->login_id)
                     ->first();

        // Cek apakah user ada dan passwordnya benar
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email/Username atau Kata Sandi salah!'
            ], 401);
        }

        // Buat token universal
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil!',
            'data' => $user, // Di dalam $user ini terdapat informasi 'role' (admin/warga)
            'access_token' => $token
        ], 200);
    }
}