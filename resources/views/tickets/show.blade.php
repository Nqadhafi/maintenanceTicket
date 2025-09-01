@extends('layouts.app')
@php
  $breadcrumbs = [
    ['label'=>'Tiket', 'url'=>route('tickets.index')],
    ['label'=>'Detail']
  ];
@endphp
@section('content')
<div class="bg-white p-4 rounded-xl shadow">
  <div class="flex items-start justify-between">
    <div>
      <div class="text-lg font-semibold">[{{ $ticket->kode_tiket }}] {{ $ticket->judul }}</div>
      <div class="text-sm text-gray-600">{{ $ticket->kategori }} • {{ $ticket->urgensi }} • {{ $ticket->status }}</div>
      @if($ticket->sla_due_at)
        <div class="text-xs text-gray-500">SLA: {{ $ticket->sla_due_at->format('d/m/Y H:i') }}</div>
      @endif
    </div>
  <a href="{{ route('tickets.index') }}" class="text-sm underline">Kembali</a>
  <a href="{{ route('tickets.edit',$ticket->id) }}" class="text-sm underline">Edit</a>
  </div>

  <div class="mt-3 text-sm whitespace-pre-line">{{ $ticket->deskripsi }}</div>

  <div class="mt-4 grid md:grid-cols-2 gap-3">
    <div class="border rounded-lg p-3">
      <div class="font-medium mb-2">Aksi</div>
      <form method="post" action="{{ route('tickets.updateStatus',$ticket->id) }}" class="flex gap-2 items-center mb-3">
        @csrf
        <select name="status" class="border rounded-lg p-2">
          @foreach (['OPEN','ASSIGNED','IN_PROGRESS','PENDING','RESOLVED','CLOSED'] as $s)
            <option value="{{ $s }}" @selected $ticket->status => {{ $s }}</option>
          @endforeach
        </select>
        <button class="px-3 py-2 rounded-lg bg-black text-white text-sm">Ubah Status</button>
      </form>

      <form method="post" action="{{ route('tickets.assign',$ticket->id) }}" class="flex gap-2 items-center">
        @csrf
        <select name="assignee_id" class="border rounded-lg p-2">
          @foreach ($pjs as $u)
            <option value="{{ $u->id }}" @selected optional $ticket->assignee =>{{ $u->name }} ({{ $u->divisi }})</option>
          @endforeach
        </select>
        <button class="px-3 py-2 rounded-lg border text-sm">Assign</button>
      </form>
    </div>

    <div class="border rounded-lg p-3">
      <div class="font-medium mb-2">Aset</div>
      @if ($ticket->asset)
        <div class="text-sm">{{ $ticket->asset->kode_aset }} — {{ $ticket->asset->nama }}</div>
      @elseif($ticket->is_asset_unlisted || $ticket->kategori==='LAINNYA')
        <div class="text-sm">{{ $ticket->asset_nama_manual }} ({{ $ticket->asset_lokasi_manual }})</div>
        @if($ticket->asset_vendor_manual)
          <div class="text-xs text-gray-500">Vendor/Merk: {{ $ticket->asset_vendor_manual }}</div>
        @endif
      @else
        <div class="text-sm text-gray-500">—</div>
      @endif
    </div>
  </div>

  <div class="mt-4 grid md:grid-cols-2 gap-3">
    <div class="border rounded-lg p-3">
      <div class="font-medium mb-2">Komentar</div>
      <div class="space-y-3 max-h-64 overflow-auto pr-1">
        @forelse ($ticket->comments as $c)
          <div class="border rounded-lg p-2 {{ $c->is_internal ? 'bg-yellow-50' : 'bg-gray-50' }}">
            <div class="text-xs text-gray-600">{{ $c->user->name }} • {{ $c->created_at->format('d/m/Y H:i') }} @if($c->is_internal) • internal @endif</div>
            <div class="text-sm whitespace-pre-line">{{ $c->body }}</div>
          </div>
        @empty
          <div class="text-sm text-gray-500">Belum ada komentar.</div>
        @endforelse
      </div>
      <form method="post" action="{{ route('tickets.comment',$ticket->id) }}" class="mt-3 grid gap-2">
        @csrf
        <textarea name="body" rows="3" class="border rounded-lg p-2" placeholder="Tulis komentar..." required></textarea>
        <label class="inline-flex items-center text-sm">
          <input type="checkbox" name="is_internal" value="1" class="mr-2"> Komentar internal (hanya PJ/Superadmin)
        </label>
        <button class="px-3 py-2 rounded-lg bg-black text-white text-sm self-start">Kirim</button>
      </form>
    </div>

<div class="border rounded-lg p-3">
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
    <input type="file" name="file" class="border rounded-lg p-2" required
           accept=".jpg,.jpeg,.png,.mp4,.pdf,.doc,.docx,.xls,.xlsx">
    <div class="text-xs text-gray-500">Maks. 5 MB. Tipe: JPG, PNG, MP4, PDF, DOC, DOCX, XLS, XLSX.</div>
    @include('partials.field-error', ['field' => 'file'])
    <button class="px-3 py-2 rounded-lg border text-sm self-start">Upload</button>
  </form>
</div>

  </div>
</div>
@endsection
