@extends('layouts.app')

@php
  $breadcrumbs = [
    ['label'=>'Tiket', 'url'=>route('tickets.index')],
    ['label'=>'Detail']
  ];

  // Normalisasi relasi aset agar aman untuk berbagai penamaan field/relasi
  $asset = $ticket->asset;
  $assetKode = $asset->kode_aset ?? null;
  $assetNama = $asset->nama ?? null;

  // Coba beberapa kemungkinan relasi/kolom untuk kategori, lokasi, vendor
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
@endphp

@section('content')
<div class="stack-lg">
  {{-- ===== Header Ticket ===== --}}
  <div class="card">
    <div class="bar">
      <div class="min-w-0">
        <div class="text-xs text-gray-500">Kode Tiket</div>
        <h1 class="text-lg font-semibold truncate">[{{ $ticket->kode_tiket }}] {{ $ticket->judul }}</h1>
        <div class="mt-2 flex items-center gap-2 flex-wrap text-xs">
          <span class="chip">{{ $ticket->kategori }}</span>
          <span class="chip ug-{{ $ticket->urgensi }}">{{ $ticket->urgensi }}</span>
          <span class="chip st-{{ $ticket->status }}">{{ $ticket->status }}</span>
          @if($ticket->sla_due_at)
            @php $overdue = $ticket->sla_due_at->isPast() && !in_array($ticket->status, ['RESOLVED','CLOSED']); @endphp
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
    {{-- Aksi & Penugasan --}}
    <div class="card">
      <div class="font-medium mb-2">Aksi</div>

      <form method="post" action="{{ route('tickets.updateStatus',$ticket->id) }}" class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center mb-3">
        @csrf
        <label class="sr-only" for="st">Status</label>
        <select id="st" name="status" class="field">
          @foreach (['OPEN','ASSIGNED','IN_PROGRESS','PENDING','RESOLVED','CLOSED'] as $s)
            <option value="{{ $s }}" @selected($ticket->status === $s)>{{ $s }}</option>
          @endforeach
        </select>
        <button class="btn btn-primary">Ubah Status</button>
      </form>

      <form method="post" action="{{ route('tickets.assign',$ticket->id) }}" class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center">
        @csrf
        <label class="sr-only" for="pj">Assign ke</label>
        <select id="pj" name="assignee_id" class="field">
          @foreach ($pjs as $u)
            <option value="{{ $u->id }}" @selected($ticket->assignee_id === $u->id)>{{ $u->name }} ({{ $u->divisi }})</option>
          @endforeach
        </select>
        <button class="btn btn-outline">Assign</button>
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

    {{-- Aset terkait --}}
    <div class="card">
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
  </div>

  {{-- ===== Komentar & Lampiran ===== --}}
  <div class="grid md:grid-cols-2 gap-3">
    {{-- Komentar --}}
    <div class="card">
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

      <form method="post" action="{{ route('tickets.comment',$ticket->id) }}" class="mt-3 grid gap-2">
        @csrf
        <textarea name="body" rows="3" class="field" placeholder="Tulis komentar..." required></textarea>
        <label class="inline-flex items-center text-sm">
          <input type="checkbox" name="is_internal" value="1" class="mr-2"> Komentar internal (hanya PJ/Superadmin)
        </label>
        <button class="btn btn-primary self-start">Kirim</button>
      </form>
    </div>

    {{-- Lampiran --}}
    <div class="card">
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
                      onsubmit="return confirm('Hapus lampiran ini?')">
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

      <form method="post" action="{{ route('tickets.attach',$ticket->id) }}" enctype="multipart/form-data" class="mt-3 grid gap-2">
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
@endsection
