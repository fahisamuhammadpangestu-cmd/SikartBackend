<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiKas;

class AdminVerifikasiController extends Controller
{
    // ==========================================
    // 1. READ: Menampilkan daftar transaksi 'pending'
    // ==========================================
    public function index()
    {
        // Ambil transaksi pending beserta data warga dan tagihannya
        $pendingTransactions = TransaksiKas::with(['warga', 'tagihan'])
                                           ->where('status', 'pending')
                                           ->orderBy('created_at', 'asc') // Yang paling lama mengantre muncul duluan
                                           ->get();

        // Menghitung total data untuk kotak "MENUNGGU X Transaksi" di UI
        $jumlahMenunggu = $pendingTransactions->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Data verifikasi berhasil diambil',
            'data' => [
                'jumlah_menunggu' => $jumlahMenunggu,
                'transaksi' => $pendingTransactions
            ]
        ], 200);
    }

    // ==========================================
    // 2. UPDATE: Mengesahkan transaksi (Ubah ke 'sukses')
    // ==========================================
    public function verify($id)
    {
        $transaksi = TransaksiKas::find($id);

        if (!$transaksi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi tidak ditemukan'
            ], 404);
        }

        if ($transaksi->status === 'sukses') {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaksi ini sudah diverifikasi sebelumnya'
            ], 400);
        }

        // Ubah status menjadi sukses
        $transaksi->update([
            'status' => 'sukses'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pembayaran berhasil diverifikasi!',
            'data' => $transaksi
        ], 200);
    }
}