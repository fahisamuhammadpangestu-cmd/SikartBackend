<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiKas;

class WargaRiwayatController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil data warga yang sedang login
        $user = $request->user();

        // 2. Ambil semua riwayat transaksi HANYA untuk warga ini
        // Kita juga memanggil relasi 'tagihan' agar tahu ini bayar iuran bulan apa
        $riwayat = TransaksiKas::with('tagihan')
                               ->where('warga_id', $user->id)
                               ->orderBy('created_at', 'desc') // Urutkan dari yang terbaru
                               ->get();

        // 3. Hitung Total Iuran Terbayar (Hanya dihitung jika statusnya sudah 'sukses' / berhasil)
        $totalTerbayar = $riwayat->where('status', 'sukses')->sum('nominal');

        // 4. Cari Tanggal Transaksi Terakhir
        // Jika ada riwayat, ambil tanggal elemen pertama (karena sudah diurutkan dari yang terbaru)
        $transaksiTerakhir = $riwayat->first() ? $riwayat->first()->created_at->format('d M Y') : '-';

        // 5. Susun format balasan JSON untuk Frontend
        return response()->json([
            'status' => 'success',
            'message' => 'Data riwayat pembayaran berhasil diambil',
            'data' => [
                'ringkasan' => [
                    'total_terbayar' => $totalTerbayar,
                    'transaksi_terakhir' => $transaksiTerakhir,
                    'status_keanggotaan' => 'Aktif & Lunas' // Bisa kita buat dinamis nanti jika ada aturan khusus
                ],
                'detail_riwayat' => $riwayat
            ]
        ], 200);
    }
}