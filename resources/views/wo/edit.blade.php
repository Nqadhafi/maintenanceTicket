@extends('layouts.app')

@section('content')
@php
  use App\Models\WorkOrder;

  /** @var \App\Models\WorkOrder $wo */
  $title = 'Ubah Work Order';
  $typeOpts = [
    WorkOrder::TYPE_CORR => 'Corrective',
    WorkOrder::TYPE_PREV => 'Preventive',
  ];
  $statusOpts = [
    WorkOrder::ST_OPEN       => 'OPEN',
    WorkOrder::ST_INPROGRESS => 'IN_PROGRESS',
    WorkOrder::ST_DONE       => 'DONE',
  ];
@endphp

<div class="space-y-4">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold">{{ $title }}</h1>
      <p class="text-sm text-gray-600">Kode: <span class="font-medium">{{ $wo->kode_wo }}</span></p>
    </div>
    <a href="{{ route('wo.show',$wo->id) }}" class="rounded-xl bg-gray-100 px-3 py-2 text-sm hover:bg-gray-200">Kembali ke Detail</a>
  </div>

  {{-- Error --}}
  @if ($errors->any())
    <div class="bg-rose-50 text-rose-700 rounded-2xl p-3">
      <div class="font-semibold mb-1">Periksa input berikut:</div>
      <ul class="text-sm list-disc pl-5 space-y-0.5">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="post" action="{{ route('wo.update', $wo->id) }}" class="bg-white rounded-2xl shadow p-3 md:p-4" id="woEditForm">
    @csrf @method('PUT')

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      {{-- Jenis WO --}}
      <div>
        <label class="block text-xs text-gray-600 mb-1">Jenis Work Order <span class="text-rose-600">*</span></label>
        <select name="type" class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500" required>
          @foreach($typeOpts as $k=>$v)
            <option value="{{ $k }}" @selected(old('type',$wo->type)===$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>

      {{-- Status --}}
      <div>
        <label class="block text-xs text-gray-600 mb-1">Status <span class="text-rose-600">*</span></label>
        <select name="status" id="status" class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500" required>
          @foreach($statusOpts as $k=>$v)
            <option value="{{ $k }}" @selected(old('status',$wo->status)===$k)>{{ ucfirst(strtolower(str_replace('_',' ', $v))) }}</option>
          @endforeach
        </select>
        <p class="text-[11px] text-gray-500 mt-1">
          Saat diset <b>DONE</b>, pastikan <em>finished_at</em> & <em>duration_minutes</em> sudah valid.
        </p>
      </div>

      {{-- Ticket (opsional) --}}
      <div>
        <label class="block text-xs text-gray-600 mb-1">Ticket (Opsional)</label>
        <select name="ticket_id" id="ticket_id" class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
          <option value="">— Tanpa Ticket —</option>
          @foreach($tickets as $t)
            <option value="{{ $t->id }}" @selected(old('ticket_id', $wo->ticket_id)==$t->id)>
              {{ $t->kode_tiket }} — {{ \Illuminate\Support\Str::limit($t->judul, 40) }}
            </option>
          @endforeach
        </select>
        <div id="ticket_hint" class="text-[11px] text-gray-500 mt-1"></div>
      </div>

      {{-- Aset --}}
      <div>
        <label class="block text-xs text-gray-600 mb-1">Aset <span class="text-rose-600">*</span></label>
        <select name="asset_id" id="asset_id" class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500" required>
          @foreach($assets as $a)
            <option value="{{ $a->id }}" @selected(old('asset_id', $wo->asset_id)==$a->id)>
              {{ $a->kode_aset }} — {{ \Illuminate\Support\Str::limit($a->nama, 40) }}
            </option>
          @endforeach
        </select>
        <div id="asset_hint" class="text-[11px] text-gray-500 mt-1"></div>
      </div>

      {{-- Penanggung Jawab (opsional) --}}
      <div>
        <label class="block text-xs text-gray-600 mb-1">Penanggung Jawab (Opsional)</label>
        <select name="assignee_id" class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
          <option value="">— Belum ditetapkan —</option>
          @foreach($pjs as $u)
            <option value="{{ $u->id }}" @selected(old('assignee_id',$wo->assignee_id)==$u->id)>{{ $u->name }} @if($u->divisi) — {{ $u->divisi }} @endif</option>
          @endforeach
        </select>
      </div>

      {{-- Ringkasan Pekerjaan --}}
      <div class="md:col-span-2">
        <label class="block text-xs text-gray-600 mb-1">Ringkasan Pekerjaan <span class="text-rose-600">*</span></label>
        <textarea name="ringkasan_pekerjaan" rows="3" class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500" required
          placeholder="Deskripsikan singkat pekerjaan yang dilakukan…">{{ old('ringkasan_pekerjaan', $wo->ringkasan_pekerjaan) }}</textarea>
      </div>

      {{-- Waktu Mulai --}}
      <div>
        <label class="block text-xs text-gray-600 mb-1">Mulai (started_at)</label>
        <input type="datetime-local" id="started_at" name="started_at"
               value="{{ old('started_at', optional($wo->started_at)->format('Y-m-d\TH:i')) }}"
               class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
        <p class="text-[11px] text-gray-500 mt-1">Isi saat WO dimulai. Kosongkan jika belum dimulai.</p>
      </div>

      {{-- Waktu Selesai --}}
      <div>
        <label class="block text-xs text-gray-600 mb-1">Selesai (finished_at)</label>
        <input type="datetime-local" id="finished_at" name="finished_at"
               value="{{ old('finished_at', optional($wo->finished_at)->format('Y-m-d\TH:i')) }}"
               class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
        <p class="text-[11px] text-gray-500 mt-1">Harus ≥ waktu mulai.</p>
      </div>

      {{-- Durasi (menit) --}}
      <div>
        <label class="block text-xs text-gray-600 mb-1">Durasi (menit)</label>
        <input type="number" id="duration_minutes" name="duration_minutes" step="1" min="0"
               value="{{ old('duration_minutes', $wo->duration_minutes) }}"
               class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
        <p class="text-[11px] text-gray-500 mt-1">Akan dihitung otomatis dari mulai–selesai (bisa kamu ubah manual).</p>
      </div>

      {{-- Cost Total (read-only info) --}}
      <div>
        <label class="block text-xs text-gray-600 mb-1">Total Biaya (info)</label>
        <input type="text" value="{{ 'Rp ' . number_format((float)$wo->cost_total,0,',','.') }}" disabled
               class="w-full rounded-xl border-gray-200 bg-gray-50 text-gray-600">
        <p class="text-[11px] text-gray-500 mt-1">Dari jumlah item biaya pada halaman detail.</p>
      </div>
    </div>

    {{-- Actions --}}
    <div class="mt-5 flex flex-col md:flex-row gap-2 md:justify-end">
      <a href="{{ route('wo.show',$wo->id) }}" class="rounded-xl bg-gray-100 px-4 py-2 text-center hover:bg-gray-200">Batal</a>
      <button class="rounded-xl bg-gray-900 text-white px-4 py-2 hover:bg-black">Simpan Perubahan</button>
    </div>
  </form>
</div>

{{-- JS: hint dropdown & auto-hitungan durasi --}}
<script>
  (function() {
    const ticketSel = document.getElementById('ticket_id');
    const assetSel  = document.getElementById('asset_id');
    const ticketHint= document.getElementById('ticket_hint');
    const assetHint = document.getElementById('asset_hint');

    function setHint(sel, hintEl, label) {
      if (!sel || !hintEl) return;
      const opt = sel.options[sel.selectedIndex];
      if (!opt || !opt.value) { hintEl.textContent = ''; return; }
      hintEl.textContent = label + ': ' + opt.text.replace(/^\s*|\s*$/g,'');
    }

    ticketSel?.addEventListener('change', () => setHint(ticketSel, ticketHint, 'Terpilih'));
    assetSel?.addEventListener('change',  () => setHint(assetSel,  assetHint,  'Terpilih'));
    setHint(ticketSel, ticketHint, 'Terpilih');
    setHint(assetSel,  assetHint,  'Terpilih');

    // Auto hitung durasi dari started_at dan finished_at
    const startedEl  = document.getElementById('started_at');
    const finishedEl = document.getElementById('finished_at');
    const durEl      = document.getElementById('duration_minutes');

    function parseLocal(dtStr) {
      // "YYYY-MM-DDTHH:mm" -> Date (local)
      if (!dtStr) return null;
      const [d,t] = dtStr.split('T');
      if (!d || !t) return null;
      const [y,m,day] = d.split('-').map(Number);
      const [hh,mm]   = t.split(':').map(Number);
      return new Date(y, (m-1), day, hh, mm, 0);
    }

    function recompute() {
      const s = parseLocal(startedEl?.value);
      const f = parseLocal(finishedEl?.value);
      if (!s || !f) return; // jangan ganggu jika salah satu kosong
      const diffMs = f - s;
      const warnId = 'wo-warn-time';
      let warn = document.getElementById(warnId);

      if (diffMs < 0) {
        // tampilkan warning
        if (!warn) {
          warn = document.createElement('div');
          warn.id = warnId;
          warn.className = 'mt-2 text-[12px] text-rose-700 bg-rose-50 rounded-xl px-3 py-2';
          finishedEl.parentElement.appendChild(warn);
        }
        warn.textContent = 'Waktu selesai tidak boleh sebelum waktu mulai.';
        return;
      } else if (warn) {
        warn.remove();
      }

      const minutes = Math.floor(diffMs / 60000);
      if (durEl) durEl.value = minutes;
    }

    startedEl?.addEventListener('change', recompute);
    finishedEl?.addEventListener('change', recompute);
  })();
</script>
@endsection
