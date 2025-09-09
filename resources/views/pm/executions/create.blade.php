@extends('layouts.app')

@section('content')
@php
  use Carbon\Carbon;

  /** @var \App\Models\PmSchedule $schedule */
  $plan   = $schedule->plan;
  $asset  = $schedule->asset;
  $items  = is_array($plan->checklist ?? null) ? $plan->checklist : [];
  $due    = $schedule->next_due_at ? Carbon::parse($schedule->next_due_at) : null;

  function fmt_interval_pm($type, $val) {
    $m = ['DAY'=>'Hari','WEEK'=>'Minggu','MONTH'=>'Bulan','METER'=>'Meter'];
    return ($m[$type] ?? $type) . ' / ' . (int)$val;
  }
@endphp

<div class="space-y-4">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold">Eksekusi Preventive Maintenance</h1>
      <p class="text-sm text-gray-600">Catat hasil pelaksanaan checklist untuk jadwal ini.</p>
    </div>
    <a href="{{ route('pm.schedules.index') }}" class="rounded-xl bg-gray-100 px-3 py-2 text-sm hover:bg-gray-200">Kembali</a>
  </div>

  {{-- Context Card --}}
  <div class="bg-white rounded-2xl shadow p-3 md:p-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
      <div>
        <div class="text-gray-500">Rencana</div>
        <div class="font-medium">{{ $plan->nama_plan ?? '—' }}</div>
      </div>
      <div>
        <div class="text-gray-500">Aset</div>
        <div class="font-medium">{{ $asset->kode_aset ?? '—' }}</div>
        <div class="text-xs text-gray-500 truncate">{{ $asset->nama ?? '—' }}</div>
      </div>
      <div>
        <div class="text-gray-500">Interval</div>
        <div class="font-medium">{{ fmt_interval_pm($plan->interval_type ?? null, $plan->interval_value ?? null) }}</div>
      </div>
      <div>
        <div class="text-gray-500">Jatuh Tempo</div>
        <div class="font-medium">{{ $due ? $due->format('d/m/Y H:i') : '—' }}</div>
      </div>
    </div>
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

  <form id="execForm" method="post" action="{{ route('pm.exec.store', $schedule->id) }}" class="bg-white rounded-2xl shadow p-3 md:p-4">
    @csrf

    {{-- Performed at --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs text-gray-600 mb-1">Waktu Pelaksanaan <span class="text-rose-600">*</span></label>
        <input type="datetime-local" name="performed_at" value="{{ old('performed_at', now()->format('Y-m-d\TH:i')) }}"
               class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500" required>
      </div>
    </div>

    {{-- Checklist Results --}}
    <div class="mt-4">
      <div class="flex items-center justify-between mb-2">
        <label class="block text-sm font-medium">Hasil Checklist <span class="text-rose-600">*</span></label>
      </div>

      @if (empty($items))
        <div class="rounded-xl bg-amber-50 text-amber-800 p-3 text-sm">
          Checklist pada plan ini belum diisi. Silakan kembali dan lengkapi checklist pada rencana PM.
        </div>
      @else
        <div class="space-y-3">
          @foreach ($items as $idx => $text)
            <div class="rounded-xl border border-gray-200 p-3">
              <div class="text-sm font-medium">{{ $idx+1 }}. {{ $text }}</div>
              <div class="mt-2 grid grid-cols-1 md:grid-cols-6 gap-2 items-start">
                <div class="md:col-span-2">
                  <label class="block text-xs text-gray-600 mb-1">Status</label>
                  <select class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500 exec-status">
                    <option value="OK"    @selected(old("checklist_status.$idx")==="OK")>OK</option>
                    <option value="NOK"   @selected(old("checklist_status.$idx")==="NOK")>Tidak OK</option>
                    <option value="N/A"   @selected(old("checklist_status.$idx")==="N/A")>N/A</option>
                  </select>
                </div>
                <div class="md:col-span-4">
                  <label class="block text-xs text-gray-600 mb-1">Catatan (opsional)</label>
                  <input type="text" class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500 exec-note"
                         value="{{ old("checklist_note.$idx") }}"
                         placeholder="Catatan singkat…">
                </div>
              </div>

              {{-- hidden yang akan dikirim ke server sesuai format controller (array of string) --}}
              <input type="hidden" name="checklist_result[]" class="exec-hidden"
                     value="{{ old('checklist_result.'.$idx) }}">
            </div>
          @endforeach
        </div>
      @endif
      <p class="text-[11px] text-gray-500 mt-2">
        Nilai akan dirangkai otomatis: <em>“[Status] - {{ '{deskripsi item}' }} - {{ '{catatan jika ada}' }}”</em>.
      </p>
    </div>

    {{-- Notes umum --}}
    <div class="mt-4">
      <label class="block text-xs text-gray-600 mb-1">Catatan Umum</label>
      <textarea name="notes" rows="3" class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Catatan tambahan untuk eksekusi ini…">{{ old('notes') }}</textarea>
    </div>

    {{-- Generate WO --}}
    <div class="mt-4 rounded-2xl bg-gray-50 p-3 md:p-4">
      <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="make_wo" value="1" id="make_wo"
               class="rounded border-gray-300 text-sky-600 focus:ring-sky-500" @checked(old('make_wo'))>
        <span class="text-sm font-medium">Buat Work Order (PREVENTIVE) dari hasil eksekusi ini</span>
      </label>

      <div id="wo_wrap" class="mt-3 {{ old('make_wo') ? '' : 'hidden' }}">
        <label class="block text-xs text-gray-600 mb-1">Ringkasan Pekerjaan WO</label>
        <input type="text" name="wo_ringkasan" value="{{ old('wo_ringkasan') ?? ('PM '.$plan->nama_plan) }}"
               class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500"
               placeholder="Contoh: Temuan saat PM - perlu penggantian belt">
        <p class="text-[11px] text-gray-500 mt-1">WO akan diset tipe <b>PREVENTIVE</b> dan asset mengikuti jadwal ini.</p>
      </div>
    </div>

    {{-- Actions --}}
    <div class="mt-5 flex flex-col md:flex-row gap-2 md:justify-end">
      <a href="{{ route('pm.schedules.index') }}" class="rounded-xl bg-gray-100 px-4 py-2 text-center hover:bg-gray-200">Batal</a>
      <button class="rounded-xl bg-emerald-600 text-white px-4 py-2 hover:bg-emerald-700">Simpan Eksekusi</button>
    </div>
  </form>
</div>

{{-- JS: rangkai checklist_result[] & toggle WO --}}
<script>
  (function() {
    const form    = document.getElementById('execForm');
    const rows    = document.querySelectorAll('.rounded-xl.border'); // tiap item
    const makeWO  = document.getElementById('make_wo');
    const woWrap  = document.getElementById('wo_wrap');

    makeWO?.addEventListener('change', function() {
      if (this.checked) {
        woWrap.classList.remove('hidden');
      } else {
        woWrap.classList.add('hidden');
      }
    });

    form?.addEventListener('submit', function(e) {
      // Susun hidden input per item: "[STATUS] - {deskripsi} - {catatan}"
      rows.forEach(function(card) {
        const statusEl = card.querySelector('.exec-status');
        const noteEl   = card.querySelector('.exec-note');
        const hidden   = card.querySelector('.exec-hidden');
        const titleEl  = card.querySelector('.text-sm.font-medium');
        const title    = titleEl ? titleEl.textContent.replace(/^\d+\.\s*/, '') : '';

        const status = statusEl ? (statusEl.value || 'OK') : 'OK';
        const note   = noteEl ? (noteEl.value || '') : '';
        const value  = note ? `[${status}] - ${title} - ${note}` : `[${status}] - ${title}`;

        if (hidden) hidden.value = value;
      });
    });
  })();
</script>
@endsection
