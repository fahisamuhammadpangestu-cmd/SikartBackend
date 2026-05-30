<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    use HasFactory;

    // Memberi tahu Laravel nama tabelnya
    protected $table = 'tagihan';

    // Memberi tahu kolom apa saja yang boleh diisi
    protected $fillable = [
        'nama_tagihan',
        'nominal',
        'keterangan',
        'status_sistem'
    ];
}