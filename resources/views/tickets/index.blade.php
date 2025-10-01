@extends('layouts.app')

@section('content')
{{-- ====== Toolbar / CTA ====== --}}
<div class="card">
  <div class="bar">
    <div class="font-semibold">Daftar Tiket</div>
    <div class="flex items-center gap-2">
      <a href="{{ route('tickets.create') }}" class="btn btn-brand">Buat Tiket</a>
    </div>
  </div>

  {{-- ====== Filters (padat): Baris 1 = Search, Baris 2 = 3 select (mobile juga 3 kolom) ====== --}}
  <details class="mt-2 md:open">
    <summary class="md:hidden btn btn-outline w-full">Filter & Pencarian</summary>

    <form method="get" class="mt-2 space-y-2">
      {{-- Baris 1: Search full width --}}
      <input
        class="field w-full text-sm"
        name="q"
        placeholder="Cari judul/deskripsi"
        value="{{ $filters['q'] ?? '' }}"
      >

      {{-- Baris 2: 3 kolom (Kategori, Status, Urgensi) — selalu 3 kolom termasuk mobile --}}
      <div class="grid grid-cols-3 gap-2">
        <select class="field text-sm" name="kategori" aria-label="Filter kategori">
          <option value="" {{ ($filters['kategori'] ?? '') === '' ? 'selected' : '' }}>Kategori (semua)</option>
          @foreach (['IT','PRODUKSI','GA','LAINNYA'] as $k)
            <option value="{{ $k }}" {{ ($filters['kategori'] ?? '') === $k ? 'selected' : '' }}>{{ $k }}</option>
          @endforeach
        </select>

        <select class="field text-sm" name="status" aria-label="Filter status">
          <option value="" {{ ($filters['status'] ?? '') === '' ? 'selected' : '' }}>Status (semua)</option>
          @foreach (['OPEN','ASSIGNED','IN_PROGRESS','PENDING','RESOLVED','CLOSED'] as $s)
            <option value="{{ $s }}" {{ ($filters['status'] ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
          @endforeach
        </select>

        <select class="field text-sm" name="urgensi" aria-label="Filter urgensi">
          <option value="" {{ ($filters['urgensi'] ?? '') === '' ? 'selected' : '' }}>Urgensi (semua)</option>
          @foreach (['RENDAH','SEDANG','TINGGI','DARURAT'] as $u)
            <option value="{{ $u }}" {{ ($filters['urgensi'] ?? '') === $u ? 'selected' : '' }}>{{ $u }}</option>
          @endforeach
        </select>
      </div>

      <div class="flex gap-2">
        <button class="btn btn-primary grow md:grow-0">Terapkan</button>
        @if( ($filters['q'] ?? '') || ($filters['status'] ?? '') || ($filters['kategori'] ?? '') || ($filters['urgensi'] ?? '') )
          <a href="{{ route('tickets.index') }}" class="btn btn-outline grow md:grow-0">Reset</a>
        @endif
      </div>
    </form>
  </details>
</div>

{{-- ====== List by Status (accordion per proses) ====== --}}
@php
  $statusOrder = ['OPEN','ASSIGNED','IN_PROGRESS','PENDING','RESOLVED','CLOSED'];
  $grouped = $tickets->getCollection()->groupBy('status');

  $statusIcon = [
    'OPEN'        => '🆕',
    'ASSIGNED'    => '👤',
    'IN_PROGRESS' => '🛠️',
    'PENDING'     => '⏸️',
    'RESOLVED'    => '✅',
    'CLOSED'      => '🏁',
  ];

  $toneFor = function($t) {
    $overdue  = $t->sla_due_at && $t->sla_due_at->isPast() && !in_array($t->status, ['RESOLVED','CLOSED']);
    $dueToday = $t->sla_due_at && $t->sla_due_at->isToday() && !in_array($t->status, ['RESOLVED','CLOSED']);
    if ($overdue) return 'danger';
    if ($dueToday) return 'warn';
    if (in_array($t->status, ['RESOLVED','CLOSED'])) return 'ok';
    return 'info';
  };
@endphp

<div class="mt-3 space-y-3">
  @foreach ($statusOrder as $statusKey)
    @php
      $list = ($grouped[$statusKey] ?? collect())->sortByDesc('created_at')->values();
      $count = $list->count();
    @endphp

    <details class="card group status-{{ strtolower($statusKey) }}">
      <summary class="bar cursor-pointer select-none gap-3">
        <div class="flex items-center gap-2">
          <span class="status-bubble">{{ $statusIcon[$statusKey] ?? 'ℹ️' }}</span>
          <span class="font-semibold">{{ $statusKey }}</span>
          <span class="chip chip-count">{{ $count }}</span>
        </div>
        <div class="text-xs md:text-sm text-gray-600 group-open:hidden">Klik untuk buka</div>
        <div class="text-xs md:text-sm text-gray-600 hidden group-open:block">Klik untuk tutup</div>
      </summary>

      <div class="p-3 pt-0">
        @if ($count === 0)
          <div class="text-sm text-gray-500 px-1 py-2">Tidak ada tiket pada status ini.</div>
        @else
          <div class="space-y-2">
            @foreach ($list as $t)
              @php
                $overdue   = $t->sla_due_at && $t->sla_due_at->isPast() && !in_array($t->status, ['RESOLVED','CLOSED']);
                $toneRow   = $toneFor($t);
                $deadline  = $t->sla_due_at ? $t->sla_due_at->format('d/m/Y H:i') : '—';

                $asset       = optional($t->asset);
                $assetName   = $asset->nama ?? '';
                $assetTag    = $asset->kode_aset ?? '';
                $assetLoc    = optional($asset->location)->nama ?? optional($asset->location)->name ?? '';
              @endphp

              <div class="ticket-card row-accent {{ $toneRow }} p-3 focus-within:ring-2 focus-within:ring-black/10">
                <div class="bar">
                  <div class="min-w-0">
                    <a href="{{ route('tickets.show',$t->id) }}" class="font-medium underline ticket-link">
                      [{{ $t->kode_tiket }}] {{ $t->judul }}
                    </a>
                    <div class="mt-1 flex items-center gap-1.5 flex-wrap text-[12px] md:text-xs">
                      {{-- Urgensi & Status lebih dulu (warna kuat) --}}
                      <span class="chip ug-{{ $t->urgensi }}">{{ $t->urgensi }}</span>
                      <span class="chip st-{{ $t->status }}">{{ $t->status }}</span>

                      {{-- Kategori berwarna --}}
                      <span class="chip cat-{{ Str::slug($t->kategori,'-') }}">{{ $t->kategori }}</span>

                      {{-- Aset & Lokasi (sekunder) --}}
                      @if($assetName || $assetTag)
                        <span class="chip tone-muted">Aset: {{ $assetName ?: $assetTag }}</span>
                      @endif
                      @if($assetLoc)
                        <span class="chip tone-muted">Lokasi: {{ $assetLoc }}</span>
                      @endif
                    </div>
                  </div>

                  <div class="text-right text-xs shrink-0">
                    <div class="{{ $overdue ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                      SLA: {{ $deadline }} @if($overdue) • lewat @endif
                    </div>
                    <div class="text-gray-500">Dibuat: {{ optional($t->created_at)->format('d/m/Y H:i') }}</div>
                    <a href="{{ route('tickets.show',$t->id) }}" class="btn btn-outline mt-2">Detail</a>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </details>
  @endforeach
</div>

{{-- ====== Pagination (tetap bawa query string) ====== --}}
<div class="mt-3">
  {{ $tickets->withQueryString()->links() }}
</div>

{{-- ====== THEME: COLORS ====== --}}
<style>
  /* Bubble kecil untuk ikon status di header accordion */
  .status-bubble{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:9999px;background:#f5f5f5}
  .chip-count{background:#111;color:#fff}

  /* Warna header tiap status (ringkas & eye-friendly) */
  details.status-open > summary{background:linear-gradient(90deg,#e8f5ff,#fff)}
  details.status-assigned > summary{background:linear-gradient(90deg,#f0f7ff,#fff)}
  details.status-in_progress > summary{background:linear-gradient(90deg,#eefcf3,#fff)}
  details.status-pending > summary{background:linear-gradient(90deg,#fff8e6,#fff)}
  details.status-resolved > summary{background:linear-gradient(90deg,#eaf8ff,#fff)}
  details.status-closed > summary{background:linear-gradient(90deg,#f6f6f6,#fff)}

  /* Chip kategori berwarna (aksesibilitas OK) */
  .chip.cat-it{background:#e6f0ff;color:#173e8a;border:1px solid #b6cef9}
  .chip.cat-produksi{background:#fff3e6;color:#8a4b17;border:1px solid #ffd4a8}
  .chip.cat-ga{background:#eaf9f0;color:#175c36;border:1px solid #bfe8cd}
  .chip.cat-lainnya{background:#f2f2f2;color:#383838;border:1px solid #e2e2e2}

  /* Accent kiri per baris (berdasarkan tone) */
  .ticket-card{border-left-width:4px;border-left-color:transparent;border-radius:12px}
  .ticket-card.info{border-left-color:#8da2fb33}
  .ticket-card.warn{border-left-color:#ffcc8033}
  .ticket-card.danger{border-left-color:#ff8a8a66}
  .ticket-card.ok{border-left-color:#69d19f66}

  /* Hover & focus agar terasa interaktif */
  .ticket-card:hover{background:#fafafa}
  .ticket-link:focus{outline:2px solid #000;outline-offset:2px;border-radius:6px}

  /* Sesuaikan chip urgensi/status jika perlu (contoh, pastikan readable) */
  .chip[class*="ug-"]{font-weight:600}
  .chip[class*="st-"]{font-weight:600}
</style>
@endsection
