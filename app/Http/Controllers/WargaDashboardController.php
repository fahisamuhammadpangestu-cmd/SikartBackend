<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiKas;

class WargaDashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            // 1. Ambil data warga yang sedang login
            $user = $request->user();

            // 2. Hitung jumlah status iuran KHUSUS untuk warga ini
            $lunasCount = TransaksiKas::where('warga_id', $user->id)->where('status', 'sukses')->count();
            $ditolakCount = TransaksiKas::where('warga_id', $user->id)->where('status', 'gagal')->count();
            $menungguCount = TransaksiKas::where('warga_id', $user->id)->where('status', 'pending')->count();

            // 3. Ambil 5 riwayat transaksi terbaru untuk ditampilkan di list bawah
            $riwayatTerkini = TransaksiKas::with('tagihan')
                                ->where('warga_id', $user->id)
                                ->orderBy('created_at', 'desc')
                                ->take(5)
                                ->get();

            // 4. Susun balasan JSON
            return response()->json([
                'status' => 'success',
                'message' => 'Data Dashboard Warga berhasil diambil',
                'data' => [
                    'profil_warga' => [
                        'nama' => $user->nama_lengkap,
                        'blok' => $user->blok ?? '-'
                    ],
                    'ringkasan' => [
                        'lunas' => $lunasCount,
                        'ditolak' => $ditolakCount,
                        'menunggu' => $menungguCount
                    ],
                    'riwayat_terkini' => $riwayatTerkini
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}