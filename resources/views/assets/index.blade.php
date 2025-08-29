@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded-xl shadow">
  <div class="flex items-center justify-between mb-3">
    <h2 class="text-lg font-semibold">Aset</h2>
    <a href="{{ route('assets.create') }}" class="px-3 py-2 rounded-lg bg-black text-white text-sm">Tambah Aset</a>
  </div>

  <form method="get" class="grid gap-2 md:grid-cols-4">
    <input class="border rounded-lg p-2" name="q" placeholder="Cari nama/kode" value="{{ $filters['q'] }}">
    <select class="border rounded-lg p-2" name="category_id">
      <option value="">Kategori (semua)</option>
      @foreach ($categories as $c)
        <option value="{{ $c->id }}" @selected($filters['category_id']==$c->id)>{{ $c->nama }}</option>
      @endforeach
    </select>
    <select class="border rounded-lg p-2" name="location_id">
      <option value="">Lokasi (semua)</option>
      @foreach ($locations as $l)
        <option value="{{ $l->id }}" @selected($filters['location_id']==$l->id)>{{ $l->nama }}</option>
      @endforeach
    </select>
    <select class="border rounded-lg p-2" name="status">
      <option value="">Status (semua)</option>
      @foreach (['AKTIF','RUSAK','SCRAP'] as $s)
        <option value="{{ $s }}" @selected($filters['status']===$s)>{{ $s }}</option>
      @endforeach
    </select>
    <div class="md:col-span-4">
      <button class="px-3 py-2 rounded-lg bg-gray-900 text-white text-sm">Terapkan Filter</button>
    </div>
  </form>

  <div class="mt-4 overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left border-b">
          <th class="py-2 pr-2">Kode</th>
          <th class="py-2 pr-2">Nama</th>
          <th class="py-2 pr-2">Kategori</th>
          <th class="py-2 pr-2">Lokasi</th>
          <th class="py-2 pr-2">Status</th>
          <th class="py-2 pr-2">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($assets as $a)
          <tr class="border-b">
            <td class="py-2 pr-2">{{ $a->kode_aset }}</td>
            <td class="py-2 pr-2">{{ $a->nama }}</td>
            <td class="py-2 pr-2">{{ optional($a->category)->nama }}</td>
            <td class="py-2 pr-2">{{ optional($a->location)->nama }}</td>
            <td class="py-2 pr-2">{{ $a->status }}</td>
            <td class="py-2 pr-2">
              <a href="{{ route('assets.edit',$a->id) }}" class="underline">Edit</a>
              <form method="post" action="{{ route('assets.destroy',$a->id) }}" class="inline-block ml-2" onsubmit="return confirm('Hapus aset ini?')">
                @csrf @method('delete')
                <button class="underline text-red-600">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="py-3 text-gray-500">Tidak ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $assets->links() }}</div>
</div>
@endsection
