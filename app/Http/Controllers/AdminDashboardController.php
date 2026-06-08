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

            // 4. Data Grafik Tren Keuangan (Pemasukan per minggu secara Real-time)
            // Mengambil semua transaksi sukses pada bulan dan tahun ini
            $transaksiBulanIni = TransaksiKas::where('status', 'sukses')
                ->whereMonth('tanggal_transaksi', Carbon::now()->month)
                ->whereYear('tanggal_transaksi', Carbon::now()->year)
                ->get();

            // Siapkan wadah untuk menampung total per minggu
            $minggu1 = 0; $minggu2 = 0; $minggu3 = 0; $minggu4 = 0;

            foreach ($transaksiBulanIni as $trx) {
                // Di sini kita menghitung tren Pemasukan saja.
                // Jika kamu ingin grafik turun saat ada pengeluaran, ubah $trx->nominal menjadi -$trx->nominal untuk pengeluaran.
                $nominal = $trx->jenis === 'pemasukan' ? $trx->nominal : 0; 
                
                $tanggal = Carbon::parse($trx->tanggal_transaksi)->day;

                // Kelompokkan berdasarkan tanggal
                if ($tanggal >= 1 && $tanggal <= 7) {
                    $minggu1 += $nominal;
                } elseif ($tanggal >= 8 && $tanggal <= 14) {
                    $minggu2 += $nominal;
                } elseif ($tanggal >= 15 && $tanggal <= 21) {
                    $minggu3 += $nominal;
                } else {
                    $minggu4 += $nominal; // Tanggal 22 sampai akhir bulan
                }
            }

            $grafik = [
                ['name' => 'Minggu 1', 'value' => $minggu1],
                ['name' => 'Minggu 2', 'value' => $minggu2],
                ['name' => 'Minggu 3', 'value' => $minggu3],
                ['name' => 'Minggu 4', 'value' => $minggu4],
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