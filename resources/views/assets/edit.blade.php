@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded-xl shadow">
  <h2 class="text-lg font-semibold mb-3">Edit Aset</h2>
  <form method="post" action="{{ route('assets.update',$asset->id) }}" class="grid gap-3">
    @csrf @method('put')
    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-xs text-gray-600">Kode Aset</label>
        <input class="border rounded-lg p-2 w-full bg-gray-100" value="{{ $asset->kode_aset }}" disabled>
      </div>
      <div>
        <label class="block text-xs text-gray-600">Nama</label>
        <input name="nama" class="border rounded-lg p-2 w-full" value="{{ old('nama',$asset->nama) }}" required>
      </div>
      <div>
        <label class="block text-xs text-gray-600">Kategori</label>
        <select name="asset_category_id" class="border rounded-lg p-2 w-full" required>
          @foreach($categories as $c)
            <option value="{{ $c->id }}" @selected($asset->asset_category_id==$c->id)>{{ $c->nama }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs text-gray-600">Lokasi</label>
        <select name="location_id" class="border rounded-lg p-2 w-full">
          <option value="">—</option>
          @foreach($locations as $l)
            <option value="{{ $l->id }}" @selected($asset->location_id==$l->id)>{{ $l->nama }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs text-gray-600">Vendor</label>
        <select name="vendor_id" class="border rounded-lg p-2 w-full">
          <option value="">—</option>
          @foreach($vendors as $v)
            <option value="{{ $v->id }}" @selected($asset->vendor_id==$v->id)>{{ $v->nama }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs text-gray-600">Status</label>
        <select name="status" class="border rounded-lg p-2 w-full">
          @foreach (['AKTIF','RUSAK','SCRAP'] as $s)
            <option value="{{ $s }}" @selected($asset->status===$s)>{{ $s }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs text-gray-600">Tanggal Beli</label>
        <input type="date" name="tanggal_beli" class="border rounded-lg p-2 w-full" value="{{ $asset->tanggal_beli ? $asset->tanggal_beli->format('Y-m-d') : '' }}">
      </div>
      <div class="md:col-span-2">
        <label class="block text-xs text-gray-600">Spesifikasi (JSON)</label>
        <textarea name="spesifikasi" rows="6" class="border rounded-lg p-2 w-full" required>{{ old('spesifikasi', json_encode($asset->spesifikasi, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) }}</textarea>
      </div>
    </div>
    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-black text-white text-sm">Simpan</button>
      <a href="{{ route('assets.index') }}" class="px-4 py-2 rounded-lg border text-sm">Batal</a>
    </div>
  </form>
</div>
@endsection
