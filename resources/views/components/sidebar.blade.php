<aside id="sidebar" class="w-64 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 sticky top-0 h-screen overflow-y-auto shrink-0 transition-all duration-200 ease-in-out">
    <div class="p-4 flex items-center justify-between border-b border-gray-200 dark:border-gray-800">
        <span class="font-semibold text-gray-800 dark:text-gray-100">Menu</span>
        <button id="sidebarToggle" class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800" aria-label="Toggle sidebar width">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-600 dark:text-gray-300">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>
    </div>

    <nav class="p-3">
        @php
            $links = [
                ['href' => route('admin.dashboard'), 'text' => 'Dashboard', 'active' => request()->routeIs('admin.dashboard')],
                ['href' => route('admin.master-kendaraan.index'), 'text' => 'Master Kendaraan', 'active' => request()->routeIs('admin.master-kendaraan.*')],
                ['href' => route('admin.keperluan.index'), 'text' => 'Keperluan', 'active' => request()->routeIs('admin.keperluan.*')],
                ['href' => route('admin.profile'), 'text' => 'Profil', 'active' => request()->routeIs('admin.profile')],
            ];
        @endphp
        <ul class="space-y-1">
            @foreach ($links as $l)
                <li>
                    <a href="{{ $l['href'] }}" class="block px-3 py-2 rounded {{ $l['active'] ? 'bg-indigo-50 text-indigo-700 dark:bg-gray-800 dark:text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}" wire:navigate>
                        {{ $l['text'] }}
                    </a>
                </li>
            @endforeach
            <li class="pt-2 border-t border-gray-200 dark:border-gray-800 mt-2">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="w-full text-left px-3 py-2 rounded text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">Log out</button>
                </form>
            </li>
        </ul>
    </nav>
</aside>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.getElementById('sidebar');
  const toggle = document.getElementById('sidebarToggle');
  if (!sidebar || !toggle) return;
  toggle.addEventListener('click', () => {
    sidebar.classList.toggle('w-64');
    sidebar.classList.toggle('w-20');
  });
});
</script>
