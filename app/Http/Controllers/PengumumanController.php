<?php
namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    // Mengambil semua pengumuman (terbaru di atas)
    public function index()
    {
        $pengumuman = Pengumuman::orderBy('created_at', 'desc')->get();
        return response()->json(['status' => 'success', 'data' => $pengumuman]);
    }

    // Menyimpan pengumuman baru
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string'
        ]);

        $pengumuman = Pengumuman::create($request->all());
        return response()->json(['status' => 'success', 'message' => 'Pengumuman berhasil dibuat!', 'data' => $pengumuman]);
    }

    // Menghapus pengumuman
    public function destroy($id)
    {
        $pengumuman = Pengumuman::find($id);
        if ($pengumuman) {
            $pengumuman->delete();
            return response()->json(['status' => 'success', 'message' => 'Pengumuman dihapus!']);
        }
        return response()->json(['status' => 'error', 'message' => 'Pengumuman tidak ditemukan.'], 404);
    }
}