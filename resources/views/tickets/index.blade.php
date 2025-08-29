@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded-xl shadow">
  <form method="get" class="grid gap-2 md:grid-cols-4">
    <input class="border rounded-lg p-2" name="q" placeholder="Cari judul/deskripsi" value="{{ $filters['q'] }}">
    <select class="border rounded-lg p-2" name="status">
      <option value="">Status (semua)</option>
      @foreach (['OPEN','ASSIGNED','IN_PROGRESS','PENDING','RESOLVED','CLOSED'] as $s)
        <option value="{{ $s }}" @selected($filters['status']===$s)>{{ $s }}</option>
      @endforeach
    </select>
    <select class="border rounded-lg p-2" name="kategori">
      <option value="">Kategori (semua)</option>
      @foreach (['IT','PRODUKSI','GA','LAINNYA'] as $k)
        <option value="{{ $k }}" @selected($filters['kategori']===$k)>{{ $k }}</option>
      @endforeach
    </select>
    <select class="border rounded-lg p-2" name="urgensi">
      <option value="">Urgensi (semua)</option>
      @foreach (['RENDAH','SEDANG','TINGGI','DARURAT'] as $u)
        <option value="{{ $u }}" @selected($filters['urgensi']===$u)>{{ $u }}</option>
      @endforeach
    </select>
    <div class="md:col-span-4">
      <button class="px-3 py-2 rounded-lg bg-black text-white text-sm">Terapkan Filter</button>
    </div>
  </form>

  <div class="mt-4 divide-y">
    @forelse ($tickets as $t)
      <div class="py-3 flex items-start justify-between">
        <div>
          <div class="font-medium">[{{ $t->kode_tiket }}] {{ $t->judul }}</div>
          <div class="text-xs text-gray-600">{{ $t->kategori }} • {{ $t->urgensi }} • {{ $t->status }}</div>
          @if($t->sla_due_at)
            <div class="text-xs text-gray-500">SLA: {{ $t->sla_due_at->format('d/m/Y H:i') }}</div>
          @endif
        </div>
<a href="{{ route('tickets.show',$t->id) }}" class="btn btn-outline">Detail</a>

      </div>
    @empty
      <div class="text-sm text-gray-500">Tidak ada tiket.</div>
    @endforelse
  </div>

  <div class="mt-4">{{ $tickets->links() }}</div>
</div>
@endsection
