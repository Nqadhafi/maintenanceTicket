@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded-xl shadow">
  <div class="flex items-center justify-between mb-3">
    <h2 class="text-lg font-semibold">Edit Tiket [{{ $ticket->kode_tiket }}]</h2>
    <a href="{{ route('tickets.show',$ticket->id) }}" class="text-sm underline">Kembali</a>
  </div>

  <form method="post" action="{{ route('tickets.update',$ticket->id) }}">
    @csrf @method('put')
    <div class="grid gap-3">
      <div class="grid md:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs text-gray-600">Kategori</label>
          <select name="kategori" id="kategori" class="border rounded-lg p-2 w-full">
            @foreach($kategori as $k)
              <option value="{{ $k }}" @selected(old('kategori',$ticket->kategori)===$k)>{{ $k }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-xs text-gray-600">Urgensi</label>
          <select name="urgensi" class="border rounded-lg p-2 w-full">
            @foreach($urgensi as $u)
              <option value="{{ $u }}" @selected(old('urgensi',$ticket->urgensi)===$u)>{{ $u }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div id="asetTerdaftar">
        <label class="block text-xs text-gray-600">Aset Terdaftar</label>
        <select name="asset_id" class="border rounded-lg p-2 w-full">
            @include('partials.field-error', ['field' => 'asset_id'])
          <option value="">— Pilih aset —</option>
          @foreach($assets as $a)
            <option value="{{ $a->id }}" @selected(old('asset_id', $ticket->asset_id)==$a->id)>{{ $a->kode_aset }} — {{ $a->nama }}</option>
          @endforeach
        </select>
        @php
          $checkedUnlisted = old('is_asset_unlisted', $ticket->is_asset_unlisted) ? 'checked' : '';
        @endphp
        <label class="inline-flex items-center mt-2 text-sm">
          <input type="checkbox" id="is_unlisted" name="is_asset_unlisted" value="1" class="mr-2" {{ $checkedUnlisted }}>
          Aset belum terdaftar
        </label>
      </div>

      <div id="asetManual" class="hidden">
        <div class="grid md:grid-cols-3 gap-3">
          <div>
            <label class="block text-xs text-gray-600">Nama/Objek</label>
            <input type="text" name="asset_nama_manual" class="border rounded-lg p-2 w-full"
                   value="{{ old('asset_nama_manual',$ticket->asset_nama_manual) }}">
            @include('partials.field-error', ['field' => 'asset_nama_manual'])
          </div>
          <div>
            <label class="block text-xs text-gray-600">Lokasi</label>
            <input type="text" name="asset_lokasi_manual" class="border rounded-lg p-2 w-full"
                   value="{{ old('asset_lokasi_manual',$ticket->asset_lokasi_manual) }}">
            @include('partials.field-error', ['field' => 'asset_lokasi_manual'])
          </div>
          <div>
            <label class="block text-xs text-gray-600">Vendor/Merk (opsional)</label>
            <input type="text" name="asset_vendor_manual" class="border rounded-lg p-2 w-full"
                   value="{{ old('asset_vendor_manual',$ticket->asset_vendor_manual) }}">
            @include('partials.field-error', ['field' => 'asset_vendor_manual'])
          </div>
        </div>
      </div>

      <div>
        <label class="block text-xs text-gray-600">Judul</label>
        <input type="text" name="judul" class="border rounded-lg p-2 w-full" required
               value="{{ old('judul',$ticket->judul) }}">
        @include('partials.field-error', ['field' => 'judul'])
      </div>
      <div>
        <label class="block text-xs text-gray-600">Deskripsi</label>
        <textarea name="deskripsi" rows="4" class="border rounded-lg p-2 w-full" required>{{ old('deskripsi',$ticket->deskripsi) }}</textarea>
        @include('partials.field-error', ['field' => 'deskripsi'])
      </div>

      <div>
        <label class="block text-xs text-gray-600">Penanggung Jawab (opsional, wajib untuk LAINNYA)</label>
        <select name="assignee_id" class="border rounded-lg p-2 w-full">
          <option value="">— Pilih PJ —</option>
          @foreach($pjs as $u)
            <option value="{{ $u->id }}" @selected(old('assignee_id', $ticket->assignee_id)==$u->id)>{{ $u->name }} ({{ $u->divisi }})</option>
          @endforeach
        </select>
      </div>

      <div class="flex gap-2">
        <button class="px-4 py-2 rounded-lg bg-black text-white text-sm">Simpan Perubahan</button>
        <a href="{{ route('tickets.show',$ticket->id) }}" class="px-4 py-2 rounded-lg border text-sm">Batal</a>
      </div>
    </div>
  </form>
</div>

<script>
  const elKat = document.getElementById('kategori');
  const elUnlisted = document.getElementById('is_unlisted');
  const boxManual = document.getElementById('asetManual');

  function refreshAsetUI() {
    const kat = elKat.value;
    const unlisted = elUnlisted && elUnlisted.checked;
    if (kat === 'LAINNYA' || unlisted) {
      boxManual.classList.remove('hidden');
    } else {
      boxManual.classList.add('hidden');
    }
  }
  elKat.addEventListener('change', refreshAsetUI);
  if (elUnlisted) elUnlisted.addEventListener('change', refreshAsetUI);
  // initial render
  refreshAsetUI();
</script>
@endsection
