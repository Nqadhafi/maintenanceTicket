@extends('layouts.app')

@php
  use Illuminate\Support\Str;

  $breadcrumbs = [
    ['label'=>'Tiket', 'url'=>route('tickets.index')],
    ['label'=>'Detail']
  ];

  // Normalisasi relasi aset
  $asset = $ticket->asset;
  $assetKode = $asset->kode_aset ?? null;
  $assetNama = $asset->nama ?? null;

  $assetKategori = optional($asset->category)->nama
                ?? optional($asset->category)->name
                ?? optional($asset->assetCategory)->nama
                ?? optional($asset->assetCategory)->name;

  $assetLokasi   = optional($asset->location)->nama
                ?? optional($asset->location)->name
                ?? optional($asset->lokasi)->nama
                ?? optional($asset->lokasi)->name;

  $assetVendor   = optional($asset->vendor)->nama
                ?? optional($asset->vendor)->name
                ?? optional($asset->supplier)->nama
                ?? optional($asset->supplier)->name;

  // THEME: mapping status -> ikon + class
  $status = $ticket->status; // e.g., IN_PROGRESS
  $statusKey = Str::of($status)->lower()->replace('_','-'); // "in-progress"
  $themeClass = 'theme--'.$statusKey;

  $statusIcon = [
    'OPEN'        => '🆕',
    'ASSIGNED'    => '👤',
    'IN_PROGRESS' => '🛠️',
    'PENDING'     => '⏸️',
    'RESOLVED'    => '✅',
    'CLOSED'      => '🏁',
  ][$status] ?? 'ℹ️';

  $overdue = $ticket->sla_due_at && $ticket->sla_due_at->isPast() && !in_array($ticket->status, ['RESOLVED','CLOSED']);
@endphp

@section('content')
<div id="ticketPage" class="stack-lg {{ $themeClass }}">

  {{-- ===== LOADING OVERLAY (global untuk halaman ini) ===== --}}
  <div id="loadingOverlay" class="loading-overlay hidden" aria-hidden="true">
    <div class="spinner" role="status" aria-label="Memproses..."></div>
    <div class="loading-text">Memproses...</div>
  </div>

  {{-- ===== MODAL SUCCESS (auto close) ===== --}}
  @if(session('ok'))
    <div id="successToast" class="success-toast" role="alert" aria-live="polite">
      <div class="toast-icon">✅</div>
      <div class="toast-text">{{ session('ok') }}</div>
    </div>
    <script>
      setTimeout(()=>{ const t=document.getElementById('successToast'); if(t){ t.classList.add('hide'); setTimeout(()=>t.remove(),300);}}, 1400);
    </script>
  @endif

  {{-- ===== STATUS BANNER (warna mengikuti status) ===== --}}
  <div class="status-banner">
    <div class="status-badge">
      <span class="status-emoji">{{ $statusIcon }}</span>
      <span class="status-text">Status: {{ $ticket->status }}</span>
    </div>

    <div class="status-meta">
      @if($ticket->sla_due_at)
        <span class="sla-chip {{ $overdue ? 'sla-overdue' : '' }}">
          SLA: {{ $ticket->sla_due_at->format('d/m/Y H:i') }}
          @if($overdue) <b>• LEWAT</b> @endif
        </span>
      @else
        <span class="sla-chip tone-muted">SLA: —</span>
      @endif
    </div>
  </div>

  {{-- ===== Header Ticket (card ikut highlight) ===== --}}
  <div class="card card-accent">
    <div class="bar">
      <div class="min-w-0">
        <div class="text-xs text-gray-500">Kode Tiket</div>
        <h1 class="text-lg font-semibold truncate">[{{ $ticket->kode_tiket }}] {{ $ticket->judul }}</h1>
        <div class="mt-2 flex items-center gap-2 flex-wrap text-xs">
          <span class="chip cat">{{ $ticket->kategori }}</span>
          <span class="chip ug-{{ $ticket->urgensi }}">{{ $ticket->urgensi }}</span>
          <span class="chip st-{{ $ticket->status }}">{{ $ticket->status }}</span>
          @if($ticket->sla_due_at)
            <span class="chip {{ $overdue ? 'tone-danger' : 'tone-muted' }}">
              SLA: {{ $ticket->sla_due_at->format('d/m/Y H:i') }}{{ $overdue ? ' • lewat' : '' }}
            </span>
          @endif
        </div>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('tickets.index') }}" class="btn btn-outline">Kembali</a>
        <a href="{{ route('tickets.edit',$ticket->id) }}" class="btn btn-brand">Edit</a>
      </div>
    </div>

    @if($ticket->deskripsi)
      <div class="mt-3 text-sm whitespace-pre-line text-gray-800">{{ $ticket->deskripsi }}</div>
    @endif
  </div>

  {{-- ===== Info Grid ===== --}}
  <div class="grid md:grid-cols-2 gap-3">
        {{-- Aset terkait (card ikut highlight) --}}
    <div class="card card-accent">
      <div class="font-medium mb-2">Aset</div>

      @if ($asset)
        <div class="stack text-sm">
          <div class="flex items-center gap-2 flex-wrap">
            @if($assetKode)<span class="chip">{{ $assetKode }}</span>@endif
            @if($assetNama)<span class="chip tone-muted">{{ $assetNama }}</span>@endif
          </div>

          <div class="grid grid-cols-3 gap-2">
            <div class="text-gray-500">Kategori</div>
            <div class="col-span-2">{{ $assetKategori ?: '—' }}</div>

            <div class="text-gray-500">Lokasi</div>
            <div class="col-span-2">{{ $assetLokasi ?: '—' }}</div>

            <div class="text-gray-500">Vendor</div>
            <div class="col-span-2">{{ $assetVendor ?: '—' }}</div>
          </div>
        </div>
      @elseif($ticket->is_asset_unlisted || $ticket->kategori==='LAINNYA')
        <div class="stack text-sm">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="chip">{{ $ticket->asset_nama_manual ?: 'Aset tidak tercatat' }}</span>
            @if($ticket->asset_vendor_manual)
              <span class="chip tone-muted">Vendor/Merk: {{ $ticket->asset_vendor_manual }}</span>
            @endif
          </div>
          <div class="grid grid-cols-3 gap-2">
            <div class="text-gray-500">Lokasi</div>
            <div class="col-span-2">{{ $ticket->asset_lokasi_manual ?: '—' }}</div>
          </div>
        </div>
      @else
        <div class="text-sm text-gray-500">—</div>
      @endif
    </div>
    {{-- Aksi & Penugasan (card ikut highlight) --}}
    <div class="card card-accent">
      <div class="font-medium mb-2">Aksi</div>

      {{-- Ubah Status (preview tema live) --}}
      <form method="post" action="{{ route('tickets.updateStatus',$ticket->id) }}" class="js-block-on-submit flex flex-col sm:flex-row gap-2 items-stretch sm:items-center mb-3">
        @csrf
        <label class="sr-only" for="st">Status</label>
        <select id="st" name="status" class="field">
          @php $selectedStatus = (string) old('status', $ticket->status ?? ''); @endphp
          @foreach (['OPEN','ASSIGNED','IN_PROGRESS','PENDING','RESOLVED','CLOSED'] as $s)
            <option value="{{ $s }}" {{ $selectedStatus === (string) $s ? 'selected' : '' }}>
              Status : {{ $s }}
            </option>
          @endforeach
        </select>
        <button class="btn btn-primary">Ubah Status</button>
      </form>

      {{-- Assign --}}
      <form method="post" action="{{ route('tickets.assign',$ticket->id) }}" class="js-block-on-submit flex flex-col sm:flex-row gap-2 items-stretch sm:items-center">
        @csrf
        <label class="sr-only" for="pj">Assign ke</label>
        <select id="pj" name="assignee_id" class="field">
          @php $selectedAssignee = (string) old('assignee_id', $ticket->assignee_id ?? ''); @endphp
          @foreach ($pjs as $u)
            <option value="{{ $u->id }}" {{ $selectedAssignee === (string) $u->id ? 'selected' : '' }}>
              Pelaksana : {{ $u->name }} ({{ $u->divisi }})
            </option>
          @endforeach
        </select>
        <button class="btn btn-outline">Tugaskan</button>
      </form>

      <div class="mt-3 grid grid-cols-3 gap-2 text-sm">
        <div class="text-gray-500">Pelapor</div>
        <div class="col-span-2">{{ optional($ticket->pelapor)->name ?? '—' }}</div>

        <div class="text-gray-500">PJ</div>
        <div class="col-span-2">{{ optional($ticket->assignee)->name ?? '—' }}</div>

        <div class="text-gray-500">Divisi PJ</div>
        <div class="col-span-2">{{ $ticket->divisi_pj ?? '—' }}</div>

        <div class="text-gray-500">Dibuat</div>
        <div class="col-span-2">{{ optional($ticket->created_at)->format('d/m/Y H:i') }}</div>

        <div class="text-gray-500">Diupdate</div>
        <div class="col-span-2">{{ optional($ticket->updated_at)->format('d/m/Y H:i') }}</div>
      </div>
    </div>


  </div>

  {{-- ===== Komentar & Lampiran (card ikut highlight) ===== --}}
  <div class="grid md:grid-cols-2 gap-3">
    {{-- Komentar --}}
    <div class="card card-accent">
      <div class="font-medium mb-2">Komentar</div>

      <div class="space-y-3 max-h-64 overflow-auto pr-1">
        @forelse ($ticket->comments as $c)
          <div class="border rounded-lg p-2 {{ $c->is_internal ? 'bg-yellow-50' : 'bg-gray-50' }}">
            <div class="text-xs text-gray-600">
              {{ $c->user->name }} • {{ $c->created_at->format('d/m/Y H:i') }} @if($c->is_internal) • internal @endif
            </div>
            <div class="text-sm whitespace-pre-line">{{ $c->body }}</div>
          </div>
        @empty
          <div class="text-sm text-gray-500">Belum ada komentar.</div>
        @endforelse
      </div>

      <form method="post" action="{{ route('tickets.comment',$ticket->id) }}" class="js-block-on-submit mt-3 grid gap-2">
        @csrf
        <textarea name="body" rows="3" class="field" placeholder="Tulis komentar..." required></textarea>
        <label class="inline-flex items-center text-sm">
          <input type="checkbox" name="is_internal" value="1" class="mr-2"> Komentar internal (hanya PJ/Superadmin)
        </label>
        <button class="btn btn-primary self-start">Kirim</button>
      </form>
    </div>

    {{-- Lampiran --}}
    <div class="card card-accent">
      <div class="font-medium mb-2">Lampiran</div>

      <div class="space-y-2">
        @forelse ($ticket->attachments as $a)
          @php
            $fname = basename($a->path);
            $sizeKB = $a->size ? round($a->size / 1024, 1) : null;
          @endphp
          <div class="flex items-center justify-between border rounded-lg p-2">
            <div class="text-sm">
              <div class="font-medium truncate max-w-[220px]">{{ $fname }}</div>
              <div class="text-xs text-gray-500">
                {{ $a->mime }} @if($sizeKB) • {{ $sizeKB >= 1024 ? round($sizeKB/1024, 1).' MB' : $sizeKB.' KB' }} @endif
              </div>
            </div>
            <div class="flex items-center gap-2">
              <a class="text-sm underline" target="_blank" href="{{ Storage::url($a->path) }}">Lihat</a>

              @if(auth()->id() === $ticket->user_id
                  || (auth()->user()->role === 'PJ' && auth()->user()->divisi === $ticket->divisi_pj)
                  || (auth()->user()->role === 'SUPERADMIN'))
                <form method="post" action="{{ route('tickets.attachments.destroy', [$ticket->id, $a->id]) }}"
                      onsubmit="return confirm('Hapus lampiran ini?')" class="js-block-on-submit">
                  @csrf @method('delete')
                  <button class="text-sm underline text-red-600">Hapus</button>
                </form>
              @endif
            </div>
          </div>
        @empty
          <div class="text-sm text-gray-500">Belum ada lampiran.</div>
        @endforelse
      </div>

      <form method="post" action="{{ route('tickets.attach',$ticket->id) }}" enctype="multipart/form-data" class="js-block-on-submit mt-3 grid gap-2">
        @csrf
        <input type="file" name="file" class="field" required
               accept=".jpg,.jpeg,.png,.mp4,.pdf,.doc,.docx,.xls,.xlsx">
        <div class="text-xs text-gray-500">Maks. 5 MB. Tipe: JPG, PNG, MP4, PDF, DOC, DOCX, XLS, XLSX.</div>
        @include('partials.field-error', ['field' => 'file'])
        <button class="btn btn-outline self-start">Upload</button>
      </form>
    </div>
  </div>
</div>

{{-- ===== STATUS THEMING & CARD HIGHLIGHT ===== --}}
<style>
  /* Banner status */
  .status-banner{
    display:flex;align-items:center;justify-content:space-between;
    gap:.75rem;padding:.75rem 1rem;border-radius:14px;border:1px solid #e5e7eb;
    background:var(--st-bg,#f8fafc);
  }
  .status-badge{display:flex;align-items:center;gap:.5rem;font-weight:600}
  .status-emoji{font-size:1.15rem}
  .status-text{font-size:.95rem}
  .sla-chip{display:inline-flex;align-items:center;gap:.4rem;padding:.25rem .5rem;border-radius:999px;background:#f3f4f6;color:#111;font-size:.75rem}
  .sla-chip.sla-overdue{background:#fee2e2;color:#991b1b}

  /* Card highlight mengikuti status */
  .card-accent{
    border-left:4px solid var(--st-accent,#d1d5db);
    background: color-mix(in srgb, var(--st-accent) 6%, #ffffff);
    border-radius:14px;
  }

  /* THEME VARIABLES per status (applied to #ticketPage) */
  .theme--open{--st-bg:#eaf5ff;--st-accent:#60a5fa}
  .theme--assigned{--st-bg:#eef2ff;--st-accent:#818cf8}
  .theme--in-progress{--st-bg:#e9fbf2;--st-accent:#34d399}
  .theme--pending{--st-bg:#fff7e6;--st-accent:#f59e0b}
  .theme--resolved{--st-bg:#ecfeff;--st-accent:#22d3ee}
  .theme--closed{--st-bg:#f3f4f6;--st-accent:#9ca3af}

  /* Terapkan variable ke komponen umum */
  #ticketPage .btn-brand{background:var(--st-accent);border-color:var(--st-accent)}
  #ticketPage .btn-brand:hover{filter:brightness(.95)}
  #ticketPage .chip.cat{background:color-mix(in srgb, var(--st-accent) 12%, white); color:#111; border:1px solid color-mix(in srgb, var(--st-accent) 40%, white)}

  /* ====== Loading Overlay ====== */
  .loading-overlay{
    position:fixed;inset:0;background:rgba(0,0,0,.35);
    display:flex;align-items:center;justify-content:center;flex-direction:column;
    z-index:60;
  }
  .loading-overlay.hidden{display:none}
  .spinner{
    width:42px;height:42px;border-radius:9999px;border:4px solid #fff;border-top-color:transparent;animation:spin 0.8s linear infinite;
  }
  .loading-text{margin-top:.5rem;color:#fff;font-weight:600}
  @keyframes spin{to{transform:rotate(360deg)}}

  /* ====== Success Toast ====== */
  .success-toast{
    position:fixed;left:50%;top:20px;transform:translateX(-50%);
    background:#111;color:#fff;padding:.6rem .9rem;border-radius:10px;display:flex;gap:.5rem;align-items:center;z-index:70;
    box-shadow:0 10px 20px rgba(0,0,0,.18);transition:opacity .25s ease, transform .25s ease;
  }
  .success-toast.hide{opacity:0;transform:translateX(-50%) translateY(-6px)}
  .toast-icon{font-size:1rem}
  .toast-text{font-size:.9rem}

  /* Improve focus states */
  .btn:disabled,.field:disabled{opacity:.65;cursor:not-allowed}
  .form-blocked { pointer-events: none; opacity: .85; }
.form-blocked :where(button,[type="submit"]) { pointer-events: auto; }
</style>

{{-- ===== Live Theme Preview + Block Double Submit ===== --}}
<script>
(function(){
  const root = document.getElementById('ticketPage');
  const selectStatus = document.getElementById('st');
  const overlay = document.getElementById('loadingOverlay');

  const map = {
    'OPEN': 'theme--open',
    'ASSIGNED': 'theme--assigned',
    'IN_PROGRESS': 'theme--in-progress',
    'PENDING': 'theme--pending',
    'RESOLVED': 'theme--resolved',
    'CLOSED': 'theme--closed'
  };
  function applyTheme(val){
    Object.values(map).forEach(cls => root.classList.remove(cls));
    root.classList.add(map[val] || 'theme--open');
  }
  if (selectStatus) {
    applyTheme(selectStatus.value || '{{ $ticket->status }}');
    selectStatus.addEventListener('change', (e)=>applyTheme(e.target.value));
  }

  // ✅ Blok double submit TANPA men-disable input/select (agar value terkirim)
  function blockForm(form){
    if (form.dataset.submitting === '1') return;   // prevent double
    form.dataset.submitting = '1';

    // Disable hanya tombol submit
    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(btn => {
      btn.disabled = true;
      btn.dataset.prevText = btn.innerHTML;
      btn.innerHTML = 'Memproses...';
    });

    // Opsional: non-aktifkan interaksi tanpa men-disable field (biar tetap terkirim)
    form.classList.add('form-blocked'); // pakai CSS pointer-events
    if (overlay){ overlay.classList.remove('hidden'); overlay.setAttribute('aria-hidden','false'); }
  }

  document.querySelectorAll('.js-block-on-submit').forEach(form => {
    form.addEventListener('submit', function(){ blockForm(form); });
  });
})();

</script>
@endsection
