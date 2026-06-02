<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiKas;

class AdminLaporanController extends Controller
{
    public function index(Request $request)
    {
        try {
            // 1. Hitung SALDO KAS SAAT INI (Keseluruhan, tidak terpengaruh filter)
            $pemasukanGlobal = TransaksiKas::where('status', 'sukses')->where('jenis', 'pemasukan')->sum('nominal');
            $pengeluaranGlobal = TransaksiKas::where('status', 'sukses')->where('jenis', 'pengeluaran')->sum('nominal');
            $saldoSaatIni = $pemasukanGlobal - $pengeluaranGlobal;

            // 2. Siapkan Mesin Pencari untuk TABEL berdasarkan filter
            $query = TransaksiKas::with(['kategori', 'warga'])->where('status', 'sukses');

            if ($request->has('tanggal') && $request->tanggal != '') {
                $query->whereDay('tanggal_transaksi', $request->tanggal);
            }
            if ($request->has('bulan') && $request->bulan != '') {
                $query->whereMonth('tanggal_transaksi', $request->bulan);
            }
            if ($request->has('tahun') && $request->tahun != '') {
                $query->whereYear('tanggal_transaksi', $request->tahun);
            }

            // Eksekusi pencarian
            $riwayat = $query->orderBy('tanggal_transaksi', 'desc')->get();

            // 3. Hitung Total Pengeluaran KHUSUS untuk periode yang difilter
            $pengeluaranPeriode = $riwayat->where('jenis', 'pengeluaran')->sum('nominal');

            return response()->json([
                'status' => 'success',
                'message' => 'Laporan keuangan berhasil diambil',
                'data' => [
                    'ringkasan' => [
                        'saldo_saat_ini' => $saldoSaatIni,
                        'total_pengeluaran' => $pengeluaranPeriode
                    ],
                    'riwayat_transaksi' => $riwayat
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}