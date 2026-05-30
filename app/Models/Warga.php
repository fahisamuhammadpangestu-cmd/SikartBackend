<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Warga extends Authenticatable
{
    use HasApiTokens, HasFactory;

    // Memberi tahu Laravel nama tabel kita di database
    protected $table = 'warga';

    // Memberi tahu Laravel kolom apa saja yang boleh diisi
    protected $fillable = [
        'nama_lengkap',
        'email',
        'no_hp',
        'username',
        'password',
        'role',
        'blok'
    ];

    // Menyembunyikan password agar tidak bocor saat data dipanggil
    protected $hidden = [
        'password',
    ];
}