<?php

namespace App\Http\Controllers;

use App\Models\Kendaraan;
use App\Models\KendaraanMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class KendaraanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kendaraans = Kendaraan::latest()->paginate(10);
        if (request()->wantsJson()) {
            return response()->json($kendaraans);
        }
        return view('kendaraan.index', compact('kendaraans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'plat_nomor' => ['required','string','max:20'],
            'jenis' => ['nullable','in:mobil,motor'],
            'catatan' => ['nullable','string'],
        ]);

        $existing = Kendaraan::where('plat_nomor', $data['plat_nomor'])
            ->whereNull('waktu_keluar')
            ->first();
        if ($existing) {
            return back()->withErrors(['plat_nomor' => 'Kendaraan ini masih berstatus masuk.']);
        }

        $master = KendaraanMaster::where('plat_nomor', strtoupper($data['plat_nomor']))->first();

        $kendaraan = Kendaraan::create([
            'kendaraan_master_id' => $master?->id,
            'plat_nomor' => strtoupper($data['plat_nomor']),
            'jenis' => $data['jenis'] ?? null,
            'waktu_masuk' => now(),
            'status' => 'in',
            'catatan' => $data['catatan'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json($kendaraan, 201);
        }
        return redirect()->back()->with('success', 'Kendaraan masuk dicatat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Kendaraan $kendaraan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kendaraan $kendaraan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kendaraan $kendaraan)
    {
        $data = $request->validate([
            'plat_nomor' => ['sometimes','required','string','max:20'],
            'jenis' => ['nullable','in:mobil,motor'],
            'catatan' => ['nullable','string'],
        ]);
        $kendaraan->update($data);

        if ($request->wantsJson()) {
            return response()->json($kendaraan);
        }
        return redirect()->back()->with('success', 'Data kendaraan diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kendaraan $kendaraan)
    {
        $kendaraan->delete();
        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }
        return redirect()->back()->with('success', 'Data kendaraan dihapus.');
    }

    /**
     * Tandai kendaraan keluar dan hitung biaya.
     */
    public function keluar(Request $request, Kendaraan $kendaraan)
    {
        if ($kendaraan->waktu_keluar) {
            return back()->withErrors(['kendaraan' => 'Kendaraan sudah keluar.']);
        }

        $waktuKeluar = now();
        $durasiMenit = (int) $kendaraan->waktu_masuk?->diffInMinutes($waktuKeluar) ?? 0;
        $biaya = $this->hitungBiaya($kendaraan->jenis, $durasiMenit);

        $kendaraan->update([
            'waktu_keluar' => $waktuKeluar,
            'durasi_menit' => $durasiMenit,
            'biaya' => $biaya,
            'status' => 'out',
        ]);

        if ($request->wantsJson()) {
            return response()->json($kendaraan);
        }
        return redirect()->back()->with('success', 'Kendaraan keluar dicatat.');
    }

    /**
     * Guest: keluar berdasarkan plat nomor (tanpa ID)
     */
    public function keluarByPlat(Request $request)
    {
        $data = $request->validate([
            'plat_nomor' => ['required','string','max:20'],
        ]);
        $plat = strtoupper($data['plat_nomor']);
        $kendaraan = Kendaraan::where('plat_nomor', $plat)
            ->whereNull('waktu_keluar')
            ->orderByDesc('waktu_masuk')
            ->first();

        if (!$kendaraan) {
            return back()->withErrors(['plat_nomor' => 'Data kendaraan berstatus masuk tidak ditemukan.']);
        }

        $waktuKeluar = now();
        $durasiMenit = (int) $kendaraan->waktu_masuk?->diffInMinutes($waktuKeluar) ?? 0;
        $biaya = $this->hitungBiaya($kendaraan->jenis, $durasiMenit);

        $kendaraan->update([
            'waktu_keluar' => $waktuKeluar,
            'durasi_menit' => $durasiMenit,
            'biaya' => $biaya,
            'status' => 'out',
        ]);

        return $request->wantsJson() ? response()->json($kendaraan) : back()->with('success', 'Kendaraan keluar dicatat.');
    }

    /**
     * Export CSV sederhana; bisa dibuka di Excel.
     */
    public function export(Request $request)
    {
        $query = Kendaraan::query();
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('waktu_masuk', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('waktu_masuk', '<=', $request->date('date_to'));
        }

        $filename = 'kendaraan_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $columns = ['ID','Plat','Jenis','Masuk','Keluar','DurasiMenit','Biaya','Status','Catatan'];

        $callback = function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            $query->orderByDesc('id')->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $r) {
                    fputcsv($handle, [
                        $r->id,
                        $r->plat_nomor,
                        $r->jenis,
                        optional($r->waktu_masuk)->format('Y-m-d H:i:s'),
                        optional($r->waktu_keluar)->format('Y-m-d H:i:s'),
                        $r->durasi_menit,
                        $r->biaya,
                        $r->status,
                        $r->catatan,
                    ]);
                }
            });
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function hitungBiaya(?string $jenis, int $durasiMenit): int
    {
        // Sederhana: pembulatan ke atas per jam
        $jam = max(1, (int) ceil($durasiMenit / 60));
        if ($jenis === 'mobil') {
            return 5000 + max(0, $jam - 1) * 2000;
        }
        // default motor
        return 2000 + max(0, $jam - 1) * 1000;
    }
}
