<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ $title ?? 'Masuk | Ticketing' }}</title>
  <link rel="stylesheet" href="{{ mix('css/app.css') }}">
  <link rel="manifest" href="/manifest.webmanifest">
  <meta name="theme-color" content="#0ea5e9">
</head>
<body class="bg-gray-100 min-h-screen">

  {{-- Wrapper penuh layar --}}
  <div class="relative min-h-screen">
    {{-- Kontainer ditaruh tepat di tengah tanpa flex/grid --}}
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md px-4">
      
      {{-- Brand / Hero (rapat) --}}
      <div class="text-center mb-4">
        <span class="inline-block align-middle">
          <img src="/icons/icon-192.png" alt="" class="w-9 h-9 rounded-lg">
        </span>
        <div class="text-lg font-semibold mt-2">Ticketing App</div>
        <div class="text-xs text-gray-500 mt-1">
          Masuk untuk membuat & memantau tiket.
        </div>
      </div>

      {{-- Flash & Errors (global) --}}
      @if (session('status'))
        <div class="mb-2 border rounded-lg px-3 py-2 text-sm bg-green-50 text-green-700 border-green-200" role="status" aria-live="polite">
          {{ session('status') }}
        </div>
      @endif
      @if ($errors->any())
        <div class="mb-2 border rounded-lg px-3 py-2 text-sm bg-red-50 text-red-700 border-red-200">
          <ul class="list-disc ml-5">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
          </ul>
        </div>
      @endif

      {{-- Konten utama (login form, dst.) --}}
      <main id="main">
        @yield('content')
      </main>

      {{-- Footer kecil --}}
      <div class="text-center text-[11px] text-gray-500 mt-4">
        © {{ date('Y') }} PT. Shabat Warna Gemilang
      </div>
    </div>
  </div>

  <script>
    const flash = document.querySelector('[role="status"][aria-live="polite"]');
    if (flash) setTimeout(()=>flash.remove(), 3000);
  </script>
</body>
</html>
