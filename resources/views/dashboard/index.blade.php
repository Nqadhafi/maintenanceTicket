@extends('layouts.app')

@section('content')
@php
  $me   = auth()->user();
  $role = $me->role ?? 'USER';
  $isAdmin = in_array($role, ['PJ','SUPERADMIN'], true);

  // Formatter tanggal Indonesia: "01 januari 2000"
  $fmtIdDate = function($dt) {
    if (!$dt) return '—';
    $bulan = ['januari','februari','maret','april','mei','juni','juli','agustus','september','oktober','november','desember'];
    $d = str_pad($dt->day, 2, '0', STR_PAD_LEFT);
    $m = $bulan[$dt->month - 1] ?? '';
    $y = $dt->year;
    return "{$d} {$m} {$y}";
  };
@endphp

{{-- ===== MOBILE APP-LIKE HEADER (padat) ===== --}}
<div class="md:hidden space-y-3">
  <div class="app-hero p-3 rounded-xl border bg-white">
    <div class="flex items-center justify-between">
      <div class="min-w-0">
        <div class="text-[11px] text-gray-500">{{ $me->divisi ?? '—' }}</div>
        <div class="font-semibold text-base truncate">{{ $me->name }}</div>
        <div class="text-[11px] text-gray-500">{{ $role }}</div>
      </div>
      <div class="avatar text-sm">{{ strtoupper(mb_substr($me->name ?? 'U', 0, 1)) }}</div>
    </div>

    {{-- Stat pills geser horizontal --}}
    <div class="mt-3 hscroll -mx-1">
      <a href="{{ route('tickets.index') }}" class="app-pill" aria-label="Lihat tiket aktif">
        <div class="k">{{ $cards[0]['title'] ?? 'Tiket Aktif' }}</div>
        <div class="v">{{ $cards[0]['value'] ?? 0 }}</div>
      </a>
      <a href="{{ route('reports.tickets') }}" class="app-pill" aria-label="Lihat tiket lewat deadline">
        <div class="k">{{ $cards[1]['title'] ?? 'Lewat Deadline' }}</div>
        <div class="v">{{ $cards[1]['value'] ?? 0 }}</div>
      </a>
    </div>
  </div>

  {{-- Grid ikon cepat (ringkas) --}}
  <div class="grid grid-cols-4 gap-2">
    <a href="{{ route('tickets.index') }}" class="icon-tile py-3" aria-label="Daftar tiket">
      <div class="ic text-base">🎫</div><div class="tx text-[11px]">Tiket</div>
    </a>
    <a href="{{ route('tickets.create') }}" class="icon-tile py-3" aria-label="Buat tiket">
      <div class="ic text-base">➕</div><div class="tx text-[11px]">Buat</div>
    </a>
    @if($isAdmin)
      <a href="{{ route('assets.index') }}" class="icon-tile py-3" aria-label="Aset">
        <div class="ic text-base">🗂️</div><div class="tx text-[11px]">Aset</div>
      </a>
      <a href="{{ route('reports.tickets') }}" class="icon-tile py-3" aria-label="Laporan">
        <div class="ic text-base">📊</div><div class="tx text-[11px]">Laporan</div>
      </a>
    @endif
  </div>
</div>

{{-- ===== DESKTOP HEADER + STATS ===== --}}
<div class="hidden md:block">
  <div class="card">
    <div class="bar">
      <div>
        <div class="text-lg font-semibold">Ringkasan Hari Ini</div>
        <div class="text-sm text-gray-500">Lihat sekilas kondisi tiket dan lakukan tindakan penting.</div>
      </div>
      @if(!empty($quick))
        <div class="flex items-center gap-2">
          @foreach($quick as $q)
            <a href="{{ $q['route'] }}" class="btn {{ $q['style'] }}">{{ $q['label'] }}</a>
          @endforeach
        </div>
      @endif
    </div>
  </div>

  <div class="stats-grid mt-3">
    @foreach($cards as $c)
      <div class="card stat {{ !empty($c['tone']) ? 'tone-'.$c['tone'] : '' }}">
        <div class="stat-title">{{ $c['title'] }}</div>
        <div class="stat-value">{{ $c['value'] }}</div>
        <div class="stat-hint">{{ $c['hint'] }}</div>
      </div>
    @endforeach
  </div>
</div>

{{-- ===== LIST SINGKAT: tiket terbaru/prioritas ===== --}}
<div class="card mt-3">
  <div class="bar mb-2">
    {{-- Legend disembunyikan di mobile untuk hemat ruang --}}
    <div class="legend mb-2 hidden md:flex">
      <span><i class="ok"></i> Selesai</span>
      <span><i class="warn"></i> Jatuh Tempo Hari Ini</span>
      <span><i class="danger"></i> Overdue</span>
      <span><i class="info"></i> Lainnya</span>
    </div>

    <div class="text-sm font-medium">Tiket Terbaru</div>
    <a href="{{ route('tickets.index') }}" class="btn btn-outline text-sm">Lihat semua</a>
  </div>

  @if($recent->isEmpty())
    <div class="text-sm text-gray-600 p-6 text-center">
      Belum ada tiket untuk ditampilkan.<br>
      <a class="btn btn-outline mt-2" href="{{ route('tickets.create') }}">Buat Tiket</a>
    </div>
  @else
    <div class="stack">
      @foreach($recent as $t)
        @php
          $overdue = $t->sla_due_at && $t->sla_due_at->isPast() && !in_array($t->status, ['RESOLVED','CLOSED']);
          $toneRow = 'info';
          if ($overdue) $toneRow = 'danger';
          elseif ($t->sla_due_at && $t->sla_due_at->isToday() && !in_array($t->status,['RESOLVED','CLOSED'])) $toneRow = 'warn';
          elseif (in_array($t->status, ['RESOLVED','CLOSED'])) $toneRow = 'ok';

          $deadlineStr = $t->sla_due_at ? $fmtIdDate($t->sla_due_at) : '—';
          $createdStr  = $t->created_at ? $fmtIdDate($t->created_at) : '—';
          $urlShow     = route('tickets.show', $t->id);

          // Aset
          $asset       = optional($t->asset);
          $assetName   = $asset->nama ?? $asset->name ?? '';
          $assetTag    = $asset->kode_aset ?? '';
          $assetLoc    = optional($asset->location)->nama ?? optional($asset->location)->name ?? '';
          $assetVendor = optional($asset->vendor)->nama   ?? optional($asset->vendor)->name   ?? '';
          $assetCat    = optional($asset->category)->nama ?? optional($asset->category)->name ?? '';

          // Pelapor
          $pelaporName = optional($t->pelapor)->name ?? '';
        @endphp

        <div
          class="border rounded-xl p-3 row-accent {{ $toneRow }} cursor-pointer js-ticket-row"
          tabindex="0"
          role="button"
          aria-label="Buka ringkas tiket {{ $t->kode_tiket }}"
          data-url="{{ $urlShow }}"
          data-kode="{{ $t->kode_tiket }}"
          data-judul="{{ $t->judul }}"
          data-deadline="{{ $deadlineStr }}"
          data-overdue="{{ $overdue ? '1':'0' }}"
          data-kategori="{{ $t->kategori }}"
          data-urgensi="{{ $t->urgensi }}"
          data-status="{{ $t->status }}"
          data-divisi="{{ $t->divisi_pj ?? '' }}"
          data-pj="{{ optional($t->assignee)->name ?? '' }}"
          data-pelapor="{{ $pelaporName }}"
          data-created="{{ $createdStr }}"
          data-asset-name="{{ $assetName }}"
          data-asset-tag="{{ $assetTag }}"
          data-asset-loc="{{ $assetLoc }}"
          data-asset-vendor="{{ $assetVendor }}"
          data-asset-cat="{{ $assetCat }}"
        >
          {{-- HEADER ROW: kode + status/urgensi kanan (mobile padat) --}}
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <div class="text-sm font-medium truncate">
                @if($assetName)
                  <span class="text-gray-700 hidden sm:inline">{{ $assetName }}</span>
                  <span class="text-gray-400 hidden sm:inline">•</span>
                @endif
                <span class="underline">{{ $t->kode_tiket }}</span>
              </div>
              <div class="text-[13px] text-gray-600 line-clamp-2 sm:line-clamp-1">{{ $t->judul }}</div>
            </div>
            <div class="flex items-center gap-1 shrink-0">
              <span class="chip st-{{ $t->status }} text-[11px] py-0.5">{{ $t->status }}</span>
              <span class="chip ug-{{ $t->urgensi }} text-[11px] py-0.5">{{ $t->urgensi }}</span>
            </div>
          </div>

          {{-- META ROW: Pelapor & Deadline (info inti) --}}
          <div class="mt-2 flex items-center justify-between gap-2">
            <div class="text-[12px] text-gray-600">
              @if($pelaporName) <span>Pelapor: {{ $pelaporName }}</span> @else <span>Pelapor: —</span> @endif
            </div>
            <div class="text-[12px] {{ $overdue ? 'text-red-600' : 'text-gray-500' }}">
              Deadline: {{ $deadlineStr }} @if($overdue) • lewat @endif
            </div>
          </div>

          {{-- CHIPS SEKUNDER: sembunyikan di mobile --}}
          <div class="mt-2 hidden md:flex items-center gap-2 flex-wrap">
            <span class="chip">{{ $t->kategori }}</span>
            @if($t->assignee) <span class="chip">PJ: {{ $t->assignee->name }}</span> @endif
            @if($t->divisi_pj) <span class="chip">Divisi: {{ $t->divisi_pj }}</span> @endif
            @if($assetName)
              <span class="chip">Aset: {{ $assetName }}</span>
            @elseif($assetTag)
              <span class="chip tone-muted">Aset: {{ $assetTag }}</span>
            @endif
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>

{{-- ===== Modal Ringkas Tiket ===== --}}
<div id="ticketModal" class="fixed inset-0 hidden z-50" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Ringkasan tiket">
  <div class="absolute inset-0 bg-black/40" data-close></div>
  <div class="relative max-w-xl md:max-w-2xl mx-auto mt-16 md:mt-24 px-3 md:px-4">
    <div class="card p-3 md:p-4">
      <div class="bar">
        <div class="min-w-0">
          <div id="m_kode" class="text-sm text-gray-500 truncate">#TIKET</div>
          <h2 id="m_judul" class="text-base md:text-lg font-semibold leading-snug break-words">Judul tiket</h2>
        </div>
        <button class="btn btn-outline text-sm md:text-base" data-close aria-label="Tutup">✕</button>
      </div>

      {{-- RINGKASAN INTI (mobile-first) --}}
      <div class="mt-2 grid grid-cols-2 gap-2 text-[12px] md:text-sm">
        <div>
          <div class="text-gray-500">Pelapor</div>
          <div id="m_pelapor">—</div>
        </div>
        <div>
          <div class="text-gray-500">PJ</div>
          <div id="m_pj">—</div>
        </div>
        <div>
          <div class="text-gray-500">Deadline</div>
          <div id="m_deadline">—</div>
        </div>
        <div>
          <div class="text-gray-500">Status</div>
          <div><span id="m_status" class="chip">—</span></div>
        </div>
      </div>

      {{-- DETAILS (collapsible di mobile) --}}
      <details class="mt-3 md:mt-4" open>
        <summary class="text-[13px] md:text-sm text-gray-600 cursor-pointer select-none">
          Detail lainnya
        </summary>
        <div class="mt-2 grid grid-cols-3 gap-2 text-[12px] md:text-sm">
          <div class="text-gray-500">Dibuat</div>
          <div class="col-span-2" id="m_created">—</div>

          <div class="text-gray-500">Kategori</div>
          <div class="col-span-2" id="m_kategori">—</div>

          <div class="text-gray-500">Urgensi</div>
          <div class="col-span-2"><span id="m_urgensi" class="chip">—</span></div>

          <div class="text-gray-500">Divisi</div>
          <div class="col-span-2" id="m_divisi">—</div>

          <div class="text-gray-500">Aset</div>
          <div class="col-span-2">
            <div class="flex items-center gap-2 flex-wrap">
              <span id="m_asset_name" class="chip">—</span>
              <span id="m_asset_cat" class="chip tone-muted">—</span>
              <span id="m_asset_tag" class="chip tone-muted">—</span>
            </div>
          </div>

          <div class="text-gray-500">Lokasi</div>
          <div class="col-span-2" id="m_asset_loc">—</div>

          <div class="text-gray-500">Vendor</div>
          <div class="col-span-2" id="m_asset_vendor">—</div>
        </div>
      </details>

      <div class="mt-3 md:mt-4 flex items-center justify-end gap-2">
        <button class="btn btn-outline text-sm md:text-base" data-close>Tutup</button>
        <a id="m_detail_link" href="#" class="btn btn-brand text-sm md:text-base">Detail Tiket</a>
      </div>
    </div>
  </div>
</div>

{{-- ===== Script Modal ===== --}}
<script>
(function(){
  const modal = document.getElementById('ticketModal');
  if (!modal) return;

  const closeEls = modal.querySelectorAll('[data-close]');
  const detailLink = document.getElementById('m_detail_link');

  const els = {
    kode:     document.getElementById('m_kode'),
    judul:    document.getElementById('m_judul'),
    created:  document.getElementById('m_created'),
    deadline: document.getElementById('m_deadline'),
    kategori: document.getElementById('m_kategori'),
    urgensi:  document.getElementById('m_urgensi'),
    status:   document.getElementById('m_status'),
    pelapor:  document.getElementById('m_pelapor'),
    pj:       document.getElementById('m_pj'),
    divisi:   document.getElementById('m_divisi'),
    aName:    document.getElementById('m_asset_name'),
    aTag:     document.getElementById('m_asset_tag'),
    aLoc:     document.getElementById('m_asset_loc'),
    aVendor:  document.getElementById('m_asset_vendor'),
    aCat:     document.getElementById('m_asset_cat'),
  };

  function openModal(){
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden','false');
    detailLink?.focus();
    document.body.style.overflow = 'hidden';
  }
  function closeModal(){
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden','true');
    document.body.style.overflow = '';
  }

  // Delegasi klik pada row
  document.addEventListener('click', function(e){
    const row = e.target.closest('.js-ticket-row');
    if (!row) return;

    els.kode.textContent     = '#' + (row.dataset.kode || '');
    els.judul.textContent    = row.dataset.judul || '';

    els.created.textContent  = row.dataset.created || '—';
    els.deadline.textContent = row.dataset.deadline || '—';

    els.kategori.textContent = row.dataset.kategori || '—';

    els.urgensi.textContent  = row.dataset.urgensi || '—';
    els.urgensi.className    = 'chip ' + (row.dataset.urgensi ? ('ug-' + row.dataset.urgensi) : 'tone-muted');

    els.status.textContent   = row.dataset.status || '—';
    els.status.className     = 'chip ' + (row.dataset.status ? ('st-' + row.dataset.status) : 'tone-muted');

    els.pelapor.textContent  = row.dataset.pelapor || '—';
    els.pj.textContent       = row.dataset.pj || '—';
    els.divisi.textContent   = row.dataset.divisi || '—';

    els.aName.textContent    = row.dataset.assetName || '—';
    els.aTag.textContent     = row.dataset.assetTag || '—';
    els.aCat.textContent     = row.dataset.assetCat || '—';
    els.aLoc.textContent     = row.dataset.assetLoc || '—';
    els.aVendor.textContent  = row.dataset.assetVendor || '—';

    if (detailLink) detailLink.href = row.dataset.url || '#';
    openModal();
  });

  // Enter/Space aksesibilitas pada row
  document.addEventListener('keydown', function(e){
    const row = e.target.closest('.js-ticket-row');
    if (!row) return;
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      row.click();
    }
  });

  // Close handlers
  closeEls.forEach(el => el.addEventListener('click', closeModal));
  modal.addEventListener('click', (e) => {
    if (e.target.matches('.bg-black\\/40,[data-close]')) closeModal();
  });
  window.addEventListener('keydown', (e) => {
    if (!modal.classList.contains('hidden') && e.key === 'Escape') closeModal();
  });
})();
</script>
@endsection
