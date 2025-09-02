@extends('layouts.app')

@section('content')
{{-- ====== Toolbar / Filters ====== --}}
<div class="card">
  <div class="bar">
    <div class="font-semibold">Daftar Tiket</div>
    <div class="flex items-center gap-2">
      <a href="{{ route('tickets.create') }}" class="btn btn-brand">Buat Tiket</a>
    </div>
  </div>

  {{-- Mobile: collapse filters; Desktop: always visible --}}
  <details class="mt-2 md:open">
    <summary class="md:hidden btn btn-outline w-full">Filter & Pencarian</summary>

    <form method="get" class="grid gap-2 md:grid-cols-5 mt-2">
      <input class="field" name="q" placeholder="Cari judul/deskripsi" value="{{ $filters['q'] ?? '' }}">

      <select class="field" name="status">
        <option value="">Status (semua)</option>
        @foreach (['OPEN','ASSIGNED','IN_PROGRESS','PENDING','RESOLVED','CLOSED'] as $s)
          <option value="{{ $s }}" @selected(($filters['status'] ?? '')===$s)>{{ $s }}</option>
        @endforeach
      </select>

      <select class="field" name="kategori">
        <option value="">Kategori (semua)</option>
        @foreach (['IT','PRODUKSI','GA','LAINNYA'] as $k)
          <option value="{{ $k }}" @selected(($filters['kategori'] ?? '')===$k)>{{ $k }}</option>
        @endforeach
      </select>

      <select class="field" name="urgensi">
        <option value="">Urgensi (semua)</option>
        @foreach (['RENDAH','SEDANG','TINGGI','DARURAT'] as $u)
          <option value="{{ $u }}" @selected(($filters['urgensi'] ?? '')===$u)>{{ $u }}</option>
        @endforeach
      </select>

      <div class="flex gap-2">
        <button class="btn btn-primary btn-block md:btn">Terapkan</button>
        @if(request()->hasAny(['q','status','kategori','urgensi']) && collect(request()->only('q','status','kategori','urgensi'))->filter()->isNotEmpty())
          <a href="{{ route('tickets.index') }}" class="btn btn-outline btn-block md:btn">Reset</a>
        @endif
      </div>
    </form>
  </details>
</div>

{{-- ====== List ====== --}}
<div class="card mt-3">
  @forelse ($tickets as $t)
    @php
      $overdue   = $t->sla_due_at && $t->sla_due_at->isPast() && !in_array($t->status, ['RESOLVED','CLOSED']);
      $dueToday  = $t->sla_due_at && $t->sla_due_at->isToday() && !in_array($t->status, ['RESOLVED','CLOSED']);
      $toneRow   = $overdue ? 'danger' : ($dueToday ? 'warn' : (in_array($t->status,['RESOLVED','CLOSED']) ? 'ok' : 'info'));
      $deadline  = $t->sla_due_at ? $t->sla_due_at->format('d/m/Y H:i') : '—';

      $asset       = optional($t->asset);
      $assetTag    = $asset->kode_aset ?? '';
      $assetName   = $asset->nama ?? '';
      $assetLoc    = optional($asset->location)->nama ?? optional($asset->location)->name ?? '';
    @endphp

    <div class="row-accent {{ $toneRow }} p-3 mb-2">
      <div class="bar">
        <div class="min-w-0">
          <a href="{{ route('tickets.show',$t->id) }}" class="font-medium underline">
            [{{ $t->kode_tiket }}] {{ $t->judul }}
          </a>
          <div class="mt-1 flex items-center gap-2 flex-wrap text-xs">
            <span class="chip">{{ $t->kategori }}</span>
            <span class="chip ug-{{ $t->urgensi }}">{{ $t->urgensi }}</span>
            <span class="chip st-{{ $t->status }}">{{ $t->status }}</span>
            @if($assetTag || $assetName)
              <span class="chip">Aset: {{ $assetTag ?: $assetName }}</span>
            @endif
            @if($assetLoc)
              <span class="chip tone-muted">Lokasi: {{ $assetLoc }}</span>
            @endif>
          </div>
        </div>

        <div class="text-right text-xs">
          <div class="{{ $overdue ? 'text-red-600' : 'text-gray-500' }}">SLA: {{ $deadline }}</div>
          <div class="text-gray-500">{{ optional($t->created_at)->format('d/m/Y H:i') }}</div>
          <a href="{{ route('tickets.show',$t->id) }}" class="btn btn-outline mt-2">Detail</a>
        </div>
      </div>
    </div>
  @empty
    <div class="text-sm text-gray-500">Tidak ada tiket.</div>
  @endforelse

  <div class="mt-3">{{ $tickets->withQueryString()->links() }}</div>
</div>
@endsection
