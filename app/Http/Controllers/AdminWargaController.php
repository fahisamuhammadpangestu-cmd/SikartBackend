<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Warga;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminWargaController extends Controller
{
    // ==========================================
    // 1. READ: Mengambil daftar warga
    // ==========================================
    public function index()
    {
        // Ambil data yang role-nya 'warga' saja (jangan tampilkan data sesama admin di tabel ini)
        $warga = Warga::where('role', 'warga')
                      ->orderBy('created_at', 'desc')
                      ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Data daftar warga berhasil diambil',
            'data' => $warga
        ], 200);
    }

    // ==========================================
    // 2. CREATE: Menambah warga baru
    // ==========================================
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'nik' => 'nullable|string|max:20',
            'blok' => 'nullable|string|max:255',
            'email' => 'required|string|email|unique:warga',
            'no_hp' => 'nullable|string|max:20',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error', 
                'errors' => $validator->errors()
            ], 400);
        }

        $warga = Warga::create([
            'nama_lengkap' => $request->nama_lengkap,
            'nik' => $request->nik,
            'blok' => $request->blok,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'username' => $request->email, 
            'password' => Hash::make($request->password),
            'role' => 'warga' // Paksa menjadi warga
        ]);

        return response()->json([
            'status' => 'success', 
            'message' => 'Warga berhasil ditambahkan', 
            'data' => $warga
        ], 201);
    }

    // ==========================================
    // 3. UPDATE: Mengubah data warga
    // ==========================================
    public function update(Request $request, $id)
    {
        $warga = Warga::find($id);

        if (!$warga) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Data warga tidak ditemukan'
            ], 404);
        }

        // Update data (jika tidak diisi, gunakan data yang lama)
        $warga->update([
            'nama_lengkap' => $request->nama_lengkap ?? $warga->nama_lengkap,
            'nik' => $request->nik ?? $warga->nik,
            'blok' => $request->blok ?? $warga->blok,
            'no_hp' => $request->no_hp ?? $warga->no_hp,
        ]);

        return response()->json([
            'status' => 'success', 
            'message' => 'Data warga berhasil diubah', 
            'data' => $warga
        ], 200);
    }

    // ==========================================
    // 4. DELETE: Menghapus data warga
    // ==========================================
    public function destroy($id)
    {
        $warga = Warga::find($id);

        if (!$warga) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Data warga tidak ditemukan'
            ], 404);
        }

        $warga->delete();

        return response()->json([
            'status' => 'success', 
            'message' => 'Data warga berhasil dihapus'
        ], 200);
    }
}