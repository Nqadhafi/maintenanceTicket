@extends('layouts.app')

@section('content')
@php
  use App\Models\WorkOrder;

  $title = 'Buat Work Order';
  $typeOpts = [
    WorkOrder::TYPE_CORR => 'Corrective',
    WorkOrder::TYPE_PREV => 'Preventive',
  ];
@endphp

<div class="space-y-4">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold">{{ $title }}</h1>
      <p class="text-sm text-gray-600">Buat perintah kerja dari tiket/PM/insiden.</p>
    </div>
    <a href="{{ route('wo.index') }}" class="rounded-xl bg-gray-100 px-3 py-2 text-sm hover:bg-gray-200">Kembali</a>
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

  <form method="post" action="{{ route('wo.store') }}" class="bg-white rounded-2xl shadow p-3 md:p-4">
    @csrf

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      {{-- Jenis WO --}}
      <div>
        <label class="block text-xs text-gray-600 mb-1">Jenis Work Order <span class="text-rose-600">*</span></label>
        <select name="type" class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500" required>
          @foreach($typeOpts as $k=>$v)
            <option value="{{ $k }}" @selected(old('type')===$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>

      {{-- Ticket (opsional) --}}
      <div>
        <label class="block text-xs text-gray-600 mb-1">Ticket (Opsional)</label>
        <select name="ticket_id" id="ticket_id" class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
          <option value="">— Tanpa Ticket —</option>
          @foreach($tickets as $t)
            <option value="{{ $t->id }}" @selected(old('ticket_id')==$t->id)>
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
          <option value="">— Pilih Aset —</option>
          @foreach($assets as $a)
            <option value="{{ $a->id }}" @selected(old('asset_id')==$a->id)>
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
            <option value="{{ $u->id }}" @selected(old('assignee_id')==$u->id)>{{ $u->name }} @if($u->divisi) — {{ $u->divisi }} @endif</option>
          @endforeach
        </select>
      </div>

      {{-- Ringkasan Pekerjaan --}}
      <div class="md:col-span-2">
        <label class="block text-xs text-gray-600 mb-1">Ringkasan Pekerjaan <span class="text-rose-600">*</span></label>
        <textarea name="ringkasan_pekerjaan" rows="3" class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500" required
          placeholder="Deskripsikan singkat pekerjaan yang akan dilakukan…">{{ old('ringkasan_pekerjaan') }}</textarea>
        <p class="text-[11px] text-gray-500 mt-1">Contoh: Cek suara bearing dan ganti jika aus; kalibrasi sensor; bersihkan housing.</p>
      </div>
    </div>

    {{-- Actions --}}
    <div class="mt-5 flex flex-col md:flex-row gap-2 md:justify-end">
      <a href="{{ route('wo.index') }}" class="rounded-xl bg-gray-100 px-4 py-2 text-center hover:bg-gray-200">Batal</a>
      <button class="rounded-xl bg-gray-900 text-white px-4 py-2 hover:bg-black">Simpan WO</button>
    </div>
  </form>
</div>

{{-- Hint dinamis sederhana utk aset & tiket --}}
<script>
  (function() {
    const ticketSel = document.getElementById('ticket_id');
    const assetSel  = document.getElementById('asset_id');
    const ticketHint= document.getElementById('ticket_hint');
    const assetHint = document.getElementById('asset_hint');

    function setHint(sel, hintEl, label) {
      if (!sel || !hintEl) return;
      const opt = sel.options[sel.selectedIndex];
      if (!opt || !opt.value) {
        hintEl.textContent = '';
        return;
      }
      hintEl.textContent = label + ': ' + opt.text.replace(/^\\s*|\\s*$/g,'');
    }

    ticketSel?.addEventListener('change', () => setHint(ticketSel, ticketHint, 'Terpilih'));
    assetSel?.addEventListener('change',  () => setHint(assetSel,  assetHint,  'Terpilih'));

    // inisialisasi saat load
    setHint(ticketSel, ticketHint, 'Terpilih');
    setHint(assetSel,  assetHint,  'Terpilih');
  })();
</script>
@endsection
