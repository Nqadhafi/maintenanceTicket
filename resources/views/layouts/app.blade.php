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
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:bg-white focus:px-3 focus:py-2 focus:rounded-md">Lewati ke konten</a>

<div class="max-w-7xl mx-auto p-4 pb-20 md:pb-4">

  {{-- TOP BAR / NAV (sticky + blur) --}}
  <header role="banner" class="mb-4 sticky top-0 z-40">
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
          $activeBtn = fn($pat) => request()->routeIs($pat) ? 'bg-black text-white border-black' : '';
        @endphp
        <nav aria-label="Navigasi utama" class="hidden md:flex items-center gap-2 text-sm">
          <a href="{{ route('dashboard') }}" class="btn btn-outline {{ $activeBtn('dashboard') }}">Dashboard</a>
          <a href="{{ route('tickets.index') }}" class="btn btn-outline {{ $activeBtn('tickets.*') }}">Tiket</a>
          <a href="{{ route('tickets.create') }}" class="btn btn-brand">Buat Tiket</a>

          @if(in_array((auth()->user()->role ?? null), ['PJ','SUPERADMIN'], true))
            <a href="{{ route('assets.index') }}" class="btn btn-outline {{ $activeBtn('assets.*') }}">Aset</a>
            <a href="{{ route('reports.tickets') }}" class="btn btn-outline {{ $activeBtn('reports.tickets') }}">Laporan</a>
          @endif

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

          <form method="POST" action="{{ route('logout') }}" class="ml-1">
            @csrf
            <button class="btn btn-outline">Logout</button>
          </form>
        </nav>
        @endauth

        {{-- Mobile actions (CTA + Drawer toggle) --}}
        @auth
          <div class="md:hidden flex items-center gap-2">
            <a href="{{ route('tickets.create') }}" class="btn btn-brand">Buat</a>
            <button id="drawerToggle" class="btn btn-outline" aria-controls="appDrawer" aria-expanded="false">Menu</button>
          </div>
        @else
          <a href="{{ route('login') }}" class="text-sm underline">Login</a>
        @endauth
      </div>
    </div>
  </header>

  {{-- Drawer samping (mobile) --}}
  @auth
  <div id="appDrawer" class="drawer" aria-hidden="true" aria-labelledby="drawerTitle">
    <div class="backdrop" data-close></div>
    <aside class="panel outline-none" role="dialog" aria-modal="true" aria-label="Menu" tabindex="-1">
      <button class="btn btn-outline close" data-close aria-label="Tutup">✕</button>

      <div id="drawerTitle" class="text-sm text-gray-500 mb-3">Navigasi</div>
      <div class="grid gap-2 text-sm">
        @php
          $activeBtn = fn($pat) => request()->routeIs($pat) ? 'bg-black text-white border-black' : '';
        @endphp
        <a href="{{ route('dashboard') }}" class="btn btn-outline btn-block {{ $activeBtn('dashboard') }}">Dashboard</a>
        <a href="{{ route('tickets.index') }}" class="btn btn-outline btn-block {{ $activeBtn('tickets.*') }}">Tiket</a>
        <a href="{{ route('tickets.create') }}" class="btn btn-brand btn-block">Buat Tiket</a>

        @if(in_array((auth()->user()->role ?? null), ['PJ','SUPERADMIN'], true))
          <a href="{{ route('assets.index') }}" class="btn btn-outline btn-block {{ $activeBtn('assets.*') }}">Aset</a>
          <a href="{{ route('reports.tickets') }}" class="btn btn-outline btn-block {{ $activeBtn('reports.tickets') }}">Laporan</a>
        @endif

        @if((auth()->user()->role ?? null) === 'SUPERADMIN')
          <div class="px-1 text-gray-500 mt-1">Admin</div>
          <a href="{{ route('master.asset_categories.index') }}" class="btn btn-outline btn-block">Kategori Aset</a>
          <a href="{{ route('master.locations.index') }}" class="btn btn-outline btn-block">Lokasi</a>
          <a href="{{ route('master.vendors.index') }}" class="btn btn-outline btn-block">Vendor</a>
          <a href="{{ route('settings.sla.index') }}" class="btn btn-outline btn-block">Aturan Deadline</a>
          <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-block">Users</a>
        @endif

        <form method="POST" action="{{ route('logout') }}" class="mt-2">
          @csrf
          <button class="btn btn-outline btn-block">Logout</button>
        </form>
      </div>
    </aside>
  </div>
  @endauth

  {{-- Flash & Errors --}}
  @if (session('ok'))
    <div data-flash="ok" class="mb-3 border rounded-lg px-3 py-2 text-sm bg-green-50 text-green-700 border-green-200" role="status" aria-live="polite">
      {{ session('ok') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="mb-3 border rounded-xl px-3 py-2 text-sm bg-red-50 text-red-700 border-red-200" role="alert">
      <ul class="list-disc ml-5">
        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- ===== Page Header (judul + breadcrumb + actions) ===== --}}
  @php
    $routeName = optional(request()->route())->getName();
    $defaultTitleMap = [
      'dashboard'            => 'Dashboard',
      'tickets.index'        => 'Daftar Tiket',
      'tickets.create'       => 'Buat Tiket',
      'tickets.show'         => 'Detail Tiket',
      'assets.index'         => 'Aset',
      'reports.tickets'      => 'Laporan Tiket',
      'settings.sla.index'   => 'Aturan Deadline',
      'admin.users.index'    => 'Pengguna',
      'admin.users.create'   => 'Tambah Pengguna',
      'admin.users.edit'     => 'Ubah Pengguna',
      'master.asset_categories.index' => 'Kategori Aset',
      'master.locations.index'        => 'Lokasi',
      'master.vendors.index'          => 'Vendor',
    ];
    $autoTitle = $defaultTitleMap[$routeName] ?? ($title ?? null);
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
  <main id="main" role="main">@yield('content')</main>
</div>

@auth
@php
  $role = auth()->user()->role ?? 'USER';
  $isAdmin = in_array($role, ['PJ','SUPERADMIN'], true);
@endphp
<div class="fixed md:hidden bottom-0 left-0 right-0 border-t bg-white/95 backdrop-blur z-40" role="navigation" aria-label="Bottom bar">
  <nav class="max-w-7xl mx-auto grid {{ $isAdmin ? 'grid-cols-4' : 'grid-cols-3' }} gap-1 p-2">
    <a href="{{ route('dashboard') }}" class="btn btn-outline btn-block {{ request()->routeIs('dashboard') ? 'bg-black text-white border-black' : '' }}" aria-label="Dashboard">🏠 <span class="sr-only">Dashboard</span></a>
    <a href="{{ route('tickets.index') }}" class="btn btn-outline btn-block {{ request()->routeIs('tickets.*') ? 'bg-black text-white border-black' : '' }}" aria-label="Daftar Tiket">🎫 <span class="sr-only">Tiket</span></a>
    <a href="{{ route('tickets.create') }}" class="btn btn-brand btn-block" aria-label="Buat Tiket">➕ <span class="sr-only">Buat Tiket</span></a>
    @if($isAdmin)
      <a href="{{ route('reports.tickets') }}" class="btn btn-outline btn-block {{ request()->routeIs('reports.tickets') ? 'bg-black text-white border-black' : '' }}" aria-label="Laporan">📊 <span class="sr-only">Laporan</span></a>
    @endif
  </nav>
</div>
@endauth

{{-- FAB: Install App --}}
<div id="installFabWrap" class="fab-wrap hidden">
  <button id="installFab" class="btn btn-brand">Install App</button>
</div>

{{-- Banner iOS A2HS --}}
<div id="iosA2HS" class="hidden" aria-live="polite">
  <div class="toast">
    <strong>Pasang di Layar Utama</strong><br>
    Buka menu <span aria-label="Share">Bagikan</span> → pilih <em>Tambahkan ke Layar Utama</em>.
    <button type="button" class="btn btn-outline" style="margin-left:.5rem"
            onclick="document.getElementById('iosA2HS').classList.add('hidden')">Tutup</button>
  </div>
</div>

{{-- JS: drawer toggle, flash, prevent double submit, SW, install --}}
<script>
  // Drawer
  const drawer = document.getElementById('appDrawer');
  const btnDrawer = document.getElementById('drawerToggle');

  function openDrawer() {
    if (!drawer) return;
    drawer.classList.add('active');
    btnDrawer?.setAttribute('aria-expanded','true');
    drawer.setAttribute('aria-hidden','false');
    const first = drawer.querySelector('a,button,input,select,textarea');
    first && first.focus();
  }
  function closeDrawer() {
    if (!drawer) return;
    drawer.classList.remove('active');
    btnDrawer?.setAttribute('aria-expanded','false');
    drawer.setAttribute('aria-hidden','true');
  }
  btnDrawer?.addEventListener('click', () => {
    drawer.classList.contains('active') ? closeDrawer() : openDrawer();
  });
  drawer?.addEventListener('click', (e) => {
    if (e.target.matches('.backdrop,[data-close]')) closeDrawer();
  });
  window.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeDrawer(); });

  // Flash ok auto-hide
  const flash = document.querySelector('[data-flash="ok"]');
  if (flash) setTimeout(()=>flash.remove(), 3000);

  // Prevent double submit
  for (const f of document.querySelectorAll('form')) {
    f.addEventListener('submit', () => {
      const btn = f.querySelector('button[type="submit"],button:not([type])');
      if (btn) btn.disabled = true;
    });
  }

  // Service Worker
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

  // PWA Install FAB
  function alreadyInstalled() {
    return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
  }
  function show(el){ el && el.classList.remove('hidden'); }
  function hide(el){ el && el.classList.add('hidden'); }

  const fabWrap = document.getElementById('installFabWrap');
  const fabBtn  = document.getElementById('installFab');
  const iosBar  = document.getElementById('iosA2HS');
  let deferredPrompt = null;

  const ua = navigator.userAgent.toLowerCase();
  const isIOS = /iphone|ipad|ipod/.test(ua);
  const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);

  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    if (alreadyInstalled()) return;
    deferredPrompt = e;
    show(fabWrap);
  });

  async function triggerInstall() {
    if (alreadyInstalled()) { hide(fabWrap); return; }
    if (deferredPrompt) {
      deferredPrompt.prompt();
      const choice = await deferredPrompt.userChoice;
      if (choice.outcome === 'accepted') hide(fabWrap);
      deferredPrompt = null;
      return;
    }
    if (isIOS && isSafari) { show(iosBar); return; }
  }
  fabBtn?.addEventListener('click', triggerInstall);

  window.addEventListener('appinstalled', () => {
    hide(fabWrap);
    const bar = document.createElement('div');
    bar.className = 'toast';
    bar.textContent = 'Aplikasi berhasil dipasang.';
    document.body.appendChild(bar);
    setTimeout(()=>bar.remove(), 2500);
  });

  window.addEventListener('load', () => {
    if (alreadyInstalled()) { hide(fabWrap); return; }
    if (isIOS && isSafari) { show(fabWrap); }
  });
</script>
</body>
</html>
