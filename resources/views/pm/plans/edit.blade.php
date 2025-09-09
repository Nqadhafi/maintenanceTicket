@extends('layouts.app')

@section('content')
@php
  $title = 'Ubah Rencana PM';
  $intervalOptions = [
    'DAY' => 'Hari',
    'WEEK'=> 'Minggu',
    'MONTH'=>'Bulan',
    'METER'=>'Meter',
  ];
  $checklist = old('checklist', is_array($plan->checklist) ? $plan->checklist : []);
  if (empty($checklist)) $checklist = [''];
@endphp

<div class="space-y-4">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold">{{ $title }}</h1>
      <p class="text-sm text-gray-600">
        Edit template preventive maintenance: <span class="font-medium">{{ $plan->nama_plan }}</span>
      </p>
    </div>
    <a href="{{ route('pm.plans.index') }}" class="rounded-xl bg-gray-100 px-3 py-2 text-sm hover:bg-gray-200">Kembali</a>
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

  <form method="post" action="{{ route('pm.plans.update', $plan->id) }}" class="bg-white rounded-2xl shadow p-3 md:p-4">
    @csrf @method('PUT')

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      {{-- Nama Plan --}}
      <div>
        <label class="block text-xs text-gray-600 mb-1">Nama Plan <span class="text-rose-600">*</span></label>
        <input type="text" name="nama_plan" value="{{ old('nama_plan', $plan->nama_plan) }}"
               class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500" required>
      </div>

      {{-- Kategori Aset --}}
      <div>
        <label class="block text-xs text-gray-600 mb-1">Kategori Aset <span class="text-rose-600">*</span></label>
        <select name="asset_category_id" class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500" required>
          @foreach($categories as $c)
            <option value="{{ $c->id }}" @selected(old('asset_category_id', $plan->asset_category_id)==$c->id)>{{ $c->nama }}</option>
          @endforeach
        </select>
      </div>

      {{-- Interval Type --}}
      <div>
        <label class="block text-xs text-gray-600 mb-1">Tipe Interval <span class="text-rose-600">*</span></label>
        <select name="interval_type" id="interval_type"
                class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500" required>
          @foreach($intervalOptions as $k=>$v)
            <option value="{{ $k }}" @selected(old('interval_type', $plan->interval_type)===$k)>{{ $v }}</option>
          @endforeach
        </select>
        <p class="text-[11px] text-gray-500 mt-1">
          Hari/Minggu/Bulan = periodik kalender, Meter = berdasarkan ambang meter/jarak/usage.
        </p>
      </div>

      {{-- Interval Value --}}
      <div>
        <label class="block text-xs text-gray-600 mb-1">Nilai Interval <span class="text-rose-600">*</span></label>
        <input type="number" min="1" step="1" name="interval_value" value="{{ old('interval_value', $plan->interval_value) }}"
               class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500" required>
      </div>

      {{-- Default PJ --}}
      <div>
        <label class="block text-xs text-gray-600 mb-1">Default Penanggung Jawab (Opsional)</label>
        <select name="default_assignee_id" class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
          <option value="">— Tidak ditetapkan —</option>
          @foreach($pjs as $u)
            <option value="{{ $u->id }}" @selected(old('default_assignee_id', $plan->default_assignee_id)==$u->id)>{{ $u->name }} @if($u->divisi) — {{ $u->divisi }} @endif</option>
          @endforeach
        </select>
      </div>

      {{-- Status --}}
      <div class="flex items-end">
        <label class="inline-flex items-center gap-2">
          <input type="checkbox" name="aktif" value="1" class="rounded border-gray-300 text-sky-600 focus:ring-sky-500"
                 @checked(old('aktif', $plan->aktif))>
          <span class="text-sm">Plan aktif</span>
        </label>
      </div>
    </div>

    {{-- Checklist Builder --}}
    <div class="mt-4">
      <div class="flex items-center justify-between mb-2">
        <label class="block text-sm font-medium">Checklist Tugas <span class="text-rose-600">*</span></label>
        <button type="button" id="btnAddItem" class="rounded-xl bg-sky-600 text-white px-3 py-1.5 text-sm hover:bg-sky-700">
          + Tambah Item
        </button>
      </div>

      <div id="checklistWrap" class="space-y-2">
        @foreach($checklist as $val)
          <div class="flex gap-2">
            <input type="text" name="checklist[]" value="{{ $val }}" required
                   class="flex-1 rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500"
                   placeholder="Deskripsi tugas (mis. Bersihkan filter, cek tegangan, ganti oli)">
            <button type="button" class="btnRemoveRow rounded-xl bg-gray-100 px-3 text-sm hover:bg-gray-200">Hapus</button>
          </div>
        @endforeach
      </div>

      <p class="text-[11px] text-gray-500 mt-2">Minimal 1 item. Urutan dapat merepresentasikan prioritas.</p>
    </div>

    {{-- Meta --}}
    <div class="mt-2 text-[11px] text-gray-500">
      Dibuat: {{ optional($plan->created_at)->format('d/m/Y H:i') ?? '—' }} ·
      Diperbarui: {{ optional($plan->updated_at)->format('d/m/Y H:i') ?? '—' }}
    </div>

    {{-- Actions --}}
    <div class="mt-5 flex flex-col md:flex-row gap-2 md:justify-end">
      <a href="{{ route('pm.plans.index') }}" class="rounded-xl bg-gray-100 px-4 py-2 text-center hover:bg-gray-200">Batal</a>
      <button class="rounded-xl bg-gray-900 text-white px-4 py-2 hover:bg-black">Simpan Perubahan</button>
    </div>
  </form>

  {{-- Danger zone (opsional) --}}
  <div class="bg-white rounded-2xl shadow p-3 md:p-4">
    <div class="flex items-center justify-between">
      <div>
        <div class="font-medium">Hapus Rencana PM</div>
        <div class="text-xs text-gray-500">Tindakan ini tidak dapat dibatalkan.</div>
      </div>
      <form method="post" action="{{ route('pm.plans.destroy', $plan->id) }}" onsubmit="return confirm('Yakin hapus rencana ini? Tindakan tidak bisa dibatalkan.')">
        @csrf @method('DELETE')
        <button class="rounded-xl bg-rose-600 text-white px-3 py-2 text-sm hover:bg-rose-700">Hapus</button>
      </form>
    </div>
  </div>
</div>

{{-- Minimal JS untuk checklist --}}
<script>
  (function() {
    const wrap = document.getElementById('checklistWrap');
    const btnAdd = document.getElementById('btnAddItem');

    btnAdd?.addEventListener('click', function() {
      const row = document.createElement('div');
      row.className = 'flex gap-2';
      row.innerHTML = `
        <input type="text" name="checklist[]" required
               class="flex-1 rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500"
               placeholder="Deskripsi tugas (mis. Bersihkan filter, cek tegangan, ganti oli)">
        <button type="button" class="btnRemoveRow rounded-xl bg-gray-100 px-3 text-sm hover:bg-gray-200">Hapus</button>
      `;
      wrap.appendChild(row);
    });

    wrap?.addEventListener('click', function(e) {
      if (e.target && e.target.classList.contains('btnRemoveRow')) {
        const rows = wrap.querySelectorAll('.flex.gap-2');
        if (rows.length > 1) {
          e.target.parentElement.remove();
        } else {
          const input = e.target.parentElement.querySelector('input[name="checklist[]"]');
          if (input) input.value = '';
        }
      }
    });
  })();
</script>
@endsection
