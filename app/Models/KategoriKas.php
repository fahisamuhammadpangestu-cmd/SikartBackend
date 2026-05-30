<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class KategoriKas extends Model
{
    protected $table = 'kategori_kas';
    protected $fillable = ['nama_kategori', 'jenis'];
}