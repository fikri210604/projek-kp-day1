<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Keperluan Kendaraan</h2>
            <a href="{{ route('admin.keperluan.export') }}" class="px-4 py-2 bg-gray-200 rounded">Export CSV</a>
        </div>
    </x-slot>

    <div class="space-y-4">
        <form method="GET" class="bg-white rounded-xl shadow p-4">
            <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama/plat/keperluan/tujuan" class="rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                <select name="status" class="rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="aktif" @selected(request('status')==='aktif')>Aktif (belum kembali)</option>
                    <option value="selesai" @selected(request('status')==='selesai')>Selesai (sudah kembali)</option>
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Dari">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Sampai">
                <div class="flex gap-2">
                    <button class="px-4 py-2 rounded bg-gray-200">Filter</button>
                    <a href="{{ route('admin.keperluan.index') }}" class="px-4 py-2 rounded bg-gray-100 border">Reset</a>
                </div>
            </div>
        </form>

        <div class="bg-white rounded-xl shadow overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-600 border-b border-gray-200">
                        <th class="py-3 px-4">ID</th>
                        <th class="py-3 px-4">Plat</th>
                        <th class="py-3 px-4">Nama</th>
                        <th class="py-3 px-4">Keperluan</th>
                        <th class="py-3 px-4">Tujuan</th>
                        <th class="py-3 px-4">Pinjam</th>
                        <th class="py-3 px-4">Kembali</th>
                        <th class="py-3 px-4">Durasi</th>
                        <th class="py-3 px-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-20">
                    @forelse ($keperluans as $k)
                        <tr data-id="{{ $k->id }}">
                            <td class="py-3 px-4">{{ $k->id }}</td>
                            <td class="py-3 px-4">{{ $k->plat_nomor ?? optional($k->kendaraan)->plat_nomor }}</td>
                            <td class="py-3 px-4">{{ $k->nama }}</td>
                            <td class="py-3 px-4">{{ $k->isi_keperluan }}</td>
                            <td class="py-3 px-4">{{ $k->tujuan_keperluan }}</td>
                            <td class="py-3 px-4">{{ optional($k->waktu_pinjam)->format('Y-m-d H:i') }}</td>
                            <td class="py-3 px-4">{{ optional($k->waktu_kembali)->format('Y-m-d H:i') ?: '-' }}</td>
                            <td class="py-3 px-4">
                                @php($dur = ($k->waktu_kembali && $k->waktu_pinjam) ? $k->waktu_pinjam->diffInMinutes($k->waktu_kembali) : null)
                                {{ $dur ? $dur.' mnt' : '-' }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2">
                                    @if (!$k->waktu_kembali)
                                        <button onclick="openMasuk({{ $k->id }})" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded">Tandai Masuk</button>
                                    @endif
                                    <button onclick="openDelete({{ $k->id }})" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-4 text-center text-gray-500">Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $keperluans->links() }}
        </div>
    </div>

    <!-- Modal Masuk -->
    <div id="modal-masuk" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Konfirmasi Masuk</h3>
            <p class="mb-4">Tandai keperluan ini sudah kembali/masuk?</p>
            <form id="form-masuk" method="POST" action="#" class="flex justify-end gap-2">
                @csrf
                <button type="button" onclick="closeMasuk()" class="px-3 py-2 rounded bg-gray-200">Batal</button>
                <button type="submit" class="px-3 py-2 rounded bg-emerald-600 text-white">Tandai Masuk</button>
            </form>
        </div>
    </div>

    <!-- Modal Delete -->
    <div id="modal-delete" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Hapus Data</h3>
            <p class="mb-4">Yakin ingin menghapus data ini?</p>
            <form id="form-delete" method="POST" action="#" class="flex justify-end gap-2">
                @csrf
                @method('DELETE')
                <button type="button" onclick="closeDelete()" class="px-3 py-2 rounded bg-gray-200">Batal</button>
                <button type="submit" class="px-3 py-2 rounded bg-red-600 text-white">Hapus</button>
            </form>
        </div>
    </div>

    <script>
        function openMasuk(id){
            const m = document.getElementById('modal-masuk');
            const f = document.getElementById('form-masuk');
            f.action = `{{ url('/admin/keperluan') }}/${id}/masuk`;
            m.classList.remove('hidden');
            m.classList.add('flex');
        }
        function closeMasuk(){
            const m = document.getElementById('modal-masuk');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }

        function openDelete(id){
            const m = document.getElementById('modal-delete');
            const f = document.getElementById('form-delete');
            f.action = `{{ url('/admin/keperluan') }}/${id}`;
            m.classList.remove('hidden');
            m.classList.add('flex');
        }
        function closeDelete(){
            const m = document.getElementById('modal-delete');
            m.classList.add('hidden');
            m.classList.remove('flex');
        }
    </script>
</x-app-layout>

