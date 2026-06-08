<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiKas;

class WargaLaporanController extends Controller
{
    public function index(Request $request)
    {
        // 1. Hitung SALDO AKHIR KESELURUHAN (Uang riil RT saat ini)
        // Dihitung dari seluruh transaksi dari awal RT berdiri sampai detik ini
        $pemasukanTotal = TransaksiKas::where('status', 'sukses')->where('jenis', 'pemasukan')->sum('nominal');
        $pengeluaranTotal = TransaksiKas::where('status', 'sukses')->where('jenis', 'pengeluaran')->sum('nominal');
        $saldoAkhir = $pemasukanTotal - $pengeluaranTotal;

        // 2. Siapkan Mesin Pencari untuk TABEL (Berdasarkan filter)
        $query = TransaksiKas::with(['kategori', 'tagihan'])->where('status', 'sukses');

        // Filter Bulan (MM)
        if ($request->has('bulan') && $request->bulan != '') {
            $query->whereMonth('tanggal_transaksi', $request->bulan);
        }

        // Filter Tahun (YYYY)
        if ($request->has('tahun') && $request->tahun != '') {
            $query->whereYear('tanggal_transaksi', $request->tahun);
        }

        // Filter Jenis Transaksi (pemasukan / pengeluaran)
        if ($request->has('jenis') && $request->jenis != '') {
            $query->where('jenis', $request->jenis);
        }

        // 3. Eksekusi pencarian, urutkan dari tanggal paling lama ke baru (asc) 
        // Agar pembacaan laporan mengalir dari awal bulan ke akhir bulan
        $laporanPeriode = $query->orderBy('tanggal_transaksi', 'asc')->get();

        // 4. Hitung Pemasukan & Pengeluaran KHUSUS PERIODE yang difilter
        // Ini untuk mengisi kotak "Total Masuk" dan "Total Keluar" warna hijau dan merah di UI-mu
        $masukPeriode = $laporanPeriode->where('jenis', 'pemasukan')->sum('nominal');
        $keluarPeriode = $laporanPeriode->where('jenis', 'pengeluaran')->sum('nominal');

        // 5. Kirim balasan ke Frontend
        return response()->json([
            'status' => 'success',
            'message' => 'Laporan transparansi kas berhasil diambil',
            'data' => [
                'ringkasan' => [
                    'total_masuk_periode' => $masukPeriode,
                    'total_keluar_periode' => $keluarPeriode,
                    'saldo_akhir_keseluruhan' => $saldoAkhir
                ],
                'detail_transaksi' => $laporanPeriode
            ]
        ], 200);
    }
}