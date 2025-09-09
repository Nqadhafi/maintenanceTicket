@extends('layouts.app')

@section('content')
@php
  $title = 'Tambah Jadwal PM';
@endphp

<div class="space-y-4">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold">{{ $title }}</h1>
      <p class="text-sm text-gray-600">Buat jadwal PM untuk aset berdasarkan rencana.</p>
    </div>
    <a href="{{ route('pm.schedules.index') }}" class="rounded-xl bg-gray-100 px-3 py-2 text-sm hover:bg-gray-200">Kembali</a>
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

  <form method="post" action="{{ route('pm.schedules.store') }}" class="bg-white rounded-2xl shadow p-3 md:p-4">
    @csrf

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      {{-- Plan --}}
      <div>
        <label class="block text-xs text-gray-600 mb-1">Rencana PM <span class="text-rose-600">*</span></label>
        <select name="pm_plan_id" id="pm_plan_id" class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500" required>
          <option value="">— Pilih Rencana —</option>
          @foreach($plans as $p)
            <option value="{{ $p->id }}" @selected(old('pm_plan_id')==$p->id)>{{ $p->nama_plan }}</option>
          @endforeach
        </select>
        <div id="plan_hint" class="text-[11px] text-gray-500 mt-1"></div>
      </div>

      {{-- Asset --}}
      <div>
        <label class="block text-xs text-gray-600 mb-1">Aset <span class="text-rose-600">*</span></label>
        <select name="asset_id" id="asset_id" class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500" required>
          <option value="">— Pilih Aset —</option>
          @foreach($assets as $a)
            <option value="{{ $a->id }}" @selected(old('asset_id')==$a->id)>{{ $a->kode_aset }} — {{ \Illuminate\Support\Str::limit($a->nama, 40) }}</option>
          @endforeach
        </select>
        <div id="asset_hint" class="text-[11px] text-gray-500 mt-1"></div>
      </div>

      {{-- Next Due --}}
      <div>
        <label class="block text-xs text-gray-600 mb-1">Jatuh Tempo Pertama (next_due_at) <span class="text-rose-600">*</span></label>
        <input type="datetime-local" name="next_due_at" value="{{ old('next_due_at', now()->addDay()->format('Y-m-d\TH:i')) }}"
               class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500" required>
        <p class="text-[11px] text-gray-500 mt-1">Tanggal/waktu eksekusi PM yang pertama.</p>
      </div>

      {{-- Meter threshold (opsional) --}}
      <div>
        <label class="block text-xs text-gray-600 mb-1">Ambang Meter (opsional)</label>
        <input type="number" name="meter_threshold" min="1" step="1" value="{{ old('meter_threshold') }}"
               class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500" placeholder="Mis. 1000">
        <p class="text-[11px] text-gray-500 mt-1">Isi jika interval rencana bertipe <b>Meter</b>.</p>
      </div>

      {{-- Aktif --}}
      <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2">
          <input type="checkbox" name="aktif" value="1" class="rounded border-gray-300 text-sky-600 focus:ring-sky-500" @checked(old('aktif', 1))>
          <span class="text-sm">Aktifkan jadwal setelah dibuat</span>
        </label>
      </div>
    </div>

    {{-- Actions --}}
    <div class="mt-5 flex flex-col md:flex-row gap-2 md:justify-end">
      <a href="{{ route('pm.schedules.index') }}" class="rounded-xl bg-gray-100 px-4 py-2 text-center hover:bg-gray-200">Batal</a>
      <button class="rounded-xl bg-gray-900 text-white px-4 py-2 hover:bg-black">Simpan Jadwal</button>
    </div>
  </form>
</div>

{{-- Hint dinamis sederhana --}}
<script>
  (function() {
    const planSel  = document.getElementById('pm_plan_id');
    const assetSel = document.getElementById('asset_id');
    const planHint = document.getElementById('plan_hint');
    const assetHint= document.getElementById('asset_hint');

    function setHint(sel, hintEl, label) {
      if (!sel || !hintEl) return;
      const opt = sel.options[sel.selectedIndex];
      hintEl.textContent = (opt && opt.value) ? (label + ': ' + opt.text.trim()) : '';
    }

    planSel?.addEventListener('change', () => setHint(planSel, planHint, 'Terpilih'));
    assetSel?.addEventListener('change', () => setHint(assetSel, assetHint, 'Terpilih'));
    setHint(planSel, planHint, 'Terpilih');
    setHint(assetSel, assetHint, 'Terpilih');
  })();
</script>
@endsection
