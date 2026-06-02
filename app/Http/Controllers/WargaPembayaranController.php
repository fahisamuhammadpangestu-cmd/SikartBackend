<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiKas;
use App\Models\KategoriKas;
use Illuminate\Support\Facades\Validator;
use Midtrans\Config;
use Midtrans\Snap;

class WargaPembayaranController extends Controller
{
    public function store(Request $request)
    {
        // =======================================================
        // HARDCODE KUNCI ASLI DARI GAMBAR (BYPASS CACHE LARAVEL)
        // =======================================================
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $validator = Validator::make($request->all(), [
            'tagihan_id' => 'required',
            'tanggal_transaksi' => 'required|date',
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 400);
        }

        try {
            $kategori = KategoriKas::where('nama_kategori', 'Iuran Bulanan')->first();
            
            $transaksi = TransaksiKas::create([
                'warga_id' => $request->user()->id, 

                'tagihan_id' => $request->tagihan_id === 'lainnya' ? null : $request->tagihan_id, 
                
                'jenis' => 'pemasukan',
                'kategori_id' => $kategori ? $kategori->id : null,
                'nominal' => $request->nominal,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'keterangan' => $request->keterangan,
                'metode_pembayaran' => 'midtrans',
                'bukti_transfer' => null,
                'status' => 'pending'
            ]);

            $orderId = 'RT03-' . $transaksi->id . '-' . time();

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $request->nominal,
                ],
                'customer_details' => [
                    'first_name' => $request->user()->nama_lengkap ?? 'Warga RT 03', 
                    'email' => $request->user()->email ?? 'warga@rt03.com',
                ],
            ];

            // Meminta Token
            $snapToken = Snap::getSnapToken($params);

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil dibuat!',
                'snap_token' => $snapToken,
                'data' => $transaksi
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }
}