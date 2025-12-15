<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Form Pendataan Kendaraan</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div class="min-h-screen bg-gray-100 py-8">
            <div class="max-w-xl mx-auto px-4">
                <div class="mb-6 text-right">
                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-3 py-1.5 text-sm rounded bg-gray-200 hover:bg-gray-300">Dashboard Admin</a>
                    @else
                        <a href="{{ route('admin.login') }}" class="inline-flex items-center px-3 py-1.5 text-sm rounded bg-indigo-600 text-white hover:bg-indigo-700">Login Admin</a>
                    @endauth
                </div>
                @if (session('success'))
                    <div class="mb-4 rounded-lg bg-green-50 text-green-800 px-4 py-3">
                        {{ session('success') }}
                    </div>
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

                <div class="bg-white rounded-xl shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Form Kendaraan Masuk (Keperluan)</h2>
                    <form method="POST" action="{{ route('guest.keperluan.keluar') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Peminjam</label>
                            <input type="text" name="nama" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Kendaraan (opsional)</label>
                            <select name="kendaraan_master_id" id="kendaraan_master_id" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Pilih dari Daftar --</option>
                                @foreach(($masters ?? []) as $km)
                                    <option value="{{ $km->id }}">{{ $km->plat_nomor }} {{ $km->nama ? '— '.$km->nama : '' }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Jika tidak ada di daftar, isi plat manual di bawah.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Plat Nomor (manual, opsional)</label>
                            <input type="text" name="plat_nomor" id="plat_nomor" placeholder="Contoh: BE 1234 XX" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keperluan</label>
                            <input type="text" name="isi_keperluan" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tujuan</label>
                            <input type="text" name="tujuan_keperluan" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-lg transition">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            const selectMaster = document.getElementById('kendaraan_master_id');
            const inputPlat = document.getElementById('plat_nomor');
            if (selectMaster && inputPlat) {
                const toggleManual = () => {
                    const usingMaster = !!selectMaster.value;
                    inputPlat.disabled = usingMaster;
                    inputPlat.classList.toggle('opacity-50', usingMaster);
                };
                selectMaster.addEventListener('change', toggleManual);
                toggleManual();
            }
        </script>
    </body>
</html>
