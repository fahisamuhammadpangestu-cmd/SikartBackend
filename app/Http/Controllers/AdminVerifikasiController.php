<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiKas;
use App\Models\Tagihan;

class AdminVerifikasiController extends Controller
{
    // ==========================================
    // 1. READ: Mengambil Data Verifikasi & Riwayat
    // ==========================================
    public function index(Request $request)
    {
        try {
            // Default ke 'all' jika tidak ada request
            $tagihanId = $request->query('tagihan_id', 'all'); 
            $tagihanList = Tagihan::orderBy('created_at', 'desc')->get();

            // Query dasar
            $queryPending = TransaksiKas::with(['warga', 'tagihan'])->where('status', 'pending');
            $queryHistory = TransaksiKas::with(['warga', 'tagihan'])->whereIn('status', ['sukses', 'gagal']);
            $queryTotalMasuk = TransaksiKas::where('status', 'sukses')->where('jenis', 'pemasukan');

            // LOGIKA FILTER YANG SUDAH DIPERBAIKI
            if ($tagihanId !== 'all') {
                if ($tagihanId === 'lainnya') {
                    // Cari yang tagihan_id-nya NULL (Midtrans opsi 'Lainnya')
                    $queryPending->whereNull('tagihan_id');
                    $queryHistory->whereNull('tagihan_id');
                    $queryTotalMasuk->whereNull('tagihan_id');
                } else {
                    // Cari tagihan spesifik berdasarkan ID
                    $queryPending->where('tagihan_id', $tagihanId);
                    $queryHistory->where('tagihan_id', $tagihanId);
                    $queryTotalMasuk->where('tagihan_id', $tagihanId);
                }
            }

            $pendingTransactions = $queryPending->orderBy('created_at', 'asc')->get();
            $historyTransactions = $queryHistory->orderBy('updated_at', 'desc')->get();
            $totalNominalMasuk = $queryTotalMasuk->sum('nominal');

            return response()->json([
                'status' => 'success',
                'message' => 'Data verifikasi berhasil diambil',
                'data' => [
                    'jumlah_menunggu' => $pendingTransactions->count(),
                    'total_nominal_masuk' => $totalNominalMasuk,
                    'tagihan_list' => $tagihanList,
                    'transaksi_pending' => $pendingTransactions,
                    'transaksi_riwayat' => $historyTransactions,
                    'tagihan_aktif_id' => $tagihanId
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ==========================================
    // 2. UPDATE: Terima atau Tolak Pembayaran
    // ==========================================
    public function verify(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:sukses,gagal'
            ]);

            $transaksi = TransaksiKas::find($id);

            if (!$transaksi) {
                return response()->json(['status' => 'error', 'message' => 'Transaksi tidak ditemukan'], 404);
            }

            if ($transaksi->status !== 'pending') {
                return response()->json(['status' => 'error', 'message' => 'Transaksi ini sudah diproses sebelumnya.'], 400);
            }

            $transaksi->update([
                'status' => $request->status
            ]);

            $pesan = $request->status === 'sukses' ? 'Pembayaran berhasil diterima!' : 'Pembayaran ditolak.';

            return response()->json([
                'status' => 'success',
                'message' => $pesan,
                'data' => $transaksi
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}