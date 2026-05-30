<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiKas;

class AdminLaporanController extends Controller
{
    public function index(Request $request)
    {
        // 1. Siapkan "Mesin Pencari" awal: Ambil transaksi yang sudah 'sukses'
        $query = TransaksiKas::with(['kategori', 'warga'])->where('status', 'sukses');

        // 2. Cek apakah ada Filter Tanggal (DD) dari Postman/Frontend
        if ($request->has('tanggal') && $request->tanggal != '') {
            $query->whereDay('tanggal_transaksi', $request->tanggal);
        }

        // 3. Cek apakah ada Filter Bulan (MM)
        if ($request->has('bulan') && $request->bulan != '') {
            $query->whereMonth('tanggal_transaksi', $request->bulan);
        }

        // 4. Cek apakah ada Filter Tahun (YYYY)
        if ($request->has('tahun') && $request->tahun != '') {
            $query->whereYear('tanggal_transaksi', $request->tahun);
        }

        // 5. Eksekusi pencarian dan urutkan dari yang paling baru
        $riwayat = $query->orderBy('tanggal_transaksi', 'desc')->get();

        // 6. Hitung Total Pemasukan & Pengeluaran dari data yang sudah disaring
        // Kita menggunakan fitur "Collection" Laravel agar perhitungannya cepat
        $totalPemasukan = $riwayat->where('jenis', 'pemasukan')->sum('nominal');
        $totalPengeluaran = $riwayat->where('jenis', 'pengeluaran')->sum('nominal');
        $saldoAkhir = $totalPemasukan - $totalPengeluaran;

        // 7. Kembalikan data dalam bentuk JSON
        return response()->json([
            'status' => 'success',
            'message' => 'Laporan keuangan berhasil diambil',
            'data' => [
                'ringkasan' => [
                    'total_pemasukan' => $totalPemasukan,
                    'total_pengeluaran' => $totalPengeluaran,
                    'saldo_akhir' => $saldoAkhir
                ],
                'riwayat_transaksi' => $riwayat
            ]
        ], 200);
    }
}