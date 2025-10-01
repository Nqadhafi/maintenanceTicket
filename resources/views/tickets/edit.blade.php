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
      {{-- Kategori & Urgensi --}}
      <div class="grid md:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs text-gray-600">Kategori</label>
          <select name="kategori" id="kategori" class="border rounded-lg p-2 w-full">
            @foreach($kategori as $k)
              <option value="{{ $k }}" {{ old('kategori', $ticket->kategori) == $k ? 'selected' : '' }}>{{ $k }}</option>
            @endforeach
          </select>
          @include('partials.field-error', ['field' => 'kategori'])
        </div>
        <div>
          <label class="block text-xs text-gray-600">Urgensi</label>
          <select name="urgensi" class="border rounded-lg p-2 w-full">
            @foreach($urgensi as $u)
              <option value="{{ $u }}" {{ old('urgensi', $ticket->urgensi) == $u ? 'selected' : '' }}>{{ $u }}</option>
            @endforeach
          </select>
          @include('partials.field-error', ['field' => 'urgensi'])
        </div>
      </div>

      {{-- ======== Aset Terdaftar (with inner search) ======== --}}
      <div id="asetTerdaftar">
        <label class="block text-xs text-gray-600 mb-1">Aset Terdaftar</label>

        @php
          $selectedAssetId = old('asset_id', $ticket->asset_id);
          $selectedFromList = $selectedAssetId ? $assets->firstWhere('id', (int)$selectedAssetId) : null;
          
          // Tentukan teks yang akan ditampilkan
          if (old('asset_id') && old('asset_label')) {
            // Jika ada old data dan label, gunakan old data
            $selectedAssetText = old('asset_label');
          } elseif ($ticket->asset) {
            // Jika tidak ada old data tapi ada asset di ticket
            $selectedAssetText = $ticket->asset->kode_aset.' — '.$ticket->asset->nama;
          } else {
            // Default
            $selectedAssetText = '— Pilih aset —';
          }
        @endphp

        <div id="assetPicker" class="relative" data-initial-id="{{ $selectedAssetId }}">
          <input type="hidden" name="asset_id" id="asset_id" value="{{ $selectedAssetId }}">
          <button type="button" id="assetBtn" class="field w-full text-left flex items-center justify-between">
            <span id="assetBtnText" class="truncate">{{ $selectedAssetText }}</span>
            <svg class="ml-2 w-4 h-4 opacity-70" viewBox="0 0 20 20" fill="currentColor"><path d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"/></svg>
          </button>

          {{-- Panel --}}
          <div id="assetPanel" class="hidden absolute z-20 mt-1 w-full bg-white border rounded-xl shadow">
            <div class="p-2 border-b">
              <input id="assetSearch" type="text" class="field w-full" placeholder="Cari aset (kode/nama)…" autocomplete="off">
              <div class="text-xs text-gray-500 mt-1">Filter berdasar kategori terpilih.</div>
            </div>
            <div id="assetList" class="max-h-64 overflow-auto">
              <div class="p-3 text-sm text-gray-500">Ketik untuk mencari…</div>
            </div>
            <div class="p-2 border-t flex items-center justify-between text-xs text-gray-500">
              <button type="button" id="assetClear" class="underline">Kosongkan pilihan</button>
              <span id="assetHint">0 hasil</span>
            </div>
          </div>
        </div>

        @include('partials.field-error', ['field' => 'asset_id'])

        {{-- Unlisted toggle --}}
        @php
          $checkedUnlisted = old('is_asset_unlisted') !== null ? (bool)old('is_asset_unlisted') : (bool)$ticket->is_asset_unlisted;
        @endphp
        <label class="inline-flex items-center mt-2 text-sm">
          <input type="checkbox" id="is_unlisted" name="is_asset_unlisted" value="1" class="mr-2" {{ $checkedUnlisted ? 'checked' : '' }}>
          Aset belum terdaftar
        </label>
      </div>

      {{-- ======== Aset Manual ======== --}}
      <div id="asetManual" class="hidden">
        <div class="grid md:grid-cols-3 gap-3">
          <div>
            <label class="block text-xs text-gray-600">Nama/Objek</label>
            <input type="text" name="asset_nama_manual" class="border rounded-lg p-2 w-full"
                   value="{{ old('asset_nama_manual', $ticket->asset_nama_manual) }}">
            @include('partials.field-error', ['field' => 'asset_nama_manual'])
          </div>
          <div>
            <label class="block text-xs text-gray-600">Lokasi</label>
            <input type="text" name="asset_lokasi_manual" class="border rounded-lg p-2 w-full"
                   value="{{ old('asset_lokasi_manual', $ticket->asset_lokasi_manual) }}">
            @include('partials.field-error', ['field' => 'asset_lokasi_manual'])
          </div>
          <div>
            <label class="block text-xs text-gray-600">Vendor/Merk (opsional)</label>
            <input type="text" name="asset_vendor_manual" class="border rounded-lg p-2 w-full"
                   value="{{ old('asset_vendor_manual', $ticket->asset_vendor_manual) }}">
            @include('partials.field-error', ['field' => 'asset_vendor_manual'])
          </div>
        </div>
      </div>

      {{-- Judul & Deskripsi --}}
      <div>
        <label class="block text-xs text-gray-600">Judul</label>
        <input type="text" name="judul" class="border rounded-lg p-2 w-full" required
               value="{{ old('judul', $ticket->judul) }}">
        @include('partials.field-error', ['field' => 'judul'])
      </div>
      <div>
        <label class="block text-xs text-gray-600">Deskripsi</label>
        <textarea name="deskripsi" rows="4" class="border rounded-lg p-2 w-full" required>{{ old('deskripsi', $ticket->deskripsi) }}</textarea>
        @include('partials.field-error', ['field' => 'deskripsi'])
      </div>

      {{-- PJ --}}
<div>
  <label for="assignee_id" class="block text-xs text-gray-600">
    Penanggung Jawab <span class="text-gray-400">(opsional, wajib untuk LAINNYA)</span>
  </label>

  <select id="assignee_id" name="assignee_id" class="field w-full">
    @php $selectedAssignee = (string)old('assignee_id', $ticket->assignee_id ?? ''); @endphp

    <option value="" {{ $selectedAssignee === '' ? 'selected' : '' }}>
      — Pilih PJ —
    </option>

    @foreach($pjs as $u)
      <option value="{{ $u->id }}"
        {{ $selectedAssignee === (string)$u->id ? 'selected' : '' }}>
        {{ $u->name }} ({{ $u->divisi }})
      </option>
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

{{-- ===== Script: Aset picker (inner search) + toggle manual/unlisted + preload OLD ===== --}}
<script>
(function(){
  const elKat      = document.getElementById('kategori');
  const elUnlisted = document.getElementById('is_unlisted');
  const boxManual  = document.getElementById('asetManual');
  const picker     = document.getElementById('assetPicker');
  const btn        = document.getElementById('assetBtn');
  const btnText    = document.getElementById('assetBtnText');
  const panel      = document.getElementById('assetPanel');
  const search     = document.getElementById('assetSearch');
  const list       = document.getElementById('assetList');
  const hiddenId   = document.getElementById('asset_id');
  const clearBtn   = document.getElementById('assetClear');
  const hint       = document.getElementById('assetHint');

  const API = "{{ route('assets.lookup') }}"; // supports: ?kategori=, ?q=, ?limit=, ?id=

  function show(el){ el.classList.remove('hidden'); }
  function hide(el){ el.classList.add('hidden'); }
  function isLainnya(){ return (elKat?.value || '') === 'LAINNYA'; }
  function isUnlisted(){ return elUnlisted && elUnlisted.checked; }

  function refreshAsetUI() {
    const needManual = isLainnya() || isUnlisted();
    if (needManual) {
      show(boxManual);
      if (btn) { btn.setAttribute('disabled','disabled'); btn.classList.add('opacity-60','cursor-not-allowed'); }
      setAsset(null, '— Pilih aset —');
    } else {
      hide(boxManual);
      if (btn) { btn.removeAttribute('disabled'); btn.classList.remove('opacity-60','cursor-not-allowed'); }
    }
    closePanel();
  }

  function openPanel(){
    if (!panel || btn.hasAttribute('disabled')) return;
    show(panel);
    picker.setAttribute('data-open','1');
    setTimeout(()=>search?.focus(), 10);
    if ((list.dataset.loaded||'') === '') doSearch('');
  }
  function closePanel(){ if (panel){ hide(panel); picker.removeAttribute('data-open'); } }

  function setAsset(id, label){
    hiddenId.value = id || '';
    btnText.textContent = label || '— Pilih aset —';
  }

  async function doSearch(q){
    const kat = elKat?.value || '';
    list.innerHTML = '<div class="p-3 text-sm text-gray-500">Memuat…</div>';
    try {
      const url = new URL(API, window.location.origin);
      if (kat)  url.searchParams.set('kategori', kat);
      if (q)    url.searchParams.set('q', q);
      url.searchParams.set('limit', '20');

      const res = await fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!res.ok) throw new Error('Request gagal');
      const data = await res.json();
      renderList(data.items || []);
      hint.textContent = ((data.total ?? (data.items||[]).length) + ' hasil');
      list.dataset.loaded = '1';
    } catch {
      list.innerHTML = '<div class="p-3 text-sm text-red-600">Gagal memuat data aset.</div>';
      hint.textContent = '0 hasil';
      list.dataset.loaded = '';
    }
  }

  async function fetchById(id){
    if (!id) return;
    try{
      const url = new URL(API, window.location.origin);
      url.searchParams.set('id', id);
      const res = await fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!res.ok) return;
      const data = await res.json(); // { item: {id,kode_aset,nama} }
      const it = data.item;
      if (it && it.id == id) setAsset(it.id, `${it.kode_aset} — ${it.nama}`);
    } catch {}
  }

  function renderList(items){
    if (!items.length){
      list.innerHTML = '<div class="p-3 text-sm text-gray-500">Tidak ada hasil.</div>';
      return;
    }
    const ul = document.createElement('ul');
    ul.setAttribute('role','listbox');
    items.forEach(it => {
      const li = document.createElement('li');
      li.setAttribute('role','option');
      li.tabIndex = 0;
      li.className = 'px-3 py-2 hover:bg-gray-50 cursor-pointer flex items-center justify-between';
      const label = `${it.kode_aset} — ${it.nama}`;
      li.innerHTML = `<span class="truncate">${label}</span>`;
      li.addEventListener('click', () => { setAsset(it.id, label); closePanel(); });
      li.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); li.click(); }
        if (e.key === 'Escape') closePanel();
      });
      ul.appendChild(li);
    });
    list.innerHTML = '';
    list.appendChild(ul);
  }

  // Events
  btn?.addEventListener('click', () => { picker.hasAttribute('data-open') ? closePanel() : openPanel(); });
  clearBtn?.addEventListener('click', () => { setAsset(null, '— Pilih aset —'); search.value=''; doSearch(''); });
  search?.addEventListener('input', (e) => { doSearch(e.target.value.trim()); });

  document.addEventListener('click', (e) => { if (!picker.contains(e.target)) closePanel(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closePanel(); });

  // Kategori / Unlisted toggles
  elKat?.addEventListener('change', () => { list.dataset.loaded = ''; if (!isLainnya()) setAsset(null, '— Pilih aset —'); refreshAsetUI(); });
  elUnlisted?.addEventListener('change', refreshAsetUI);

  // Initial UI state
  refreshAsetUI();

  // ===== Preload "old('asset_id')" label if needed =====
  const initialId = picker?.dataset.initialId || '';
  if (initialId && btnText.textContent.trim() === 'Memuat…') {
    fetchById(initialId);
  }
})();
</script>
@endsection