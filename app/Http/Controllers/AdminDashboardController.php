<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiKas;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Hitung Total Pemasukan (hanya yang statusnya sukses)
        $pemasukan = TransaksiKas::where('jenis', 'pemasukan')
                                 ->where('status', 'sukses')
                                 ->sum('nominal');

        // 2. Hitung Total Pengeluaran (hanya yang statusnya sukses)
        $pengeluaran = TransaksiKas::where('jenis', 'pengeluaran')
                                   ->where('status', 'sukses')
                                   ->sum('nominal');

        // 3. Hitung Saldo Akhir
        $saldo = $pemasukan - $pengeluaran;

        // 4. Hitung Jumlah Transaksi yang Butuh Verifikasi (status pending)
        $butuhVerifikasi = TransaksiKas::where('status', 'pending')->count();

        // 5. Ambil 5 Transaksi Terakhir untuk list di sebelah kanan
        $transaksiTerakhir = TransaksiKas::orderBy('created_at', 'desc')
                                         ->take(5)
                                         ->get();

        // 6. Susun jawaban untuk dikirim ke Frontend (ReactJS)
        return response()->json([
            'status' => 'success',
            'message' => 'Data Dashboard Admin berhasil diambil',
            'data' => [
                'ringkasan' => [
                    'total_pemasukan' => $pemasukan,
                    'total_pengeluaran' => $pengeluaran,
                    'saldo_akhir' => $saldo,
                    'butuh_verifikasi' => $butuhVerifikasi
                ],
                'transaksi_terakhir' => $transaksiTerakhir
            ]
        ], 200);
    }
}