@extends('layouts.app')

@section('content')
{{-- HERO / Quick actions --}}
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

{{-- KARTU UTAMA (compact, 1 baris di mobile) --}}
<div class="stats-grid mt-3">
  @foreach($cards as $c)
    <div class="card stat {{ !empty($c['tone']) ? 'tone-'.$c['tone'] : '' }}">
      <div class="stat-title">{{ $c['title'] }}</div>
      <div class="stat-value">{{ $c['value'] }}</div>
      <div class="stat-hint">{{ $c['hint'] }}</div>
    </div>
  @endforeach
</div>


{{-- LIST SINGKAT: tiket terbaru/prioritas --}}
<div class="card mt-3">
  <div class="bar mb-2">
    <div class="legend mb-2">
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
  if ($overdue) {
    $toneRow = 'danger';
  } elseif ($t->sla_due_at && $t->sla_due_at->isToday() && !in_array($t->status,['RESOLVED','CLOSED'])) {
    $toneRow = 'warn';
  } elseif (in_array($t->status, ['RESOLVED','CLOSED'])) {
    $toneRow = 'ok';
  }
  $deadlineStr = $t->sla_due_at ? $t->sla_due_at->format('d/m H:i') : '—';
  $createdStr  = optional($t->created_at)->format('d/m H:i');
  $urlShow     = route('tickets.show', $t->id);

  // ==== Aset sesuai skema: kode_aset, nama, relasi nama ====
  $asset       = optional($t->asset);
  $assetTag    = $asset->kode_aset ?? '';
  $assetName   = $asset->nama ?? '';
  $assetLoc    = optional($asset->location)->nama ?? optional($asset->location)->name ?? '';
  $assetVendor = optional($asset->vendor)->nama   ?? optional($asset->vendor)->name   ?? '';
  $assetCat    = optional($asset->category)->nama ?? optional($asset->category)->name ?? '';
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
          data-created="{{ $createdStr }}"

          data-asset-tag="{{ $assetTag }}"
          data-asset-name="{{ $assetName }}"
          data-asset-loc="{{ $assetLoc }}"
          data-asset-vendor="{{ $assetVendor }}"
          data-asset-cat="{{ $assetCat }}"
        >
          <div class="bar">
            <div class="font-medium">
              <span class="underline">{{ $t->kode_tiket }}</span>
              <span class="text-gray-500">— {{ $t->judul }}</span>
            </div>
            <div class="text-right">
              <div class="text-xs {{ $overdue ? 'text-red-600' : 'text-gray-500' }}">
                Deadline: {{ $deadlineStr }} @if($overdue) • lewat deadline @endif
              </div>
              <div class="text-xs text-gray-500">
                {{ $createdStr }}
              </div>
            </div>
          </div>

          <div class="flex items-center gap-2 mt-2 flex-wrap">
            <span class="chip">{{ $t->kategori }}</span>
            <span class="chip ug-{{ $t->urgensi }}">{{ $t->urgensi }}</span>
            <span class="chip st-{{ $t->status }}">{{ $t->status }}</span>
            @if($t->divisi_pj)
              <span class="chip">Divisi: {{ $t->divisi_pj }}</span>
            @endif
            @if($t->assignee)
              <span class="chip">PJ: {{ $t->assignee->name }}</span>
            @endif
            @if($assetTag || $assetName)
              <span class="chip">Aset: {{ $assetTag ?: $assetName }}</span>
            @endif
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>

{{-- ===== Modal Ringkas Tiket (dengan info aset) ===== --}}
<div id="ticketModal" class="fixed inset-0 hidden z-50" aria-hidden="true">
  <div class="absolute inset-0 bg-black/40" data-close></div>
  <div class="relative max-w-lg mx-auto mt-24 px-4">
    <div class="card">
      <div class="bar">
        <div>
          <div id="m_kode" class="text-sm text-gray-500">#TIKET</div>
          <h2 id="m_judul" class="text-lg font-semibold">Judul tiket</h2>
        </div>
        <button class="btn btn-outline" data-close aria-label="Tutup">✕</button>
      </div>

      <div class="stack mt-2 text-sm">
        <div class="grid grid-cols-3 gap-2">
          <div class="text-gray-500">Dibuat</div>
          <div class="col-span-2" id="m_created">—</div>

          <div class="text-gray-500">Deadline</div>
          <div class="col-span-2" id="m_deadline">—</div>

          <div class="text-gray-500">Kategori</div>
          <div class="col-span-2" id="m_kategori">—</div>

          <div class="text-gray-500">Urgensi</div>
          <div class="col-span-2"><span id="m_urgensi" class="chip">—</span></div>

          <div class="text-gray-500">Status</div>
          <div class="col-span-2"><span id="m_status" class="chip">—</span></div>

          <div class="text-gray-500">PJ</div>
          <div class="col-span-2" id="m_pj">—</div>

          <div class="text-gray-500">Divisi</div>
          <div class="col-span-2" id="m_divisi">—</div>
        </div>

        <div class="border-t my-2"></div>

        {{-- Blok aset singkat --}}
        <div class="grid grid-cols-3 gap-2">
          <div class="text-gray-500">Aset</div>
          <div class="col-span-2">
            <div class="flex items-center gap-2 flex-wrap">
              <span id="m_asset_tag" class="chip">—</span>
              <span id="m_asset_name" class="chip">—</span>
              <span id="m_asset_cat" class="chip tone-muted">—</span>
            </div>
          </div>

          <div class="text-gray-500">Lokasi</div>
          <div class="col-span-2" id="m_asset_loc">—</div>

          <div class="text-gray-500">Vendor</div>
          <div class="col-span-2" id="m_asset_vendor">—</div>
        </div>
      </div>

      <div class="mt-4 flex items-center justify-end gap-2">
        <button class="btn btn-outline" data-close>Close</button>
        <a id="m_detail_link" href="#" class="btn btn-brand">Detail Tiket</a>
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
    pj:       document.getElementById('m_pj'),
    divisi:   document.getElementById('m_divisi'),
    aTag:     document.getElementById('m_asset_tag'),
    aName:    document.getElementById('m_asset_name'),
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

    // Isi data tiket
    els.kode.textContent     = '#' + (row.dataset.kode || '');
    els.judul.textContent    = row.dataset.judul || '';
    els.created.textContent  = row.dataset.created || '—';
    els.deadline.textContent = row.dataset.deadline || '—';
    els.kategori.textContent = row.dataset.kategori || '—';

    els.urgensi.textContent = row.dataset.urgensi || '—';
    els.urgensi.className   = 'chip ' + (row.dataset.urgensi ? ('ug-' + row.dataset.urgensi) : 'tone-muted');

    els.status.textContent = row.dataset.status || '—';
    els.status.className   = 'chip ' + (row.dataset.status ? ('st-' + row.dataset.status) : 'tone-muted');

    els.pj.textContent     = row.dataset.pj || '—';
    els.divisi.textContent = row.dataset.divisi || '—';

    // Isi data aset
    els.aTag.textContent  = row.dataset.assetTag  || '—';
    els.aName.textContent = row.dataset.assetName || '—';
    els.aCat.textContent  = row.dataset.assetCat  || '—';
    els.aLoc.textContent  = row.dataset.assetLoc  || '—';
    els.aVendor.textContent = row.dataset.assetVendor || '—';

    // Link detail
    if (detailLink) detailLink.href = row.dataset.url || '#';

    openModal();
  });

  // Enter/Space untuk aksesibilitas pada row
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
