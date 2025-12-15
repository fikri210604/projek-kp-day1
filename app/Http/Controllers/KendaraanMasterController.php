<?php

namespace App\Http\Controllers;

use App\Models\KendaraanMaster;
use Illuminate\Http\Request;

class KendaraanMasterController extends Controller
{
    public function index()
    {
        $query = KendaraanMaster::query();

        $q = request('q');
        $jenis = request('jenis');
        $aktif = request('aktif');

        if ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('plat_nomor', 'like', "%{$q}%")
                  ->orWhere('nama', 'like', "%{$q}%")
                  ->orWhere('unit', 'like', "%{$q}%");
            });
        }
        if (in_array($jenis, ['mobil','motor'], true)) {
            $query->where('jenis', $jenis);
        }
        if ($aktif !== null && $aktif !== '') {
            $query->where('aktif', (bool)((int) $aktif));
        }

        $masters = $query->orderBy('plat_nomor')->paginate(15)->withQueryString();
        if (request()->wantsJson()) {
            return response()->json($masters);
        }
        return view('admin.kendaraan.index', compact('masters'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'plat_nomor' => ['required','string','max:20','unique:kendaraan_masters,plat_nomor'],
            'jenis' => ['nullable','in:mobil,motor'],
            'nama' => ['nullable','string','max:100'],
            'unit' => ['nullable','string','max:100'],
            'aktif' => ['nullable','boolean'],
        ]);
        $data['plat_nomor'] = strtoupper($data['plat_nomor']);
        $master = KendaraanMaster::create($data);
        return $request->wantsJson() ? response()->json($master, 201) : back()->with('success', 'Kendaraan ditambahkan');
    }

    public function update(Request $request, KendaraanMaster $kendaraanMaster)
    {
        $data = $request->validate([
            'plat_nomor' => ['sometimes','required','string','max:20','unique:kendaraan_masters,plat_nomor,'.$kendaraanMaster->id],
            'jenis' => ['nullable','in:mobil,motor'],
            'nama' => ['nullable','string','max:100'],
            'unit' => ['nullable','string','max:100'],
            'aktif' => ['nullable','boolean'],
        ]);
        if (isset($data['plat_nomor'])) {
            $data['plat_nomor'] = strtoupper($data['plat_nomor']);
        }
        $kendaraanMaster->update($data);
        return $request->wantsJson() ? response()->json($kendaraanMaster) : back()->with('success', 'Kendaraan diperbarui');
    }

    public function destroy(KendaraanMaster $kendaraanMaster)
    {
        $kendaraanMaster->delete();
        return request()->wantsJson() ? response()->json(null, 204) : back()->with('success', 'Kendaraan dihapus');
    }
}
