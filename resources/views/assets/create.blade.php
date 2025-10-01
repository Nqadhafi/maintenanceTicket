@extends('layouts.app')

@section('content')
<div class="card">
  {{-- Header + CTA --}}
  <div class="bar mb-3">
    <h2 class="text-lg font-semibold">Tambah Aset</h2>
    <a href="{{ route('assets.index') }}" class="btn btn-outline text-sm">Kembali</a>
  </div>

  <form method="post" action="{{ route('assets.store') }}" class="grid gap-3" id="assetForm">
    @csrf

    {{-- Tips ringkas --}}
    <div class="tone-info card mb-1">
      <div class="text-sm">Isi data aset di bawah. Spesifikasi wajib format JSON—akan divalidasi otomatis.</div>
    </div>



    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-xs text-gray-600">Kode Aset</label>
        <input name="kode_aset" class="field" required value="{{ old('kode_aset') }}" placeholder="IT-2025-001">
        @include('partials.field-error', ['field' => 'kode_aset'])
      </div>

      <div>
        <label class="block text-xs text-gray-600">Nama</label>
        <input name="nama" class="field" required value="{{ old('nama') }}" placeholder="PC Front Office 01">
        @include('partials.field-error', ['field' => 'nama'])
      </div>

<div>
  <label for="asset_category_id" class="block text-xs text-gray-600">Kategori</label>
  <select id="asset_category_id" name="asset_category_id" class="field" required>
    @foreach($categories as $c)
      <option value="{{ $c->id }}"
        {{ (string) old('asset_category_id') === (string) $c->id ? 'selected' : '' }}>
        {{ $c->nama }}
      </option>
    @endforeach
  </select>
  @include('partials.field-error', ['field' => 'asset_category_id'])
</div>

<div>
  <label for="location_id" class="block text-xs text-gray-600">Lokasi (opsional)</label>
  <select id="location_id" name="location_id" class="field">
    <option value="" {{ in_array(old('location_id'), [null, ''], true) ? 'selected' : '' }}>—</option>
    @foreach($locations as $l)
      <option value="{{ $l->id }}"
        {{ (string) old('location_id') === (string) $l->id ? 'selected' : '' }}>
        {{ $l->nama }}
      </option>
    @endforeach
  </select>
  @include('partials.field-error', ['field' => 'location_id'])
</div>

<div>
  <label for="vendor_id" class="block text-xs text-gray-600">Vendor (opsional)</label>
  <select id="vendor_id" name="vendor_id" class="field">
    <option value="" {{ in_array(old('vendor_id'), [null, ''], true) ? 'selected' : '' }}>—</option>
    @foreach($vendors as $v)
      <option value="{{ $v->id }}"
        {{ (string) old('vendor_id') === (string) $v->id ? 'selected' : '' }}>
        {{ $v->nama }}
      </option>
    @endforeach
  </select>
  @include('partials.field-error', ['field' => 'vendor_id'])
</div>


      <div>
        <label class="block text-xs text-gray-600">Status</label>
        @php $defaultStatus = old('status','AKTIF'); @endphp
        <select name="status" class="field">
          @foreach (['AKTIF','RUSAK','SCRAP'] as $s)
            <option value="{{ $s }}" @selected($defaultStatus===$s)>{{ $s }}</option>
          @endforeach
        </select>
        @include('partials.field-error', ['field' => 'status'])
      </div>

      <div>
        <label class="block text-xs text-gray-600">Tanggal Beli</label>
        <input type="date" name="tanggal_beli" class="field" value="{{ old('tanggal_beli') }}">
        @include('partials.field-error', ['field' => 'tanggal_beli'])
      </div>

      {{-- Spesifikasi JSON + indikator validasi + pratinjau --}}
      <div class="md:col-span-2">
            {{-- Quick template spesifikasi --}}
    <div class="grid md:grid-cols-3 gap-3 mb-1">
      <div class="md:col-span-3">
        <label class="block text-xs text-gray-600">Template cepat (opsional)</label>
        <div class="flex items-center gap-2">
          <select id="tplSelect" class="field w-full md:w-auto">
            <option value="">— Pilih template —</option>
            <option value="IT">Perangkat IT (PC/Laptop)</option>
            <option value="PRODUKSI">Mesin Produksi</option>
            <option value="GA">Perlengkapan GA</option>
            <option value="LAINNYA">Umum / Lainnya</option>
          </select>
          <button id="tplApply" class="btn btn-outline text-sm" type="button">Isi ke Spesifikasi</button>
        </div>
      </div>
    </div>
        <div class="bar mb-1">
          <label class="block text-xs text-gray-600">Spesifikasi (JSON)</label>
          <div id="jsonBadge" class="chip tone-muted text-xs">Belum dicek</div>
        </div>
        <textarea id="specField" name="spesifikasi" rows="8" class="field font-mono"
                  placeholder='{"tipe":"PC","cpu":"i5","ram_gb":8}'>{{ old('spesifikasi') }}</textarea>
        @include('partials.field-error', ['field' => 'spesifikasi'])

        <details class="mt-2">
          <summary class="cursor-pointer text-sm text-gray-600">Pratinjau spesifikasi</summary>
          <div id="specPreview" class="mt-2 text-sm text-gray-700 grid md:grid-cols-2 gap-2"></div>
        </details>
      </div>
    </div>

    <div class="flex gap-2">
      <button class="btn btn-primary">Simpan</button>
      <a href="{{ route('assets.index') }}" class="btn btn-outline">Batal</a>
    </div>
  </form>
</div>

{{-- ===== JSON helper & template filler ===== --}}
<script>
(function(){
  const specField = document.getElementById('specField');
  const badge = document.getElementById('jsonBadge');
  const preview = document.getElementById('specPreview');
  const tplSelect = document.getElementById('tplSelect');
  const tplApply = document.getElementById('tplApply');
  const form = document.getElementById('assetForm');

  const TEMPLATES = {
    IT: {
      tipe: "PC/Laptop", cpu: "Intel i5 / Ryzen 5", ram_gb: 8, storage: "SSD 256GB",
      os: "Windows 11 Pro", tahun: new Date().getFullYear()
    },
    PRODUKSI: {
      tipe: "Mesin Produksi", model: "—", serial: "—", kapasitas: "—",
      daya: "—", tahun: new Date().getFullYear(), catatan: "Jadwal preventive per 3 bulan"
    },
    GA: {
      tipe: "Perlengkapan GA", merk: "—", model: "—", kategori: "—", kondisi: "Baik"
    },
    LAINNYA: {
      tipe: "Lainnya", deskripsi: "—", catatan: "—"
    }
  };

  function renderPreview(obj){
    preview.innerHTML = '';
    if (!obj || typeof obj !== 'object') return;
    Object.keys(obj).forEach(k => {
      const v = obj[k];
      const wrap = document.createElement('div');
      wrap.className = 'card tone-muted';
      wrap.innerHTML = `<div class="text-xs opacity-70">${k}</div><div class="font-medium break-words">${escapeHtml(String(typeof v === 'object' ? JSON.stringify(v) : v))}</div>`;
      preview.appendChild(wrap);
    });
  }

  function escapeHtml(s){
    return s.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  }

  function validateJSON(){
    const val = specField.value.trim();
    if (!val) {
      badge.textContent = 'Wajib diisi (JSON)';
      badge.className = 'chip tone-warn';
      preview.innerHTML = '';
      return false;
    }
    try {
      const obj = JSON.parse(val);
      badge.textContent = 'JSON valid';
      badge.className = 'chip tone-ok';
      renderPreview(obj);
      return true;
    } catch(e){
      badge.textContent = 'JSON tidak valid';
      badge.className = 'chip tone-danger';
      preview.innerHTML = '';
      return false;
    }
  }

  specField.addEventListener('input', validateJSON);
  window.addEventListener('load', validateJSON);

  tplApply?.addEventListener('click', () => {
    const key = tplSelect.value;
    if (!key) return;
    const obj = TEMPLATES[key] || TEMPLATES.LAINNYA;
    // Pretty print 2-spaces
    specField.value = JSON.stringify(obj, null, 2);
    validateJSON();
    specField.focus();
  });

  // Hard-guard on submit
  form.addEventListener('submit', (e) => {
    if (!validateJSON()) {
      e.preventDefault();
      specField.focus();
      alert('Spesifikasi harus berupa JSON yang valid.');
    }
  });
})();
</script>
@endsection
