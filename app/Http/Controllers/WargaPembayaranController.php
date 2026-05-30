<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiKas;
use App\Models\KategoriKas;
use Illuminate\Support\Facades\Validator;

class WargaPembayaranController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi input dari form (termasuk file gambar)
        $validator = Validator::make($request->all(), [
            'tagihan_id' => 'required|exists:tagihan,id', // Harus tahu bayar tagihan yang mana
            'tanggal_pembayaran' => 'required|date',
            'nominal' => 'required|numeric|min:1',
            'bukti_transfer' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Wajib gambar, maks 2MB
            'keterangan' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 400);
        }

        // 2. Proses upload file gambar
        $pathGambar = null;
        if ($request->hasFile('bukti_transfer')) {
            // Gambar disimpan di folder storage/app/public/bukti_transfer
            $pathGambar = $request->file('bukti_transfer')->store('bukti_transfer', 'public');
        }

        // 3. Cari ID Kategori untuk 'Iuran Bulanan' (Agar pembukuan kas rapi)
        $kategori = KategoriKas::where('nama_kategori', 'Iuran Bulanan')->first();
        $kategoriId = $kategori ? $kategori->id : null;

        // 4. Simpan data ke database
        $transaksi = TransaksiKas::create([
            'warga_id' => $request->user()->id, // Diambil dari token warga yang login
            'tagihan_id' => $request->tagihan_id,
            'jenis' => 'pemasukan',
            'kategori_id' => $kategoriId,
            'nominal' => $request->nominal,
            'tanggal_transaksi' => $request->tanggal_pembayaran,
            'keterangan' => $request->keterangan,
            'metode_pembayaran' => 'qris_manual',
            'bukti_transfer' => $pathGambar, // Simpan nama/path filenya
            'status' => 'pending' // Status mengantre untuk diverifikasi admin!
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Bukti pembayaran berhasil dikirim. Silakan tunggu verifikasi Admin.',
            'data' => $transaksi
        ], 201);
    }
}