<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tagihan;
use App\Models\TransaksiKas;

class WargaDashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil data warga yang sedang login saat ini
        $user = $request->user();

        // 2. Ambil semua daftar Tagihan yang statusnya 'aktif'
        $semuaTagihan = Tagihan::where('status_sistem', 'aktif')
                               ->orderBy('created_at', 'desc')
                               ->get();

        // Siapkan variabel untuk menghitung ringkasan
        $lunasCount = 0;
        $menungguCount = 0;
        $tunggakanCount = 0;
        
        // Siapkan array kosong untuk menampung daftar status iuran
        $statusIuranTerkini = [];

        // 3. Lakukan perulangan (looping) untuk mencocokkan setiap tagihan dengan transaksi warga ini
        foreach ($semuaTagihan as $tagihan) {
            
            // Cek apakah warga ini punya riwayat transaksi untuk tagihan ini
            $transaksi = TransaksiKas::where('warga_id', $user->id)
                                     ->where('tagihan_id', $tagihan->id)
                                     ->latest() // Ambil yang paling baru jika warga mencoba bayar 2 kali
                                     ->first();

            $statusPembayaran = 'Belum Bayar'; // Status default
            $tanggalBayar = null;

            if ($transaksi) {
                $tanggalBayar = $transaksi->tanggal_transaksi;
                
                if ($transaksi->status === 'sukses') {
                    $statusPembayaran = 'Lunas';
                    $lunasCount++;
                } elseif ($transaksi->status === 'pending') {
                    $statusPembayaran = 'Menunggu Verifikasi';
                    $menungguCount++;
                }
            } else {
                // Jika tidak ada transaksi sama sekali, berarti menunggak
                $tunggakanCount++;
            }

            // Masukkan hasil pengecekan ke dalam daftar
            $statusIuranTerkini[] = [
                'id_tagihan' => $tagihan->id,
                'nama_tagihan' => $tagihan->nama_tagihan,
                'nominal' => $tagihan->nominal,
                'status' => $statusPembayaran,
                'tanggal_bayar' => $tanggalBayar
            ];
        }

        // 4. Susun jawaban untuk dikirim ke Frontend
        return response()->json([
            'status' => 'success',
            'message' => 'Data Dashboard Warga berhasil diambil',
            'data' => [
                'profil_warga' => [
                    'nama' => $user->nama_lengkap,
                    'blok' => $user->blok
                ],
                'ringkasan' => [
                    'lunas' => $lunasCount,
                    'tunggakan' => $tunggakanCount,
                    'menunggu' => $menungguCount
                ],
                'status_iuran_terkini' => $statusIuranTerkini
            ]
        ], 200);
    }
}