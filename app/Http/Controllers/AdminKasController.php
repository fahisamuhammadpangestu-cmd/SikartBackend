<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiKas;
use Illuminate\Support\Facades\Validator;

class AdminKasController extends Controller
{
    // ==========================================
    // 1. READ: Tampil Data & Hitung Saldo
    // ==========================================
    public function index()
    {
        try {
            // Hitung Saldo dan Pengeluaran
            $pemasukan = TransaksiKas::where('jenis', 'pemasukan')->where('status', 'sukses')->sum('nominal');
            $pengeluaran = TransaksiKas::where('jenis', 'pengeluaran')->where('status', 'sukses')->sum('nominal');
            $saldo = $pemasukan - $pengeluaran;

            // Ambil Riwayat 
            $riwayat = TransaksiKas::with(['kategori', 'tagihan'])
                                   ->orderBy('tanggal_transaksi', 'desc')
                                   ->orderBy('created_at', 'desc')
                                   ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'saldo_saat_ini' => $saldo,
                    'total_kas_keluar' => $pengeluaran,
                    'riwayat_transaksi' => $riwayat
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ==========================================
    // 2. CREATE: Simpan Transaksi Baru
    // ==========================================
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'jenis' => 'required|in:pemasukan,pengeluaran',
                'tanggal_transaksi' => 'required|date',
                'keterangan' => 'required|string', 
                'nominal' => 'required|numeric|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 400);
            }

            $transaksi = TransaksiKas::create([
                'warga_id' => $request->user()->id, 
                'jenis' => $request->jenis,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'kategori_id' => null, // Karena sekarang manual teks
                'nominal' => $request->nominal,
                'keterangan' => $request->keterangan,
                'status' => 'sukses', 
                'metode_pembayaran' => 'manual'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi kas berhasil dicatat!',
                'data' => $transaksi
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ==========================================
    // 3. UPDATE: Edit Transaksi
    // ==========================================
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'jenis' => 'required|in:pemasukan,pengeluaran',
                'tanggal_transaksi' => 'required|date',
                'keterangan' => 'required|string', 
                'nominal' => 'required|numeric|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 400);
            }

            $transaksi = TransaksiKas::find($id);
            
            if (!$transaksi) {
                return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
            }

            $transaksi->update([
                'jenis' => $request->jenis,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'nominal' => $request->nominal,
                'keterangan' => $request->keterangan,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil diperbarui!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ==========================================
    // 4. DELETE: Hapus Transaksi
    // ==========================================
    public function destroy($id)
    {
        try {
            $transaksi = TransaksiKas::find($id);
            
            if (!$transaksi) {
                return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
            }

            $transaksi->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil dihapus!'
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}