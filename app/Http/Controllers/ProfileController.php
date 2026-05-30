<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Warga;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    // ==========================================
    // 1. READ: Menampilkan Profil Saat Ini
    // ==========================================
    public function show(Request $request)
    {
        // $request->user() otomatis mengambil data user (Admin/Warga) dari Token yang dipakai
        return response()->json([
            'status' => 'success',
            'message' => 'Data profil berhasil diambil',
            'data' => $request->user()
        ], 200);
    }

    // ==========================================
    // 2. UPDATE: Menyimpan Perubahan Profil
    // ==========================================
    public function update(Request $request)
    {
        $user = $request->user(); // Ambil data user yang sedang login

        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'nullable|string|max:255',
            'no_hp' => 'nullable|string|max:20',
            'blok' => 'nullable|string|max:255',
            // Pengecekan email unik, TAPI kecualikan email milik dia sendiri agar tidak eror
            'email' => 'nullable|string|email|unique:warga,email,' . $user->id,
            'password' => 'nullable|string|min:8' // Password boleh kosong jika tidak ingin diubah
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 400);
        }

        // Siapkan data yang mau diupdate
        $dataUpdate = [
            'nama_lengkap' => $request->nama_lengkap ?? $user->nama_lengkap,
            'no_hp' => $request->no_hp ?? $user->no_hp,
            'blok' => $request->blok ?? $user->blok,
            'email' => $request->email ?? $user->email,
        ];

        // Jika dia mengisi kolom password baru, maka kita acak (hash) lalu masukkan ke data update
        if ($request->filled('password')) {
            $dataUpdate['password'] = Hash::make($request->password);
        }

        // Simpan ke database (Menggunakan query builder agar model ter-refresh)
        Warga::where('id', $user->id)->update($dataUpdate);

        // Ambil data terbaru setelah diupdate
        $userTerbaru = Warga::find($user->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Profil berhasil diperbarui!',
            'data' => $userTerbaru
        ], 200);
    }
}