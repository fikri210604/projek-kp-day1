<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Master Kendaraan</h2>
            <button onclick="openCreate()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">Tambah</button>
        </div>
    </x-slot>

    <style>
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); align-items:center; justify-content:center; z-index:50; }
        .modal { background:#fff; border-radius:.75rem; width:100%; max-width:32rem; box-shadow:0 10px 15px rgba(0,0,0,.2); }
        .dark .modal { background:#1f2937; color:#e5e7eb; }
        .show { display:flex !important; }
    </style>

    <div class="max-w-7xl mx-auto p-6">

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 text-green-800 px-4 py-3">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 text-red-800 px-4 py-3">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="GET" class="mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari plat/nama/unit" class="rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            <select name="jenis" class="rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Semua Jenis</option>
                <option value="mobil" @selected(request('jenis')==='mobil')>Mobil</option>
                <option value="motor" @selected(request('jenis')==='motor')>Motor</option>
            </select>
            <select name="aktif" class="rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Aktif/Nonaktif</option>
                <option value="1" @selected(request('aktif')==='1')>Aktif</option>
                <option value="0" @selected(request('aktif')==='0')>Nonaktif</option>
            </select>
            <div class="flex gap-2">
                <button class="px-4 py-2 rounded bg-gray-200">Filter</button>
                <a href="{{ route('admin.master-kendaraan.index') }}" class="px-4 py-2 rounded bg-gray-100 border">Reset</a>
            </div>
        </div>
    </form>

    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>

            <tr class="text-left text-gray-60 border-b border-gray-200 ">
                <th class="py-3 px-4">Plat</th>
                <th class="py-3 px-4">Jenis</th>
                <th class="py-3 px-4">Nama</th>
                <th class="py-3 px-4">Unit</th>
                <th class="py-3 px-4">Aktif</th>
                <th class="py-3 px-4">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            @forelse ($masters as $m)
                <tr data-id="{{ $m->id }}" data-plat="{{ $m->plat_nomor }}" data-jenis="{{ $m->jenis }}" data-nama="{{ $m->nama }}" data-unit="{{ $m->unit }}" data-aktif="{{ (int)$m->aktif }}">
                    <td class="py-3 px-4">{{ $m->plat_nomor }}</td>
                    <td class="py-3 px-4">{{ $m->jenis ?? '-' }}</td>
                    <td class="py-3 px-4">{{ $m->nama ?? '-' }}</td>
                    <td class="py-3 px-4">{{ $m->unit ?? '-' }}</td>
                    <td class="py-3 px-4">
                        @if($m->aktif)
                            <span class="px-2 py-1 text-xs rounded bg-emerald-100 text-emerald-700">Aktif</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-gray-200 text-gray-700">Nonaktif</span>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-2">
                            <button onclick="openEdit(this)" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded">Ubah</button>
                            <button onclick="openDelete(this)" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded">Hapus</button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-4 text-center text-gray-500">Belum ada data.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $masters->links() }}</div>
</div>

<!-- Modal Create -->
<div id="modal-create" class="modal-overlay">
    <div class="modal p-6">
        <h3 class="text-lg font-semibold mb-4">Tambah Kendaraan</h3>
        <form method="POST" action="{{ route('admin.master-kendaraan.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm">Plat Nomor</label>
                <input name="plat_nomor" type="text" required class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm">Jenis</label>
                <select name="jenis" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">- Pilih -</option>
                    <option value="mobil">Mobil</option>
                    <option value="motor">Motor</option>
                </select>
            </div>
            <div>
                <label class="block text-sm">Nama</label>
                <input name="nama" type="text" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm">Unit</label>
                <input name="unit" type="text" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="flex items-center gap-2">
                <input id="create-aktif" name="aktif" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" checked>
                <label for="create-aktif" class="text-sm">Aktif</label>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeCreate()" class="px-3 py-2 rounded bg-gray-200">Batal</button>
                <button type="submit" class="px-3 py-2 rounded bg-indigo-600 text-white">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modal-edit" class="modal-overlay">
    <div class="modal p-6">
        <h3 class="text-lg font-semibold mb-4">Ubah Kendaraan</h3>
        <form id="form-edit" method="POST" action="#" class="space-y-3">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm">Plat Nomor</label>
                <input id="edit-plat" name="plat_nomor" type="text" required class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm">Jenis</label>
                <select id="edit-jenis" name="jenis" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">- Pilih -</option>
                    <option value="mobil">Mobil</option>
                    <option value="motor">Motor</option>
                </select>
            </div>
            <div>
                <label class="block text-sm">Nama</label>
                <input id="edit-nama" name="nama" type="text" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm">Unit</label>
                <input id="edit-unit" name="unit" type="text" class="w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="flex items-center gap-2">
                <input id="edit-aktif" name="aktif" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <label for="edit-aktif" class="text-sm">Aktif</label>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeEdit()" class="px-3 py-2 rounded bg-gray-200">Batal</button>
                <button type="submit" class="px-3 py-2 rounded bg-indigo-600 text-white">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Delete -->
<div id="modal-delete" class="modal-overlay">
    <div class="modal p-6">
        <h3 class="text-lg font-semibold mb-4">Hapus Kendaraan</h3>
        <p class="mb-4">Yakin ingin menghapus data <span id="delete-plat" class="font-semibold"></span>?</p>
        <form id="form-delete" method="POST" action="#" class="flex justify-end gap-2">
            @csrf
            @method('DELETE')
            <button type="button" onclick="closeDelete()" class="px-3 py-2 rounded bg-gray-200">Batal</button>
            <button type="submit" class="px-3 py-2 rounded bg-red-600 text-white">Hapus</button>
        </form>
    </div>
</div>

<script>
function routeUpdate(id){ return `{{ url('/admin/master-kendaraan') }}/${id}`; }
function routeDelete(id){ return `{{ url('/admin/master-kendaraan') }}/${id}`; }

const modalCreate = document.getElementById('modal-create');
const modalEdit = document.getElementById('modal-edit');
const modalDelete = document.getElementById('modal-delete');

function openCreate(){ modalCreate.classList.add('show'); }
function closeCreate(){ modalCreate.classList.remove('show'); }

function openEdit(btn){
  const tr = btn.closest('tr');
  document.getElementById('form-edit').action = routeUpdate(tr.dataset.id);
  document.getElementById('edit-plat').value = tr.dataset.plat;
  document.getElementById('edit-jenis').value = tr.dataset.jenis || '';
  document.getElementById('edit-nama').value = tr.dataset.nama || '';
  document.getElementById('edit-unit').value = tr.dataset.unit || '';
  document.getElementById('edit-aktif').checked = tr.dataset.aktif === '1';
  modalEdit.classList.add('show');
}
function closeEdit(){ modalEdit.classList.remove('show'); }

function openDelete(btn){
  const tr = btn.closest('tr');
  document.getElementById('form-delete').action = routeDelete(tr.dataset.id);
  document.getElementById('delete-plat').innerText = tr.dataset.plat;
  modalDelete.classList.add('show');
}
function closeDelete(){ modalDelete.classList.remove('show'); }
</script>

</x-app-layout>
