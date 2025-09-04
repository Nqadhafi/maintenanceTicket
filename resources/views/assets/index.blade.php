@extends('layouts.app')

@section('content')
<div class="card">
  {{-- Header + CTA --}}
  <div class="bar mb-3">
    <h2 class="text-lg font-semibold">Aset</h2>
    <div class="flex items-center gap-2">
      <button id="btnExpandAll" type="button" class="btn btn-outline text-sm">Buka semua</button>
      <button id="btnCollapseAll" type="button" class="btn btn-outline text-sm">Tutup semua</button>
      <a href="{{ route('assets.create') }}" class="btn btn-brand text-sm">Tambah Aset</a>
    </div>
  </div>

  {{-- Filter --}}
  <form method="get" class="grid gap-2 md:grid-cols-5">
    <input class="field" name="q" placeholder="Cari nama/kode…" value="{{ $filters['q'] }}">
    <select class="field" name="category_id">
      <option value="">Kategori (semua)</option>
      @foreach ($categories as $c)
        <option value="{{ $c->id }}" @selected($filters['category_id']==$c->id)>{{ $c->nama }}</option>
      @endforeach
    </select>
    <select class="field" name="location_id">
      <option value="">Lokasi (semua)</option>
      @foreach ($locations as $l)
        <option value="{{ $l->id }}" @selected($filters['location_id']==$l->id)>{{ $l->nama }}</option>
      @endforeach
    </select>
    <select class="field" name="status">
      <option value="">Status (semua)</option>
      @foreach (['AKTIF','RUSAK','SCRAP'] as $s)
        <option value="{{ $s }}" @selected($filters['status']===$s)>{{ $s }}</option>
      @endforeach
    </select>
    <div class="flex gap-2">
      <button class="btn btn-primary btn-block md:btn">Terapkan</button>
      @if($filters['q'] || $filters['category_id'] || $filters['location_id'] || $filters['status'])
        <a href="{{ route('assets.index') }}" class="btn btn-outline btn-block md:btn">Reset</a>
      @endif
    </div>
  </form>

  {{-- Grouped by Kategori (collapsible, minimized by default) --}}
  @php
    $grouped = $assets->groupBy(fn($a) => optional($a->category)->nama ?: 'Tanpa Kategori')->sortKeys();
  @endphp

  <div class="mt-4">
    @forelse($grouped as $catName => $rows)
      <details class="mb-3 rounded-xl border overflow-hidden" data-acc>
        <summary class="cursor-pointer select-none bg-gray-50 px-3 py-2">
          <div class="bar">
            <div class="flex items-center gap-2">
              <span class="font-medium">{{ $catName }}</span>
              <span class="chip tone-muted">{{ $rows->count() }} aset</span>
            </div>
            <div class="text-xs text-gray-500">Ketuk untuk lihat</div>
          </div>
        </summary>

        <div class="p-3">
          {{-- Desktop table per kategori --}}
          <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-sm table">
              <thead>
                <tr class="text-left">
                  <th class="py-2 pr-2">Kode</th>
                  <th class="py-2 pr-2">Nama</th>
                  <th class="py-2 pr-2">Lokasi</th>
                  <th class="py-2 pr-2">Vendor</th>
                  <th class="py-2 pr-2">Status</th>
                  <th class="py-2 pr-2 w-40">Aksi</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($rows as $a)
                  <tr class="hover:bg-gray-50">
                    <td class="py-2 pr-2 font-medium">
                      <button
                        type="button"
                        class="underline js-asset-open"
                        data-id="{{ $a->id }}"
                        data-url="{{ route('assets.peek', $a->id) }}"
                        aria-label="Buka ringkas aset {{ $a->kode_aset }}"
                      >{{ $a->kode_aset }}</button>
                    </td>
                    <td class="py-2 pr-2">{{ $a->nama }}</td>
                    <td class="py-2 pr-2">{{ optional($a->location)->nama }}</td>
                    <td class="py-2 pr-2">{{ optional($a->vendor)->nama }}</td>
                    <td class="py-2 pr-2">
                      <span class="chip {{ $a->status === 'AKTIF' ? 'tone-ok' : ($a->status === 'RUSAK' ? 'tone-warn' : 'tone-muted') }}">
                        {{ $a->status }}
                      </span>
                    </td>
                    <td class="py-2 pr-2">
                      <div class="flex items-center gap-2">
                        <a href="{{ route('assets.edit',$a->id) }}" class="btn btn-outline text-sm">Edit</a>
                        <form method="post" action="{{ route('assets.destroy',$a->id) }}"
                              onsubmit="return confirm('Hapus aset ini?')">
                          @csrf @method('delete')
                          <button class="btn btn-danger text-sm">Hapus</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          {{-- Mobile cards per kategori --}}
          <div class="md:hidden stack mt-2">
            @foreach ($rows as $a)
              @php
                $tone = $a->status === 'AKTIF' ? 'ok' : ($a->status === 'RUSAK' ? 'warn' : 'info');
              @endphp
              <div class="p-3 border rounded-xl row-accent {{ $tone }}">
                <div class="bar">
                  <button
                    type="button"
                    class="font-semibold underline truncate js-asset-open text-left"
                    data-id="{{ $a->id }}"
                    data-url="{{ route('assets.peek', $a->id) }}"
                    aria-label="Buka ringkas aset {{ $a->kode_aset }}"
                  >{{ $a->kode_aset }}</button>
                  <span class="chip {{ $a->status === 'AKTIF' ? 'tone-ok' : ($a->status === 'RUSAK' ? 'tone-warn' : 'tone-muted') }}">
                    {{ $a->status }}
                  </span>
                </div>
                <div class="mt-1 text-sm text-gray-700">{{ $a->nama }}</div>
                <div class="mt-1 text-xs text-gray-500 flex flex-wrap gap-1">
                  @if(optional($a->location)->nama)
                    <span class="chip tone-muted">Lokasi: {{ optional($a->location)->nama }}</span>
                  @endif
                  @if(optional($a->vendor)->nama)
                    <span class="chip tone-muted">Vendor: {{ optional($a->vendor)->nama }}</span>
                  @endif
                </div>
                <div class="mt-2 flex items-center gap-2">
                  <a href="{{ route('assets.edit',$a->id) }}" class="btn btn-outline btn-block">Edit</a>
                  <form method="post" action="{{ route('assets.destroy',$a->id) }}" class="flex-1"
                        onsubmit="return confirm('Hapus aset ini?')">
                    @csrf @method('delete')
                    <button class="btn btn-danger btn-block">Hapus</button>
                  </form>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </details>
    @empty
      <div class="text-sm text-gray-500">Tidak ada data.</div>
    @endforelse
  </div>

  {{-- Pagination --}}
  <div class="mt-4">{{ $assets->links() }}</div>
</div>

{{-- Expand/Collapse all (default: semua tertutup) --}}
<script>
  (function(){
    const accs = Array.from(document.querySelectorAll('[data-acc]'));
    accs.forEach(d => d.open = false);
    document.getElementById('btnExpandAll')?.addEventListener('click', () => accs.forEach(d => d.open = true));
    document.getElementById('btnCollapseAll')?.addEventListener('click', () => accs.forEach(d => d.open = false));
  })();
</script>

{{-- ======================= ASSET MODAL (centered & classy) ======================= --}}
<div id="assetModal" class="fixed inset-0 hidden z-50">
  {{-- Backdrop --}}
  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" data-close></div>

  {{-- Modal container (center) --}}
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl transform rounded-2xl shadow-2xl bg-white ring-1 ring-black/5 transition-all scale-95 opacity-0" id="assetModalCard" role="dialog" aria-modal="true" aria-labelledby="am_title" aria-describedby="am_desc">
      {{-- Header --}}
      <div class="rounded-t-2xl px-4 py-3" style="background:linear-gradient(135deg,#0ea5e9,#2563eb);color:#fff;">
        <div class="bar">
          <div class="min-w-0">
            <div id="am_kode" class="text-xs/5 opacity-90">#ASSET</div>
            <h3 id="am_title" class="font-semibold truncate">Nama Aset</h3>
          </div>
          <button class="appbar-btn" data-close aria-label="Tutup">✕</button>
        </div>
      </div>

      {{-- Body --}}
      <div class="p-4">
        {{-- Meta grid --}}
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
          <div>
            <div class="text-gray-500 text-xs">Kategori</div>
            <div id="am_cat" class="font-medium">—</div>
          </div>
          <div>
            <div class="text-gray-500 text-xs">Lokasi</div>
            <div id="am_loc" class="font-medium">—</div>
          </div>
          <div>
            <div class="text-gray-500 text-xs">Vendor</div>
            <div id="am_vendor" class="font-medium">—</div>
          </div>
          <div>
            <div class="text-gray-500 text-xs">Status</div>
            <div id="am_status" class="chip tone-muted inline-block">—</div>
          </div>
          <div>
            <div class="text-gray-500 text-xs">Tgl Beli</div>
            <div id="am_tgl" class="font-medium">—</div>
          </div>
        </div>

        <div class="border-t my-3"></div>

        {{-- Riwayat --}}
        <div class="grid md:grid-cols-2 gap-3">
          <div>
            <div class="text-sm font-medium mb-2">Riwayat Tiket</div>
            <div id="am_tickets" class="stack"></div>
          </div>
          <div>
            <div class="text-sm font-medium mb-2">Riwayat Maintenance / WO</div>
            <div id="am_wos" class="stack"></div>
          </div>
        </div>
      </div>

      {{-- Footer --}}
      <div class="px-4 pb-4 flex items-center justify-end gap-2">
        <a id="am_print" href="#" target="_blank" class="btn btn-outline">Cetak</a>
        <button class="btn btn-brand" data-close>Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const modal = document.getElementById('assetModal');
  const card  = document.getElementById('assetModalCard');
  const closeEls = modal.querySelectorAll('[data-close]');

  const els = {
    kode:   document.getElementById('am_kode'),
    title:  document.getElementById('am_title'),
    cat:    document.getElementById('am_cat'),
    loc:    document.getElementById('am_loc'),
    vendor: document.getElementById('am_vendor'),
    status: document.getElementById('am_status'),
    tgl:    document.getElementById('am_tgl'),
    tickets:document.getElementById('am_tickets'),
    wos:    document.getElementById('am_wos'),
    print:  document.getElementById('am_print'),
  };

  function openModal(){
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden','false');
    // animasi halus
    requestAnimationFrame(()=>{
      card.style.opacity = '1';
      card.style.transform = 'scale(1)';
    });
    document.body.style.overflow = 'hidden';
  }
  function closeModal(){
    // animasi keluar
    card.style.opacity = '0';
    card.style.transform = 'scale(0.95)';
    setTimeout(()=>{
      modal.classList.add('hidden');
      modal.setAttribute('aria-hidden','true');
      document.body.style.overflow = '';
    }, 120);
  }

  closeEls.forEach(el => el.addEventListener('click', closeModal));
  modal.addEventListener('click', (e) => {
    if (e.target.matches('[data-close], .backdrop')) closeModal();
  });
  window.addEventListener('keydown', (e) => {
    if (!modal.classList.contains('hidden') && e.key === 'Escape') closeModal();
  });

  function chipTone(status){
    if(status === 'AKTIF') return 'chip tone-ok';
    if(status === 'RUSAK') return 'chip tone-warn';
    return 'chip tone-muted';
  }

  function itemRow(left, right, url){
    const wrap = document.createElement('div');
    wrap.className = 'border rounded-lg p-2 hover:bg-gray-50 transition';
    wrap.innerHTML = `
      <div class="bar">
        <div class="text-sm">
          <div class="font-medium truncate">${left}</div>
          <div class="text-xs text-gray-500 truncate">${right || ''}</div>
        </div>
        ${url ? `<a href="${url}" class="btn btn-outline text-xs">Buka</a>` : ''}
      </div>`;
    return wrap;
  }

  async function loadAsset(url, id){
    try{
      const res = await fetch(url, { headers: { 'Accept':'application/json' }});
      if(!res.ok) throw new Error('Gagal memuat data');
      const json = await res.json();

      const a = json.asset || {};
      els.kode.textContent  = a.kode_aset ? '#'+a.kode_aset : '#ASSET';
      els.title.textContent = a.nama || '—';
      els.cat.textContent   = a.kategori || '—';
      els.loc.textContent   = a.lokasi || '—';
      els.vendor.textContent= a.vendor || '—';
      els.status.className  = chipTone(a.status);
      els.status.textContent= a.status || '—';
      els.tgl.textContent   = a.tanggal_beli || '—';

      // tickets
      els.tickets.innerHTML = '';
      (json.tickets || []).forEach(t=>{
        els.tickets.appendChild(itemRow(`[${t.kode}] ${t.judul}`, `${t.status} • ${t.urgensi} • ${t.created_at}`, t.url));
      });
      if((json.tickets || []).length === 0){
        els.tickets.innerHTML = `<div class="text-xs text-gray-500">Belum ada tiket.</div>`;
      }

      // work orders
      els.wos.innerHTML = '';
      (json.work_orders || []).forEach(w=>{
        els.wos.appendChild(itemRow(`[${w.kode}] ${w.judul}`, `${w.status} • ${w.created_at}`, w.url));
      });
      if((json.work_orders || []).length === 0){
        els.wos.innerHTML = `<div class="text-xs text-gray-500">Belum ada WO.</div>`;
      }

      // print (gunakan halaman cetak khusus jika ada)
      els.print.href = "{{ url('/assets') }}/"+id+"/print";

      openModal();
    }catch(e){
      console.error(e);
      alert('Gagal memuat ringkasan aset.');
    }
  }

  // init anim state
  card.style.opacity = '0';
  card.style.transform = 'scale(0.95)';

  document.addEventListener('click', (e)=>{
    const btn = e.target.closest('.js-asset-open');
    if(!btn) return;
    const url = btn.dataset.url;
    const id  = btn.dataset.id;
    if(url && id) loadAsset(url, id);
  });
})();
</script>
@endsection
