@extends('layouts.app')

@section('content')
<div class="card">
  {{-- Header + CTA --}}
  <div class="bar mb-3">
    <h2 class="text-lg font-semibold">Edit Aset</h2>
    <div class="flex items-center gap-2">
      <a href="{{ route('assets.index') }}" class="btn btn-outline text-sm">Kembali</a>
    </div>
  </div>

  <form method="post" action="{{ route('assets.update',$asset->id) }}" class="grid gap-3">
    @csrf @method('put')

    {{-- Identitas --}}
    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-xs text-gray-600">Kode Aset</label>
        <input class="field bg-gray-100" value="{{ $asset->kode_aset }}" disabled>
        <div class="hint mt-1">Kode aset tidak dapat diubah.</div>
      </div>

      <div>
        <label class="block text-xs text-gray-600">Nama</label>
        <input name="nama" class="field" value="{{ old('nama',$asset->nama) }}" required>
        @include('partials.field-error', ['field' => 'nama'])
      </div>

<div>
  <label for="asset_category_id" class="block text-xs text-gray-600">Kategori</label>
  <select id="asset_category_id" name="asset_category_id" class="field" required>
    @foreach($categories as $c)
      <option value="{{ $c->id }}"
        {{ (string) old('asset_category_id', $asset->asset_category_id) === (string) $c->id ? 'selected' : '' }}>
        {{ $c->nama }}
      </option>
    @endforeach
  </select>
  @include('partials.field-error', ['field' => 'asset_category_id'])
</div>

<div>
  <label for="location_id" class="block text-xs text-gray-600">Lokasi</label>
  <select id="location_id" name="location_id" class="field">
    <option value=""
      {{ in_array(old('location_id', $asset->location_id), [null, ''], true) ? 'selected' : '' }}>
      —
    </option>
    @foreach($locations as $l)
      <option value="{{ $l->id }}"
        {{ (string) old('location_id', $asset->location_id) === (string) $l->id ? 'selected' : '' }}>
        {{ $l->nama }}
      </option>
    @endforeach
  </select>
  @include('partials.field-error', ['field' => 'location_id'])
</div>

<div>
  <label for="vendor_id" class="block text-xs text-gray-600">Vendor</label>
  <select id="vendor_id" name="vendor_id" class="field">
    <option value=""
      {{ in_array(old('vendor_id', $asset->vendor_id), [null, ''], true) ? 'selected' : '' }}>
      —
    </option>
    @foreach($vendors as $v)
      <option value="{{ $v->id }}"
        {{ (string) old('vendor_id', $asset->vendor_id) === (string) $v->id ? 'selected' : '' }}>
        {{ $v->nama }}
      </option>
    @endforeach
  </select>
  @include('partials.field-error', ['field' => 'vendor_id'])
</div>


<div>
  <label for="status" class="block text-xs text-gray-600">Status</label>
  <select id="status" name="status" class="field">
    @foreach (['AKTIF','RUSAK','SCRAP'] as $s)
      <option value="{{ $s }}"
        {{ (string) old('status', $asset->status ?? '') === (string) $s ? 'selected' : '' }}>
        {{ $s }}
      </option>
    @endforeach
  </select>
  @include('partials.field-error', ['field' => 'status'])
</div>


      <div>
        <label class="block text-xs text-gray-600">Tanggal Beli</label>
        <input type="date" name="tanggal_beli" class="field"
               value="{{ old('tanggal_beli', $asset->tanggal_beli ? $asset->tanggal_beli->format('Y-m-d') : '') }}">
        @include('partials.field-error', ['field' => 'tanggal_beli'])
      </div>
    </div>

    {{-- Spesifikasi JSON --}}
    <div class="grid gap-2">
      <div class="bar">
        <label class="block text-xs text-gray-600">Spesifikasi (JSON)</label>
        <span id="jsonStatus" class="text-xs chip tone-muted">Belum dicek</span>
      </div>
      <textarea id="spesifikasiInput" name="spesifikasi" rows="8" class="field" required
        placeholder='{"tipe":"PC","cpu":"i5","ram_gb":8}'>{{ old('spesifikasi', json_encode($asset->spesifikasi, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) }}</textarea>
      @include('partials.field-error', ['field' => 'spesifikasi'])

      <details class="mt-1">
        <summary class="text-xs underline cursor-pointer">Tips format JSON</summary>
        <div class="hint mt-1">
          Gunakan pasangan <em>key</em> dan <em>value</em>. Contoh:
          <code>{"tipe":"PC","cpu":"i5","ram_gb":8,"os":"Windows 11"}</code>
        </div>
      </details>
    </div>

    {{-- Actions --}}
    <div class="flex flex-col md:flex-row gap-2 mt-2">
      <button class="btn btn-primary">Simpan</button>
      <a href="{{ route('assets.index') }}" class="btn btn-outline">Batal</a>
    </div>
  </form>
</div>

{{-- Mini JSON validator (client-side) --}}
<script>
  (function(){
    const ta = document.getElementById('spesifikasiInput');
    const badge = document.getElementById('jsonStatus');
    if(!ta || !badge) return;

    function setBadge(ok, msg){
      badge.textContent = msg;
      badge.className = 'text-xs chip ' + (ok ? 'tone-ok' : 'tone-danger');
    }
    function check(){
      const v = ta.value.trim();
      if(!v){ setBadge(false,'Kosong'); return; }
      try {
        JSON.parse(v);
        setBadge(true, 'JSON valid');
      } catch(e){
        setBadge(false, 'JSON tidak valid');
      }
    }
    ta.addEventListener('input', check);
    window.addEventListener('load', check);
  })();
</script>
@endsection
