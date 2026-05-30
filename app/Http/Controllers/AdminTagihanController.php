<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tagihan;
use Illuminate\Support\Facades\Validator;

class AdminTagihanController extends Controller
{
    // ==========================================
    // 1. READ: Mengambil Daftar Tagihan
    // ==========================================
    public function index()
    {
        // Mengambil semua tagihan, diurutkan dari yang paling baru dibuat
        $tagihan = Tagihan::orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar tagihan berhasil diambil',
            'data' => $tagihan
        ], 200);
    }

    // ==========================================
    // 2. CREATE: Membuat Tagihan Baru
    // ==========================================
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_tagihan' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 400);
        }

        // Simpan ke database
        $tagihan = Tagihan::create([
            'nama_tagihan' => $request->nama_tagihan,
            'nominal' => $request->nominal,
            'keterangan' => $request->keterangan,
            'status_sistem' => 'aktif' // Otomatis diset aktif saat baru dibuat
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Tagihan iuran berhasil dibuat!',
            'data' => $tagihan
        ], 201);
    }

    // ==========================================
    // 3. DELETE: Menghapus Tagihan
    // ==========================================
    public function destroy($id)
    {
        // Cari data tagihan berdasarkan ID yang dikirim
        $tagihan = Tagihan::find($id);

        // Jika data tidak ditemukan, beri tahu Frontend/Postman
        if (!$tagihan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tagihan tidak ditemukan'
            ], 404); // 404 adalah kode Not Found
        }

        // Jalankan perintah hapus
        $tagihan->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Tagihan iuran berhasil dihapus!'
        ], 200);
    }
}