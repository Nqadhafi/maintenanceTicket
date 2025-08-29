@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded-xl shadow">
  <h2 class="text-lg font-semibold mb-3">Edit Kategori</h2>
  <form method="post" action="{{ route('master.asset_categories.update',$row->id) }}" class="grid md:grid-cols-2 gap-3">
    @csrf @method('put')
    <div>
      <label class="block text-xs text-gray-600">Nama</label>
      <input name="nama" class="border rounded-lg p-2 w-full" value="{{ old('nama',$row->nama) }}" required>
    </div>
    <div>
      <label class="block text-xs text-gray-600">Deskripsi</label>
      <input name="deskripsi" class="border rounded-lg p-2 w-full" value="{{ old('deskripsi',$row->deskripsi) }}">
    </div>
    <div class="md:col-span-2 flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-black text-white text-sm">Simpan</button>
      <a href="{{ route('master.asset_categories.index') }}" class="px-4 py-2 rounded-lg border text-sm">Batal</a>
    </div>
  </form>
</div>
@endsection
