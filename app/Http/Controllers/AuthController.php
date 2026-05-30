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
        // 1. Mengecek kelengkapan data (Validasi)
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|string|email|unique:warga', // Email tidak boleh kembar
            'no_hp' => 'required|string|max:20',
            'password' => 'required|string|min:8',
            'role' => 'nullable|in:admin,warga'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak valid',
                'errors' => $validator->errors()
            ], 400); // 400 adalah kode error dari server
        }

        // 2. Menyimpan data ke database
        $user = Warga::create([
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            // Username kita isi dengan email untuk sementara agar tidak kosong
            'username' => $request->email, 
            // Password WAJIB dienkripsi (diacak) demi keamanan
            'password' => Hash::make($request->password), 
            // Jika role dikirim, pakai itu. Jika tidak, set ke 'warga'
            'role' => $request->role ?? 'warga'
        ]);

        // 3. Membuat "Kunci Tiket" (Token) agar user langsung berstatus Login
        $token = $user->createToken('auth_token')->plainTextToken;

        // 4. Mengembalikan jawaban sukses ke Frontend / Postman
        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi admin berhasil!',
            'data' => $user,
            'access_token' => $token
        ], 201); // 201 adalah kode sukses membuat data
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
}