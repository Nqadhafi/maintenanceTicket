@extends('layouts.app')

@section('content')
@php
  use Carbon\Carbon;

  $title   = 'Jadwal Preventive Maintenance';
  $planId  = $filters['plan_id'] ?? null;
  $assetQ  = $filters['asset_q'] ?? null;
  $aktif   = $filters['aktif'] ?? null;
  $dueIn   = $filters['due_in_days'] ?? null;

  function badge_due($dt) {
    if (!$dt) return '';
    $now = now();
    if ($dt->isPast()) {
      return '<span class="inline-flex items-center rounded-full bg-rose-100 text-rose-700 text-xs px-2 py-0.5">Overdue</span>';
    }
    if ($dt->lte($now->copy()->addDays(3))) {
      return '<span class="inline-flex items-center rounded-full bg-amber-100 text-amber-700 text-xs px-2 py-0.5">Due Soon</span>';
    }
    return '';
  }

  function fmt_interval_short($type, $val) {
    $m = ['DAY'=>'Hari','WEEK'=>'Minggu','MONTH'=>'Bulan','METER'=>'Meter'];
    return ($m[$type] ?? $type) . ' / ' . (int)$val;
  }
@endphp

<div class="space-y-4">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold">{{ $title }}</h1>
      <p class="text-sm text-gray-600">Kelola jadwal PM per aset dari rencana yang telah dibuat.</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('pm.schedules.create') }}" class="inline-flex items-center rounded-xl bg-sky-600 px-3 py-2 text-white shadow hover:bg-sky-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5a1 1 0 1 1 2 0v6h6a1 1 0 1 1 0 2h-6v6a1 1 0 1 1-2 0v-6H5a1 1 0 1 1 0-2h6z"/></svg>
        Tambah Jadwal
      </a>
    </div>
  </div>

  {{-- Filter --}}
  <form method="get" class="bg-white rounded-2xl shadow p-3 md:p-4">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
      <div class="md:col-span-2">
        <label class="block text-xs text-gray-600 mb-1">Rencana PM</label>
        <select name="plan_id" class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
          <option value="">— Semua Rencana —</option>
          @foreach($plans as $p)
            <option value="{{ $p->id }}" @selected((string)$planId === (string)$p->id)>{{ $p->nama_plan }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs text-gray-600 mb-1">Cari Aset</label>
        <input type="text" name="asset_q" value="{{ $assetQ }}" placeholder="Kode / Nama aset…"
               class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
      </div>
      <div>
        <label class="block text-xs text-gray-600 mb-1">Status</label>
        <select name="aktif" class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
          <option value="">— Semua —</option>
          <option value="1" @selected($aktif==='1')>Aktif</option>
          <option value="0" @selected($aktif==='0')>Nonaktif</option>
        </select>
      </div>
      <div>
        <label class="block text-xs text-gray-600 mb-1">Jatuh Tempo Dalam (hari)</label>
        <input type="number" name="due_in_days" min="1" step="1" value="{{ $dueIn }}" placeholder="3"
               class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
      </div>
    </div>
    <div class="mt-3 flex items-center gap-2">
      <button class="rounded-xl bg-gray-900 text-white px-4 py-2">Terapkan</button>
      @if(request()->query())
        <a href="{{ route('pm.schedules.index') }}" class="rounded-xl bg-gray-100 px-4 py-2">Reset</a>
      @endif
    </div>
  </form>

  {{-- Table (Desktop) --}}
  <div class="hidden md:block bg-white rounded-2xl shadow overflow-hidden">
    <table class="min-w-full whitespace-nowrap">
      <thead class="bg-gray-50 text-xs uppercase text-gray-500">
        <tr>
          <th class="px-4 py-3 text-left">Rencana</th>
          <th class="px-4 py-3 text-left">Aset</th>
          <th class="px-4 py-3 text-left">Interval</th>
          <th class="px-4 py-3 text-left">Jatuh Tempo</th>
          <th class="px-4 py-3 text-left">Meter</th>
          <th class="px-4 py-3 text-left">Status</th>
          <th class="px-4 py-3 text-right">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse($rows as $r)
          @php
            $due   = $r->next_due_at ? Carbon::parse($r->next_due_at) : null;
            $badge = $due ? badge_due($due) : '';
          @endphp
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <div class="font-medium">{{ $r->plan->nama_plan ?? '—' }}</div>
              <div class="text-[11px] text-gray-500">#{{ $r->id }}</div>
            </td>
            <td class="px-4 py-3">
              <div class="font-medium">{{ $r->asset->kode_aset ?? '—' }}</div>
              <div class="text-[11px] text-gray-500 truncate max-w-[22ch]">{{ $r->asset->nama ?? '—' }}</div>
            </td>
            <td class="px-4 py-3">{{ fmt_interval_short($r->plan->interval_type ?? null, $r->plan->interval_value ?? null) }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <span>{{ $due ? $due->format('d/m/Y H:i') : '—' }}</span>
                {!! $badge !!}
              </div>
            </td>
            <td class="px-4 py-3">{{ $r->meter_threshold ? number_format($r->meter_threshold) : '—' }}</td>
            <td class="px-4 py-3">
              @if($r->aktif)
                <span class="inline-flex items-center rounded-full bg-emerald-100 text-emerald-700 text-xs px-2 py-0.5">Aktif</span>
              @else
                <span class="inline-flex items-center rounded-full bg-gray-100 text-gray-700 text-xs px-2 py-0.5">Nonaktif</span>
              @endif
            </td>
            <td class="px-4 py-3 text-right">
              <div class="inline-flex gap-2">
                <a href="{{ route('pm.exec.create', $r->id) }}" class="rounded-lg px-3 py-1 text-sm bg-emerald-50 text-emerald-700 hover:bg-emerald-100">Eksekusi</a>
                <a href="{{ route('pm.schedules.edit', $r->id) }}" class="rounded-lg px-3 py-1 text-sm bg-sky-50 text-sky-700 hover:bg-sky-100">Edit</a>
                <form method="post" action="{{ route('pm.schedules.destroy',$r->id) }}" onsubmit="return confirm('Hapus jadwal ini?')">
                  @csrf @method('DELETE')
                  <button class="rounded-lg px-3 py-1 text-sm bg-rose-50 text-rose-700 hover:bg-rose-100">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-4 py-8 text-center text-gray-600">Belum ada jadwal PM.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Cards (Mobile) --}}
  <div class="md:hidden space-y-3">
    @forelse($rows as $r)
      @php
        $due   = $r->next_due_at ? Carbon::parse($r->next_due_at) : null;
        $badge = $due ? badge_due($due) : '';
      @endphp
      <div class="bg-white rounded-2xl shadow p-3">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="text-sm font-semibold truncate">{{ $r->plan->nama_plan ?? '—' }}</div>
            <div class="text-[11px] text-gray-500 truncate">{{ $r->asset->kode_aset ?? '—' }} · {{ $r->asset->nama ?? '—' }}</div>
          </div>
          <div class="text-right">
            {!! $badge !!}
          </div>
        </div>

        <div class="mt-2 grid grid-cols-3 gap-2 text-[11px]">
          <div class="bg-gray-50 rounded-xl px-2 py-1">
            <div class="text-gray-500">Interval</div>
            <div class="font-medium">{{ fmt_interval_short($r->plan->interval_type ?? null, $r->plan->interval_value ?? null) }}</div>
          </div>
          <div class="bg-gray-50 rounded-xl px-2 py-1">
            <div class="text-gray-500">Due</div>
            <div class="font-medium">{{ $due ? $due->format('d/m/Y H:i') : '—' }}</div>
          </div>
          <div class="bg-gray-50 rounded-xl px-2 py-1">
            <div class="text-gray-500">Meter</div>
            <div class="font-medium">{{ $r->meter_threshold ? number_format($r->meter_threshold) : '—' }}</div>
          </div>
        </div>

        <div class="mt-3 flex items-center justify-between">
          <div>
            @if($r->aktif)
              <span class="inline-flex items-center rounded-full bg-emerald-100 text-emerald-700 text-[10px] px-2 py-0.5">Aktif</span>
            @else
              <span class="inline-flex items-center rounded-full bg-gray-100 text-gray-700 text-[10px] px-2 py-0.5">Nonaktif</span>
            @endif
          </div>
          <div class="flex gap-2">
            <a href="{{ route('pm.exec.create', $r->id) }}" class="rounded-lg px-3 py-1 text-xs bg-emerald-50 text-emerald-700">Eksekusi</a>
            <a href="{{ route('pm.schedules.edit', $r->id) }}" class="rounded-lg px-3 py-1 text-xs bg-sky-50 text-sky-700">Edit</a>
            <form method="post" action="{{ route('pm.schedules.destroy',$r->id) }}" onsubmit="return confirm('Hapus jadwal ini?')">
              @csrf @method('DELETE')
              <button class="rounded-lg px-3 py-1 text-xs bg-rose-50 text-rose-700">Hapus</button>
            </form>
          </div>
        </div>
      </div>
    @empty
      <div class="bg-white rounded-2xl shadow p-6 text-center text-gray-600">Belum ada jadwal PM.</div>
    @endforelse
  </div>

  {{-- Pagination --}}
  <div>
    {{ $rows->links() }}
  </div>
</div>
@endsection
