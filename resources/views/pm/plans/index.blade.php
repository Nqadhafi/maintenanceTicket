@extends('layouts.app')

@section('content')
@php
  $title = 'Rencana Preventive Maintenance';
  $q         = $filters['q'] ?? null;
  $category  = $filters['category_id'] ?? null;
  $aktif     = $filters['aktif'] ?? null;

  function fmt_interval($type, $val) {
    if (!$type || !$val) return '—';
    $map = ['DAY'=>'Hari','WEEK'=>'Minggu','MONTH'=>'Bulan','METER'=>'Meter'];
    return ($map[$type] ?? $type) . ' / ' . (int)$val;
  }
@endphp

<div class="space-y-4">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold">{{ $title }}</h1>
      <p class="text-sm text-gray-600">Kelola template & periodisasi PM per kategori aset.</p>
    </div>
    <div>
      <a href="{{ route('pm.plans.create') }}" class="inline-flex items-center rounded-xl bg-sky-600 px-3 py-2 text-white shadow hover:bg-sky-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5a1 1 0 1 1 2 0v6h6a1 1 0 1 1 0 2h-6v6a1 1 0 1 1-2 0v-6H5a1 1 0 1 1 0-2h6z"/></svg>
        Tambah Plan
      </a>
    </div>
  </div>

  {{-- Filter Bar --}}
  <form method="get" class="bg-white rounded-2xl shadow p-3 md:p-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
      <div>
        <label class="block text-xs text-gray-600 mb-1">Pencarian</label>
        <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama plan…"
               class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
      </div>
      <div>
        <label class="block text-xs text-gray-600 mb-1">Kategori Aset</label>
        <select name="category_id" class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
          <option value="">— Semua —</option>
          @foreach($categories as $c)
            <option value="{{ $c->id }}" @selected((string)$category === (string)$c->id)>{{ $c->nama }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs text-gray-600 mb-1">Status</label>
        <select name="aktif" class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
          <option value="">— Semua —</option>
          <option value="1" @selected($aktif==='1')>Aktif</option>
          <option value="0" @selected($aktif==='0')>Nonaktif</option>
        </select>
      </div>
      <div class="flex items-end gap-2">
        <button class="rounded-xl bg-gray-900 text-white px-4 py-2 w-full md:w-auto">Terapkan</button>
        @if(request()->query())
          <a href="{{ route('pm.plans.index') }}" class="rounded-xl bg-gray-100 px-4 py-2 w-full md:w-auto">Reset</a>
        @endif
      </div>
    </div>
  </form>

  {{-- List (Desktop: Table) --}}
  <div class="hidden md:block bg-white rounded-2xl shadow overflow-hidden">
    <table class="min-w-full whitespace-nowrap">
      <thead class="bg-gray-50 text-xs uppercase text-gray-500">
        <tr>
          <th class="px-4 py-3 text-left">Plan</th>
          <th class="px-4 py-3 text-left">Kategori</th>
          <th class="px-4 py-3 text-left">Interval</th>
          <th class="px-4 py-3 text-left">Checklist</th>
          <th class="px-4 py-3 text-left">Default PJ</th>
          <th class="px-4 py-3 text-left">Status</th>
          <th class="px-4 py-3 text-right">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse($rows as $r)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <div class="font-medium">{{ $r->nama_plan }}</div>
              <div class="text-xs text-gray-500">ID: {{ $r->id }}</div>
            </td>
            <td class="px-4 py-3">{{ $r->category->nama ?? '—' }}</td>
            <td class="px-4 py-3">{{ fmt_interval($r->interval_type, $r->interval_value) }}</td>
            <td class="px-4 py-3">{{ is_array($r->checklist) ? count($r->checklist) : 0 }} item</td>
            <td class="px-4 py-3">{{ $r->defaultAssignee->name ?? '—' }}</td>
            <td class="px-4 py-3">
              @if($r->aktif)
                <span class="inline-flex items-center rounded-full bg-emerald-100 text-emerald-700 text-xs px-2 py-1">Aktif</span>
              @else
                <span class="inline-flex items-center rounded-full bg-gray-100 text-gray-700 text-xs px-2 py-1">Nonaktif</span>
              @endif
            </td>
            <td class="px-4 py-3 text-right">
              <div class="inline-flex gap-2">
                <a href="{{ route('pm.plans.edit',$r->id) }}" class="rounded-lg px-3 py-1 text-sm bg-sky-50 text-sky-700 hover:bg-sky-100">Edit</a>
                <form method="post" action="{{ route('pm.plans.destroy',$r->id) }}" onsubmit="return confirm('Hapus rencana PM ini?')">
                  @csrf @method('DELETE')
                  <button class="rounded-lg px-3 py-1 text-sm bg-rose-50 text-rose-700 hover:bg-rose-100">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-4 py-8 text-center text-gray-600">Belum ada rencana PM.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- List (Mobile: Cards) --}}
  <div class="md:hidden space-y-3">
    @forelse($rows as $r)
      <div class="bg-white rounded-2xl shadow p-3">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="text-sm font-semibold truncate">{{ $r->nama_plan }}</div>
            <div class="text-xs text-gray-500 truncate">{{ $r->category->nama ?? '—' }}</div>
          </div>
          <div>
            @if($r->aktif)
              <span class="inline-flex items-center rounded-full bg-emerald-100 text-emerald-700 text-[10px] px-2 py-0.5">Aktif</span>
            @else
              <span class="inline-flex items-center rounded-full bg-gray-100 text-gray-700 text-[10px] px-2 py-0.5">Nonaktif</span>
            @endif
          </div>
        </div>
        <div class="mt-2 grid grid-cols-3 gap-2 text-[11px]">
          <div class="bg-gray-50 rounded-xl px-2 py-1">
            <div class="text-gray-500">Interval</div>
            <div class="font-medium">{{ fmt_interval($r->interval_type, $r->interval_value) }}</div>
          </div>
          <div class="bg-gray-50 rounded-xl px-2 py-1">
            <div class="text-gray-500">Checklist</div>
            <div class="font-medium">{{ is_array($r->checklist) ? count($r->checklist) : 0 }} item</div>
          </div>
          <div class="bg-gray-50 rounded-xl px-2 py-1">
            <div class="text-gray-500">PJ</div>
            <div class="font-medium truncate">{{ $r->defaultAssignee->name ?? '—' }}</div>
          </div>
        </div>
        <div class="mt-3 flex justify-end gap-2">
          <a href="{{ route('pm.plans.edit',$r->id) }}" class="rounded-lg px-3 py-1 text-xs bg-sky-50 text-sky-700">Edit</a>
          <form method="post" action="{{ route('pm.plans.destroy',$r->id) }}" onsubmit="return confirm('Hapus rencana PM ini?')">
            @csrf @method('DELETE')
            <button class="rounded-lg px-3 py-1 text-xs bg-rose-50 text-rose-700">Hapus</button>
          </form>
        </div>
      </div>
    @empty
      <div class="bg-white rounded-2xl shadow p-6 text-center text-gray-600">Belum ada rencana PM.</div>
    @endforelse
  </div>

  {{-- Pagination --}}
  <div>
    {{ $rows->links() }}
  </div>
</div>
@endsection
