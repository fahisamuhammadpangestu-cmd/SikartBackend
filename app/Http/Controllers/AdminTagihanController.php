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
        try {
            // Mengambil semua tagihan dari yang terbaru
            $tagihan = Tagihan::orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Daftar tagihan berhasil diambil',
                'data' => $tagihan
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ==========================================
    // 2. CREATE: Membuat Tagihan Baru
    // ==========================================
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_tagihan' => 'required|string|max:255',
                'nominal' => 'required|numeric|min:1',
                'keterangan' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 400);
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
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ==========================================
    // 3. UPDATE: Mengedit Tagihan (BARU)
    // ==========================================
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_tagihan' => 'required|string|max:255',
                'nominal' => 'required|numeric|min:1',
                'keterangan' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 400);
            }

            $tagihan = Tagihan::find($id);

            if (!$tagihan) {
                return response()->json(['status' => 'error', 'message' => 'Data tagihan tidak ditemukan'], 404);
            }

            // Lakukan update data
            $tagihan->update([
                'nama_tagihan' => $request->nama_tagihan,
                'nominal' => $request->nominal,
                'keterangan' => $request->keterangan,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Tagihan iuran berhasil diperbarui!',
                'data' => $tagihan
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ==========================================
    // 4. DELETE: Menghapus Tagihan
    // ==========================================
    public function destroy($id)
    {
        try {
            $tagihan = Tagihan::find($id);

            if (!$tagihan) {
                return response()->json(['status' => 'error', 'message' => 'Data tagihan tidak ditemukan'], 404);
            }

            $tagihan->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Tagihan iuran berhasil dihapus!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}