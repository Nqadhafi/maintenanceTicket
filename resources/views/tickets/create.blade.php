@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded-xl shadow relative">

  {{-- ===== LOADING OVERLAY (halaman ini saja) ===== --}}
  <div id="loadingOverlay" class="loading-overlay hidden" aria-hidden="true">
    <div class="spinner" role="status" aria-label="Memproses..."></div>
    <div class="loading-text">Memproses...</div>
  </div>

  <form method="post" action="{{ route('tickets.store') }}" class="js-block-on-submit">
    @csrf
    <div class="grid gap-3">

      {{-- Kategori & Urgensi --}}
      <div class="grid md:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs text-gray-600">Kategori</label>
          <select name="kategori" id="kategori" class="field w-full">
            @foreach($kategori as $k)
              <option value="{{ $k }}" {{ (string)old('kategori') === (string)$k ? 'selected' : '' }}>{{ $k }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-xs text-gray-600">Urgensi</label>
          <select name="urgensi" class="field w-full">
            @foreach($urgensi as $u)
              <option value="{{ $u }}" {{ (string)old('urgensi') === (string)$u ? 'selected' : '' }}>{{ $u }}</option>
            @endforeach
          </select>
        </div>
      </div>

      {{-- ========= ASET TERDAFTAR: dropdown dgn input search di dalam ========= --}}
      <div id="asetTerdaftar" class="grid gap-2">
        <div class="bar">
          <label class="block text-xs text-gray-600">Aset Terdaftar</label>
          <label class="inline-flex items-center text-sm">
            <input type="checkbox" id="is_unlisted" name="is_asset_unlisted" value="1" class="mr-2" {{ old('is_asset_unlisted') ? 'checked' : '' }}> Aset belum terdaftar
          </label>
        </div>

        <div id="assetDropdown" class="relative">
          <input type="hidden" name="asset_id" id="asset_id" value="{{ old('asset_id') }}">
          <button type="button" id="asset_btn"
                  class="field w-full text-left flex items-center justify-between">
            <span id="asset_btn_label" class="truncate text-gray-700">
              @php
                $prefill = null;
                if (old('asset_id')) {
                  $prefill = \App\Models\Asset::find(old('asset_id'));
                }
              @endphp
              @if($prefill)
                {{ $prefill->kode_aset }} — {{ $prefill->nama }}
              @else
                — Pilih aset —
              @endif
            </span>
            <span aria-hidden="true">▾</span>
          </button>

          {{-- Panel dropdown --}}
          <div id="asset_panel"
               class="hidden absolute left-0 right-0 mt-1 bg-white border rounded-xl shadow z-50">
            <div class="p-2 border-b">
              <input id="asset_search" type="text" class="field w-full"
                     placeholder="Cari kode/nama aset… (filter by kategori)">
            </div>
            <div id="asset_results" class="max-h-64 overflow-auto p-1" role="listbox" aria-label="Hasil aset"></div>
            <div id="asset_empty" class="hidden p-3 text-sm text-gray-500">Tidak ada hasil.</div>
          </div>
        </div>

        <div class="text-xs text-gray-500">Tips: Buka dropdown, ketik untuk mencari. Hasil otomatis difilter berdasarkan kategori yang dipilih.</div>
      </div>

      {{-- ========= ASET MANUAL ========= --}}
      <div id="asetManual" class="hidden">
        <div class="grid md:grid-cols-3 gap-3">
          <div>
            <label class="block text-xs text-gray-600">Nama/Objek</label>
            <input type="text" name="asset_nama_manual" class="field w-full" value="{{ old('asset_nama_manual') }}">
            @include('partials.field-error', ['field' => 'asset_nama_manual'])
          </div>
          <div>
            <label class="block text-xs text-gray-600">Lokasi</label>
            <input type="text" name="asset_lokasi_manual" class="field w-full" value="{{ old('asset_lokasi_manual') }}">
            @include('partials.field-error', ['field' => 'asset_lokasi_manual'])
          </div>
          <div>
            <label class="block text-xs text-gray-600">Vendor/Merk (opsional)</label>
            <input type="text" name="asset_vendor_manual" class="field w-full" value="{{ old('asset_vendor_manual') }}">
            @include('partials.field-error', ['field' => 'asset_vendor_manual'])
          </div>
        </div>
      </div>

      {{-- Judul & Deskripsi --}}
      <div>
        <label class="block text-xs text-gray-600">Judul</label>
        <input type="text" name="judul" class="field w-full" required value="{{ old('judul') }}">
        @include('partials.field-error', ['field' => 'judul'])
      </div>
      <div>
        <label class="block text-xs text-gray-600">Deskripsi</label>
        <textarea name="deskripsi" rows="4" class="field w-full" required>{{ old('deskripsi') }}</textarea>
      </div>

      {{-- PJ --}}
      <div>
        <label for="assignee_id" class="block text-xs text-gray-600">
          Penanggung Jawab <span class="text-gray-400">(opsional, wajib untuk LAINNYA)</span>
        </label>

        <select id="assignee_id" name="assignee_id" class="field w-full">
          <option value=""
            {{ (old('assignee_id') === null || old('assignee_id') === '') ? 'selected' : '' }}>
            — Pilih PJ —
          </option>
          @foreach($pjs as $u)
            <option value="{{ $u->id }}"
              {{ (string)old('assignee_id') === (string)$u->id ? 'selected' : '' }}>
              {{ $u->name }} ({{ $u->divisi }})
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <button type="submit" class="btn btn-primary js-submit-btn">Buat Tiket</button>
      </div>
    </div>
  </form>
</div>

{{-- ===== JS: Dropdown aset dgn search (inner) + filter by kategori + Loading submit ===== --}}
<script>
(function(){
  // ====== Logic aset dropdown & mode manual ======
  const kat = document.getElementById('kategori');
  const unlisted = document.getElementById('is_unlisted');
  const wrapManual = document.getElementById('asetManual');
  const wrapTerdaftar = document.getElementById('asetTerdaftar');

  const btn = document.getElementById('asset_btn');
  const btnLabel = document.getElementById('asset_btn_label');
  const panel = document.getElementById('asset_panel');
  const search = document.getElementById('asset_search');
  const results = document.getElementById('asset_results');
  const emptyEl = document.getElementById('asset_empty');
  const inpId = document.getElementById('asset_id');

  const endpoint = "{{ route('assets.lookup') }}";

  function setManualMode(on){
    wrapManual.classList.toggle('hidden', !on);
    wrapTerdaftar.classList.toggle('hidden', on);
    if (on) {
      closePanel();
      setAsset('', '— Pilih aset —');
    }
  }

  function refreshMode(){
    const isManual = (unlisted?.checked ?? false) || (kat?.value === 'LAINNYA');
    setManualMode(isManual);
  }

  function openPanel(){
    if (panel.classList.contains('hidden')) {
      panel.classList.remove('hidden');
      search.value = '';
      search.focus();
      fetchAssets(''); // load awal
    }
  }
  function closePanel(){
    panel.classList.add('hidden');
  }

  function setAsset(id, label){
    inpId.value = id || '';
    btnLabel.textContent = label || '— Pilih aset —';
  }

  let debTimer=null;
  function debounce(fn, wait){ return (...a)=>{ clearTimeout(debTimer); debTimer=setTimeout(()=>fn(...a),wait); }; }

  async function fetchAssets(q){
    const K = (kat.value||'').toUpperCase();
    if (!K || K === 'LAINNYA') {
      renderList([]);
      return;
    }
    const url = new URL(endpoint, location.origin);
    url.searchParams.set('kategori', K);
    if (q) url.searchParams.set('q', q);
    url.searchParams.set('limit', '20');
    try{
      const res = await fetch(url.toString(), { headers:{'Accept':'application/json'} });
      if(!res.ok) throw new Error('net');
      const json = await res.json();
      renderList(json.data || []);
    }catch(_e){
      renderList([]);
    }
  }

  function renderList(items){
    results.innerHTML = '';
    if (!items.length){
      emptyEl.classList.remove('hidden');
      return;
    }
    emptyEl.classList.add('hidden');
    const frag = document.createDocumentFragment();
    items.forEach(it=>{
      const b = document.createElement('button');
      b.type='button';
      b.className='w-full text-left px-3 py-2 hover:bg-gray-50 rounded-lg';
      b.setAttribute('role','option');
      b.dataset.id = it.id;
      b.dataset.label = `${it.kode_aset ?? ''} — ${it.nama ?? ''}`;
      b.innerHTML = `
        <div class="text-sm font-medium truncate">${it.kode_aset ?? ''} — ${it.nama ?? ''}</div>
        <div class="text-xs text-gray-500 truncate">${[it.kategori, it.lokasi, it.vendor].filter(Boolean).join(' • ')}</div>
      `;
      frag.appendChild(b);
    });
    results.appendChild(frag);
  }

  // Events dropdown
  btn?.addEventListener('click', ()=> panel.classList.contains('hidden') ? openPanel() : closePanel());
  search?.addEventListener('input', debounce((e)=>fetchAssets(e.target.value.trim()), 250));
  search?.addEventListener('keydown', (e)=>{ if(e.key==='Escape'){ e.preventDefault(); closePanel(); } });
  results?.addEventListener('click', (e)=>{
    const it = e.target.closest('[role="option"]');
    if (!it) return;
    setAsset(it.dataset.id, it.dataset.label);
    closePanel();
  });
  document.addEventListener('click', (e)=>{ if (!e.target.closest('#assetDropdown')) closePanel(); });

  kat?.addEventListener('change', ()=>{
    setAsset('', '— Pilih aset —');
    closePanel();
    refreshMode();
  });
  unlisted?.addEventListener('change', refreshMode);
  refreshMode();

  // ====== Loading submit (tanpa disable field, cegah double submit) ======
  const form = document.querySelector('form.js-block-on-submit');
  const overlay = document.getElementById('loadingOverlay');

  function blockForm(){
    if (form.dataset.submitting === '1') return; // prevent double
    form.dataset.submitting = '1';

    // Disable hanya tombol submit
    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(btn => {
      btn.disabled = true;
      btn.dataset.prevText = btn.innerHTML;
      btn.innerHTML = 'Membuat…';
    });

    // Blok interaksi tanpa disable field agar value tetap terkirim
    form.classList.add('form-blocked');

    // Tampilkan overlay
    if (overlay){
      overlay.classList.remove('hidden');
      overlay.setAttribute('aria-hidden','false');
    }
  }

  form?.addEventListener('submit', ()=>{
    blockForm();
  });

})();
</script>

{{-- ===== Styles: Overlay & Form Block ===== --}}
<style>
  .loading-overlay{
    position:absolute; inset:0; background:rgba(0,0,0,.35);
    display:flex; align-items:center; justify-content:center; flex-direction:column;
    border-radius:12px; z-index:20;
  }
  .loading-overlay.hidden{ display:none; }
  .spinner{
    width:42px; height:42px; border-radius:9999px;
    border:4px solid #fff; border-top-color:transparent;
    animation:spin .8s linear infinite;
  }
  .loading-text{ margin-top:.5rem; color:#fff; font-weight:600 }
  @keyframes spin{ to{ transform:rotate(360deg) } }

  /* Cegah interaksi tanpa mematikan field (value tetap terkirim) */
  .form-blocked{ pointer-events: none; opacity: .85; }
  .form-blocked :where(button,[type="submit"]){ pointer-events:auto; } /* biar hover tombol tetap normal */
</style>
@endsection
