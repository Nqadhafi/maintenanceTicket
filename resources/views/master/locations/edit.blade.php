@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded-xl shadow">
  <h2 class="text-lg font-semibold mb-3">Edit Lokasi</h2>
  <form method="post" action="{{ route('master.locations.update',$row->id) }}" class="grid md:grid-cols-2 gap-3">
    @csrf @method('put')
    <div>
      <label class="block text-xs text-gray-600">Nama</label>
      <input name="nama" class="border rounded-lg p-2 w-full" value="{{ old('nama',$row->nama) }}" required>
    </div>
    <div>
      <label class="block text-xs text-gray-600">Detail</label>
      <input name="detail" class="border rounded-lg p-2 w-full" value="{{ old('detail',$row->detail) }}">
    </div>
    <div class="md:col-span-2 flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-black text-white text-sm">Simpan</button>
      <a href="{{ route('master.locations.index') }}" class="px-4 py-2 rounded-lg border text-sm">Batal</a>
    </div>
  </form>
</div>
@endsection
