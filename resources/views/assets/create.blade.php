@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded-xl shadow">
  <h2 class="text-lg font-semibold mb-3">Tambah Aset</h2>
  <form method="post" action="{{ route('assets.store') }}" class="grid gap-3">
    @csrf
    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-xs text-gray-600">Kode Aset</label>
        <input name="kode_aset" class="border rounded-lg p-2 w-full" required>
      </div>
      <div>
        <label class="block text-xs text-gray-600">Nama</label>
        <input name="nama" class="border rounded-lg p-2 w-full" required>
      </div>
      <div>
        <label class="block text-xs text-gray-600">Kategori</label>
        <select name="asset_category_id" class="border rounded-lg p-2 w-full" required>
          @foreach($categories as $c)
            <option value="{{ $c->id }}">{{ $c->nama }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs text-gray-600">Lokasi (opsional)</label>
        <select name="location_id" class="border rounded-lg p-2 w-full">
          <option value="">—</option>
          @foreach($locations as $l)
            <option value="{{ $l->id }}">{{ $l->nama }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs text-gray-600">Vendor (opsional)</label>
        <select name="vendor_id" class="border rounded-lg p-2 w-full">
          <option value="">—</option>
          @foreach($vendors as $v)
            <option value="{{ $v->id }}">{{ $v->nama }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs text-gray-600">Status</label>
        <select name="status" class="border rounded-lg p-2 w-full">
          @foreach (['AKTIF','RUSAK','SCRAP'] as $s)
            <option value="{{ $s }}">{{ $s }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs text-gray-600">Tanggal Beli</label>
        <input type="date" name="tanggal_beli" class="border rounded-lg p-2 w-full">
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs text-gray-600">Spesifikasi (JSON)</label>
        <textarea name="spesifikasi" rows="6" class="border rounded-lg p-2 w-full" placeholder='{"tipe":"PC","cpu":"i5","ram_gb":8}' required></textarea>
      </div>
    </div>
    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-black text-white text-sm">Simpan</button>
      <a href="{{ route('assets.index') }}" class="px-4 py-2 rounded-lg border text-sm">Batal</a>
    </div>
  </form>
</div>
@endsection
