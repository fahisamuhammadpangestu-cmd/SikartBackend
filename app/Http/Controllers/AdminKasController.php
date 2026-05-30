<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiKas;
use App\Models\KategoriKas;
use Illuminate\Support\Facades\Validator;

class AdminKasController extends Controller
{
    // 1. Mengambil Saldo, Kategori, dan Riwayat (Untuk tampilan awal)
    public function index()
    {
        // Hitung Saldo
        $pemasukan = TransaksiKas::where('jenis', 'pemasukan')->where('status', 'sukses')->sum('nominal');
        $pengeluaran = TransaksiKas::where('jenis', 'pengeluaran')->where('status', 'sukses')->sum('nominal');
        $saldo = $pemasukan - $pengeluaran;

        // Ambil pilihan Kategori
        $kategori = KategoriKas::all();

        // Ambil Riwayat Transaksi (beserta nama kategorinya)
        $riwayat = TransaksiKas::with('kategori')
                               ->orderBy('tanggal_transaksi', 'desc')
                               ->orderBy('created_at', 'desc')
                               ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'saldo_saat_ini' => $saldo,
                'kategori_kas' => $kategori,
                'riwayat_transaksi' => $riwayat
            ]
        ], 200);
    }

    // 2. Menyimpan Transaksi Manual (Tombol "Simpan Transaksi")
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenis' => 'required|in:pemasukan,pengeluaran',
            'tanggal_transaksi' => 'required|date',
            'kategori_id' => 'required|exists:kategori_kas,id',
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 400);
        }

        // Simpan transaksi
        $transaksi = TransaksiKas::create([
            'warga_id' => $request->user()->id, // Mengambil ID Admin yang sedang login
            'jenis' => $request->jenis,
            'tanggal_transaksi' => $request->tanggal_transaksi,
            'kategori_id' => $request->kategori_id,
            'nominal' => $request->nominal,
            'keterangan' => $request->keterangan,
            'status' => 'sukses', // Input manual admin otomatis sukses
            'metode_pembayaran' => 'manual'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Transaksi kas berhasil dicatat!',
            'data' => $transaksi
        ], 201);
    }
}