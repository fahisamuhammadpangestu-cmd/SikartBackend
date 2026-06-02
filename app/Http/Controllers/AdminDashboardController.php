<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiKas;
use Carbon\Carbon; // Digunakan untuk memanipulasi tanggal

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            // 1. Data dari Manajemen Kas
            $pemasukan = TransaksiKas::where('jenis', 'pemasukan')->where('status', 'sukses')->sum('nominal');
            $pengeluaran = TransaksiKas::where('jenis', 'pengeluaran')->where('status', 'sukses')->sum('nominal');
            $saldo = $pemasukan - $pengeluaran;

            // 2. Data dari Verifikasi
            $butuhVerifikasi = TransaksiKas::where('status', 'pending')->count();

            // 3. Transaksi Terakhir
           $transaksiTerakhir = TransaksiKas::with(['kategori', 'tagihan'])
                                             ->orderBy('created_at', 'desc')
                                             ->take(5)
                                             ->get();
                                             
            // 4. Data Grafik Tren Keuangan (Dibagi per minggu dalam bulan ini)
            // Mengambil pemasukan sukses bulan ini, lalu disimulasikan ke 4 titik (Minggu 1 - 4)
            $bulanIni = TransaksiKas::where('jenis', 'pemasukan')
                            ->where('status', 'sukses')
                            ->whereMonth('tanggal_transaksi', Carbon::now()->month)
                            ->sum('nominal');
                            
            // Pembagian sederhana untuk visualisasi (Bisa disesuaikan nanti dengan query per-minggu yang riil)
            $grafik = [
                ['name' => 'Minggu 1', 'value' => $bulanIni * 0.2],
                ['name' => 'Minggu 2', 'value' => $bulanIni * 0.5],
                ['name' => 'Minggu 3', 'value' => $bulanIni * 0.1],
                ['name' => 'Minggu 4', 'value' => $bulanIni * 0.2],
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Data Dashboard Admin berhasil diambil',
                'data' => [
                    'ringkasan' => [
                        'total_pemasukan' => $pemasukan, // Tetap dikirim meski disembunyikan di UI
                        'total_pengeluaran' => $pengeluaran,
                        'saldo_akhir' => $saldo,
                        'butuh_verifikasi' => $butuhVerifikasi
                    ],
                    'transaksi_terakhir' => $transaksiTerakhir,
                    'grafik' => $grafik // Mengirim data grafik
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}