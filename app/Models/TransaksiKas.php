<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiKas extends Model
{
    use HasFactory;

    // Memberi tahu nama tabelnya
    protected $table = 'transaksi_kas';

    // Kolom apa saja yang boleh diisi
    protected $fillable = [

        'warga_id',
        'tagihan_id',
        'jenis',
        'kategori_id',
        'nominal',
        'tanggal_transaksi',
        'keterangan',
        'metode_pembayaran',
        'bukti_transfer',
        'status',
        'order_id_midtrans'
    ];

     // Relasi ke tabel kategori_kas
        public function kategori()
        {
            return $this->belongsTo(KategoriKas::class, 'kategori_id');
        }

    // Relasi ke tabel warga
    public function warga()
    {
        return $this->belongsTo(Warga::class, 'warga_id');
    }

    // Relasi ke tabel tagihan
    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class, 'tagihan_id');
    }
}