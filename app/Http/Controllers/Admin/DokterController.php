<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Dokter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DokterController extends Controller
{
    // Menampilkan daftar dokter
    public function index(Request $request)
    {
        $query = Dokter::query();

        // Pencarian
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_dokter', 'like', '%' . $search . '%')
                  ->orWhere('alamat', 'like', '%' . $search . '%')
                  ->orWhere('no_telepon', 'like', '%' . $search . '%');
            });
        }

        $dokters = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.dokter.index', compact('dokters'));
    }

    // Tampilkan form tambah dokter
    public function create()
    {
        return view('admin.dokter.create');
    }

    // Simpan dokter baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_dokter' => 'required|string|max:255',
            'alamat' => 'required|string',
            'no_telepon' => 'nullable|string|max:20',
            'id_layanan' => 'nullable|exists:layanan,id',
            'foto_dokter' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $fotoPath = null;
            if ($request->hasFile('foto_dokter')) {
                $fotoPath = $this->uploadFoto($request->file('foto_dokter'));
            }

            Dokter::create([
                'nama_dokter' => $request->nama_dokter,
                'alamat' => $request->alamat,
                'no_telepon' => $request->no_telepon,
                'id_layanan' => $request->id_layanan,
                'foto_dokter' => $fotoPath,
            ]);

            return redirect()->route('admin.dokter.index')
                ->with('success', 'Data dokter berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Error creating dokter: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan')->withInput();
        }
    }

    // Tampilkan form edit dokter
    public function edit($id)
    {
        $dokter = Dokter::findOrFail($id);
        return view('admin.dokter.edit', compact('dokter'));
    }

    // Update data dokter
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_dokter' => 'required|string|max:255',
            'alamat' => 'required|string',
            'no_telepon' => 'nullable|string|max:20',
            'id_layanan' => 'nullable|exists:layanan,id',
            'foto_dokter' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $dokter = Dokter::findOrFail($id);

            if ($request->hasFile('foto_dokter')) {
                $this->deleteFoto($dokter->foto_dokter);
                $dokter->foto_dokter = $this->uploadFoto($request->file('foto_dokter'));
            }

            $dokter->nama_dokter = $request->nama_dokter;
            $dokter->alamat = $request->alamat;
            $dokter->no_telepon = $request->no_telepon;
            $dokter->id_layanan = $request->id_layanan;
            $dokter->save();

            return redirect()->route('admin.dokter.index')
                ->with('success', 'Data dokter berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error updating dokter: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan')->withInput();
        }
    }

    // Hapus dokter
    public function destroy($id)
    {
        try {
            $dokter = Dokter::findOrFail($id);
            $this->deleteFoto($dokter->foto_dokter);
            $dokter->delete();

            return redirect()->route('admin.dokter.index')
                ->with('success', 'Data dokter berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting dokter: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data');
        }
    }

    // Upload foto helper
    private function uploadFoto($file)
    {
        $extension = $file->getClientOriginalExtension();
        $filename = 'dokter_' . time() . '_' . Str::random(10) . '.' . $extension;
        $path = $file->storeAs('dokter_photos', $filename, 'public');
        return $path;
    }

    // Delete foto helper
    private function deleteFoto($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
