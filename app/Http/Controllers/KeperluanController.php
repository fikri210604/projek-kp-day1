<?php

namespace App\Http\Controllers;

use App\Models\Keperluan;
use App\Models\KendaraanMaster;
use Illuminate\Http\Request;

class KeperluanController extends Controller
{
    public function index()
    {
        $query = Keperluan::with('kendaraan');

        $q = request('q');
        $status = request('status'); // aktif|selesai
        $dateFrom = request('date_from');
        $dateTo = request('date_to');

        if ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('nama', 'like', "%{$q}%")
                  ->orWhere('isi_keperluan', 'like', "%{$q}%")
                  ->orWhere('tujuan_keperluan', 'like', "%{$q}%")
                  ->orWhere('plat_nomor', 'like', "%{$q}%")
                  ->orWhereHas('kendaraan', function ($qq) use ($q) {
                      $qq->where('plat_nomor', 'like', "%{$q}%")
                         ->orWhere('nama', 'like', "%{$q}%")
                         ->orWhere('unit', 'like', "%{$q}%");
                  });
            });
        }
        if ($status === 'aktif') {
            $query->whereNull('waktu_kembali');
        } elseif ($status === 'selesai') {
            $query->whereNotNull('waktu_kembali');
        }
        if ($dateFrom) {
            $query->whereDate('waktu_pinjam', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('waktu_pinjam', '<=', $dateTo);
        }

        $keperluans = $query->orderByDesc('id')->paginate(15)->withQueryString();
        if (request()->wantsJson()) {
            return response()->json($keperluans);
        }
        return view('admin.keperluan.index', compact('keperluans'));
    }

    // Guest/Admin: catat keluar (pinjam)
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required','string','max:100'],
            'isi_keperluan' => ['required','string','max:255'],
            'tujuan_keperluan' => ['required','string','max:255'],
            'kendaraan_master_id' => ['nullable','exists:kendaraan_masters,id'],
            'plat_nomor' => ['nullable','string','max:20'],
            'waktu_pinjam' => ['nullable','date'],
        ]);

        if (!empty($data['plat_nomor'])) {
            $data['plat_nomor'] = strtoupper($data['plat_nomor']);
        }

        if (empty($data['kendaraan_master_id']) && !empty($data['plat_nomor'])) {
            $master = KendaraanMaster::where('plat_nomor', $data['plat_nomor'])->first();
            if ($master) {
                $data['kendaraan_master_id'] = $master->id;
            }
        }

        $data['waktu_pinjam'] = $data['waktu_pinjam'] ?? now();

        $keperluan = Keperluan::create($data);

        return $request->wantsJson() ? response()->json($keperluan, 201) : back()->with('success', 'Keperluan (keluar) dicatat.');
    }

    // Tandai kembali (masuk) berdasarkan keperluan id
    public function kembali(Request $request, Keperluan $keperluan)
    {
        if ($keperluan->waktu_kembali) {
            return back()->withErrors(['keperluan' => 'Sudah ditandai kembali.']);
        }
        $keperluan->update(['waktu_kembali' => now()]);
        return $request->wantsJson() ? response()->json($keperluan) : back()->with('success', 'Kendaraan kembali (masuk) dicatat.');
    }

    // Guest: kembali by plat, ambil transaksi keperluan terakhir yang belum kembali
    public function kembaliByPlat(Request $request)
    {
        $data = $request->validate([
            'plat_nomor' => ['required','string','max:20'],
        ]);
        $plat = strtoupper($data['plat_nomor']);

        $keperluan = Keperluan::where(function ($q) use ($plat) {
                $q->where('plat_nomor', $plat)
                  ->orWhereHas('kendaraan', function ($qq) use ($plat) {
                      $qq->where('plat_nomor', $plat);
                  });
            })
            ->whereNull('waktu_kembali')
            ->orderByDesc('waktu_pinjam')
            ->first();

        if (!$keperluan) {
            return back()->withErrors(['plat_nomor' => 'Tidak ada keperluan aktif untuk plat tersebut.']);
        }

        $keperluan->update(['waktu_kembali' => now()]);

        return $request->wantsJson() ? response()->json($keperluan) : back()->with('success', 'Kendaraan kembali (masuk) dicatat.');
    }

    public function update(Request $request, Keperluan $keperluan)
    {
        $data = $request->validate([
            'nama' => ['sometimes','required','string','max:100'],
            'isi_keperluan' => ['sometimes','required','string','max:255'],
            'tujuan_keperluan' => ['sometimes','required','string','max:255'],
            'kendaraan_master_id' => ['nullable','exists:kendaraan_masters,id'],
            'plat_nomor' => ['nullable','string','max:20'],
            'waktu_pinjam' => ['nullable','date'],
            'waktu_kembali' => ['nullable','date'],
        ]);
        if (isset($data['plat_nomor'])) {
            $data['plat_nomor'] = strtoupper($data['plat_nomor']);
        }
        $keperluan->update($data);
        return $request->wantsJson() ? response()->json($keperluan) : back()->with('success', 'Data keperluan diperbarui.');
    }

    public function destroy(Keperluan $keperluan)
    {
        $keperluan->delete();
        return request()->wantsJson() ? response()->json(null, 204) : back()->with('success', 'Data keperluan dihapus.');
    }

    // Export CSV sederhana (bisa dibuka Excel)
    public function export(Request $request)
    {
        $query = Keperluan::with('kendaraan');
        if ($request->filled('date_from')) {
            $query->whereDate('waktu_pinjam', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('waktu_pinjam', '<=', $request->date('date_to'));
        }

        $filename = 'keperluan_'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        $columns = ['ID','Plat','Nama','Keperluan','Tujuan','Pinjam','Kembali','DurasiMenit'];

        $callback = function () use ($query, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);
            $query->orderByDesc('id')->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $r) {
                    $plat = $r->plat_nomor ?? optional($r->kendaraan)->plat_nomor;
                    $durasi = $r->waktu_kembali && $r->waktu_pinjam ? $r->waktu_pinjam->diffInMinutes($r->waktu_kembali) : null;
                    fputcsv($handle, [
                        $r->id,
                        $plat,
                        $r->nama,
                        $r->isi_keperluan,
                        $r->tujuan_keperluan,
                        optional($r->waktu_pinjam)->format('Y-m-d H:i:s'),
                        optional($r->waktu_kembali)->format('Y-m-d H:i:s'),
                        $durasi,
                    ]);
                }
            });
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
