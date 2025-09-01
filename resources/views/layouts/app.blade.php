<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ $title ?? 'Ticketing' }}</title>
  <link rel="stylesheet" href="{{ mix('css/app.css') }}">
  <link rel="manifest" href="/manifest.webmanifest">
  <meta name="theme-color" content="#0ea5e9">
  <link rel="apple-touch-icon" href="/icons/icon-192.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
</head>
<body class="bg-gray-100">

<div class="max-w-7xl mx-auto p-4 pb-20 md:pb-4">

    {{-- TOP BAR / NAV (sticky + blur) --}}
    <header class="mb-4 sticky top-0 z-40">
      <div class="bg-white/90 backdrop-blur rounded-xl shadow px-4 py-3">
        <div class="flex items-center justify-between gap-2">
          <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route('home') }}" class="text-lg font-semibold truncate">Ticketing App</a>
            @auth
              <span class="hidden md:inline text-gray-300">|</span>
              <div class="hidden md:flex items-center gap-2 text-sm text-gray-600">
                <span class="truncate">Halo, {{ auth()->user()->name }}</span>
                @php $role = auth()->user()->role ?? 'USER'; @endphp
                <span class="chip">{{ $role }}</span>
              </div>
            @endauth
          </div>

          {{-- Desktop nav --}}
          @auth
          @php
            $is = fn($pat) => request()->routeIs($pat);
          @endphp
<nav class="hidden md:flex items-center gap-2 text-sm">
  <a href="{{ route('dashboard') }}"
     class="btn btn-outline {{ request()->routeIs('dashboard') ? 'bg-black text-white border-black' : '' }}">
    Dashboard
  </a>

  <a href="{{ route('tickets.index') }}"
     class="btn btn-outline {{ request()->routeIs('tickets.*') ? 'bg-black text-white border-black' : '' }}">
    Tiket
  </a>

  <a href="{{ route('tickets.create') }}" class="btn btn-brand">Buat Tiket</a>

  {{-- Aset & Laporan: hanya PJ & SUPERADMIN --}}
  @if(in_array((auth()->user()->role ?? null), ['PJ','SUPERADMIN'], true))
    <a href="{{ route('assets.index') }}"
       class="btn btn-outline {{ request()->routeIs('assets.*') ? 'bg-black text-white border-black' : '' }}">
      Aset
    </a>
    <a href="{{ route('reports.tickets') }}"
       class="btn btn-outline {{ request()->routeIs('reports.tickets') ? 'bg-black text-white border-black' : '' }}">
      Laporan
    </a>
  @endif

  {{-- Master dropdown (SUPERADMIN) --}}
  @if((auth()->user()->role ?? null) === 'SUPERADMIN')
    <details class="relative">
      <summary class="btn btn-outline {{ (request()->routeIs('master.*')||request()->routeIs('settings.sla.*')||request()->routeIs('admin.users.*')) ? 'bg-black text-white border-black' : '' }}">
        Admin
      </summary>
      <div class="absolute right-0 mt-2 w-56 bg-white border rounded-xl shadow z-10 py-1">
        <a href="{{ route('master.asset_categories.index') }}" class="block px-3 py-2 hover:bg-gray-50">Kategori Aset</a>
        <a href="{{ route('master.locations.index') }}" class="block px-3 py-2 hover:bg-gray-50">Lokasi</a>
        <a href="{{ route('master.vendors.index') }}" class="block px-3 py-2 hover:bg-gray-50">Vendor</a>
        <div class="border-t my-1"></div>
        <a href="{{ route('settings.sla.index') }}" class="block px-3 py-2 hover:bg-gray-50">Aturan Deadline</a>
        <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 hover:bg-gray-50">Users</a>
      </div>
    </details>
  @endif

  <form method="POST" action="{{ route('logout') }}">
    @csrf
    <button class="btn btn-outline">Logout</button>
  </form>
</nav>

          @endauth

          {{-- Mobile toggle / CTA --}}
          @auth
            <div class="md:hidden flex items-center gap-2">
              <a href="{{ route('tickets.create') }}" class="btn btn-brand">Buat</a>
              <button id="navToggle" class="btn btn-outline" aria-controls="navPanel" aria-expanded="false">Menu</button>
            </div>
          @else
            <a href="{{ route('login') }}" class="text-sm underline">Login</a>
          @endauth
        </div>

        {{-- Mobile nav panel --}}
        @auth
        <div id="navPanel" class="md:hidden hidden mt-3 border-t pt-3">
        <div class="grid gap-2 text-sm">
            <a href="{{ route('dashboard') }}"
            class="btn btn-outline btn-block {{ request()->routeIs('dashboard') ? 'bg-black text-white border-black' : '' }}">
            Dashboard
            </a>
            <a href="{{ route('tickets.index') }}"
            class="btn btn-outline btn-block {{ request()->routeIs('tickets.*') ? 'bg-black text-white border-black' : '' }}">
            Tiket
            </a>
            <a href="{{ route('tickets.create') }}" class="btn btn-brand btn-block">Buat Tiket</a>

            @if(in_array((auth()->user()->role ?? null), ['PJ','SUPERADMIN'], true))
            <a href="{{ route('assets.index') }}"
                class="btn btn-outline btn-block {{ request()->routeIs('assets.*') ? 'bg-black text-white border-black' : '' }}">
                Aset
            </a>
            <a href="{{ route('reports.tickets') }}"
                class="btn btn-outline btn-block {{ request()->routeIs('reports.tickets') ? 'bg-black text-white border-black' : '' }}">
                Laporan
            </a>
            @endif

            @if((auth()->user()->role ?? null) === 'SUPERADMIN')
            <div class="px-1 text-gray-500 mt-1">Admin</div>
            <a href="{{ route('master.asset_categories.index') }}" class="btn btn-outline btn-block">Kategori Aset</a>
            <a href="{{ route('master.locations.index') }}" class="btn btn-outline btn-block">Lokasi</a>
            <a href="{{ route('master.vendors.index') }}" class="btn btn-outline btn-block">Vendor</a>
            <a href="{{ route('settings.sla.index') }}" class="btn btn-outline btn-block">Aturan Deadline</a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-block">Users</a>
            @endif

            <form method="POST" action="{{ route('logout') }}" class="mt-1">
            @csrf
            <button class="btn btn-outline btn-block">Logout</button>
            </form>
        </div>
        </div>

        @endauth
      </div>
    </header>

    {{-- Flash & Errors --}}
    @if (session('ok'))
      <div data-flash="ok" class="mb-3 border rounded-lg px-3 py-2 text-sm bg-green-50 text-green-700 border-green-200" role="status" aria-live="polite">
        {{ session('ok') }}
      </div>
    @endif
    @if ($errors->any())
      <div class="mb-3 border rounded-xl px-3 py-2 text-sm bg-red-50 text-red-700 border-red-200">
        <ul class="list-disc ml-5">
          @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
      </div>
    @endif
{{-- ===== Page Header (judul halaman + breadcrumb + actions) ===== --}}
@php
  $routeName = optional(request()->route())->getName();
  $defaultTitleMap = [
    // utama
    'dashboard'            => 'Dashboard',
    'tickets.index'        => 'Daftar Tiket',
    'tickets.create'       => 'Buat Tiket',
    'tickets.show'         => 'Detail Tiket',
    'assets.index'         => 'Aset',
    'reports.tickets'      => 'Laporan Tiket',
    // admin/pengaturan
    'settings.sla.index'   => 'Aturan Deadline',
    'admin.users.index'    => 'Pengguna',
    'admin.users.create'   => 'Tambah Pengguna',
    'admin.users.edit'     => 'Ubah Pengguna',
    'master.asset_categories.index' => 'Kategori Aset',
    'master.locations.index'        => 'Lokasi',
    'master.vendors.index'          => 'Vendor',
  ];
  // judul otomatis berdasar route, fallback ke <title> atau custom section
  $autoTitle = $defaultTitleMap[$routeName] ?? ($title ?? null);
  // izinkan view override via @section('page_title')
  $pageTitle = trim($__env->yieldContent('page_title', $autoTitle));
  $pageSubtitle = trim($__env->yieldContent('page_subtitle', ''));
@endphp

@if($pageTitle)
  <div class="card mb-3">
    <div class="bar">
      <div>
        <h1 class="text-lg font-semibold">{{ $pageTitle }}</h1>
        @if($pageSubtitle)
          <div class="text-sm text-gray-500 mt-0.5">{{ $pageSubtitle }}</div>
        @endif

        {{-- Breadcrumb opsional: kirim $breadcrumbs = [['label'=>'Tiket','url'=>...], ['label'=>'Detail']] dari view --}}
        @if(isset($breadcrumbs) && is_array($breadcrumbs) && count($breadcrumbs))
          <nav class="text-xs text-gray-500 mt-2" aria-label="Breadcrumb">
            <ol class="flex items-center gap-1 flex-wrap">
              @foreach($breadcrumbs as $i => $b)
                @if(!empty($b['url']) && $i < count($breadcrumbs)-1)
                  <li><a href="{{ $b['url'] }}" class="underline">{{ $b['label'] }}</a></li>
                  <li aria-hidden="true">›</li>
                @else
                  <li aria-current="page">{{ $b['label'] }}</li>
                @endif
              @endforeach
            </ol>
          </nav>
        @endif
      </div>

      {{-- Aksi cepat opsional: @section('page_actions') ... @endsection --}}
      @hasSection('page_actions')
        <div class="flex items-center gap-2">
          @yield('page_actions')
        </div>
      @endif
    </div>
  </div>
@endif
{{-- ===== End Page Header ===== --}}

    {{-- Main content --}}
    <main id="main">@yield('content')</main>
  </div>
@auth
@php
  $role = auth()->user()->role ?? 'USER';
  $isAdmin = in_array($role, ['PJ','SUPERADMIN'], true);
@endphp
<div class="fixed md:hidden bottom-0 left-0 right-0 border-t bg-white/95 backdrop-blur z-40">
  <nav class="max-w-7xl mx-auto grid {{ $isAdmin ? 'grid-cols-4' : 'grid-cols-3' }} gap-1 p-2">
    <a href="{{ route('dashboard') }}"
       class="btn btn-outline btn-block {{ request()->routeIs('dashboard') ? 'bg-black text-white border-black' : '' }}"
       aria-label="Dashboard">
      🏠 <span class="sr-only">Dashboard</span>
    </a>
    <a href="{{ route('tickets.index') }}"
       class="btn btn-outline btn-block {{ request()->routeIs('tickets.*') ? 'bg-black text-white border-black' : '' }}"
       aria-label="Daftar Tiket">
      🎫 <span class="sr-only">Tiket</span>
    </a>
    <a href="{{ route('tickets.create') }}" class="btn btn-brand btn-block" aria-label="Buat Tiket">
      ➕ <span class="sr-only">Buat Tiket</span>
    </a>
    @if($isAdmin)
      <a href="{{ route('reports.tickets') }}"
         class="btn btn-outline btn-block {{ request()->routeIs('reports.tickets') ? 'bg-black text-white border-black' : '' }}"
         aria-label="Laporan">
        📊 <span class="sr-only">Laporan</span>
      </a>
    @endif
  </nav>
</div>
@endauth

</body>
  {{-- JS kecil: toggle mobile, auto-hide flash, cegah double submit --}}
  <script>
    const btn = document.getElementById('navToggle');
    const panel = document.getElementById('navPanel');
    if (btn && panel) {
      btn.addEventListener('click', () => {
        const hidden = panel.classList.toggle('hidden');
        btn.setAttribute('aria-expanded', String(!hidden));
      });
    }

    const flash = document.querySelector('[data-flash="ok"]');
    if (flash) setTimeout(()=>flash.remove(), 3000);

    for (const f of document.querySelectorAll('form')) {
      f.addEventListener('submit', () => {
        const btn = f.querySelector('button[type="submit"],button:not([type])');
        if (btn) btn.disabled = true;
      });
    }

        if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' })
          .then(reg => {
            reg.addEventListener('updatefound', () => {
              const nw = reg.installing;
              nw?.addEventListener('statechange', () => {
                if (nw.state === 'installed' && navigator.serviceWorker.controller) {
                  const bar = document.createElement('div');
                  bar.className = 'fixed bottom-16 left-0 right-0 mx-auto max-w-xl bg-black text-white text-sm rounded-xl shadow p-3 z-50';
                  bar.innerHTML = 'Versi baru tersedia. <button id="swRefresh" class="btn btn-brand" style="margin-left:.5rem">Muat ulang</button>';
                  document.body.appendChild(bar);
                  document.getElementById('swRefresh').onclick = () => location.reload();
                }
              });
            });
          })
          .catch(console.error);
      });
      navigator.serviceWorker.addEventListener('controllerchange', () => location.reload());
    }
  </script>
</html>
