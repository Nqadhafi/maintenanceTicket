@extends('layouts.app')

@section('content')
@php
  use App\Models\WorkOrder;

  $title  = 'Work Orders';
  $q      = $filters['q'] ?? null;
  $type   = $filters['type'] ?? null;
  $status = $filters['status'] ?? null;

  $typeOpts = [
    WorkOrder::TYPE_CORR => 'Corrective',
    WorkOrder::TYPE_PREV => 'Preventive',
  ];
  $statusOpts = [
    WorkOrder::ST_OPEN       => 'Open',
    WorkOrder::ST_INPROGRESS => 'In Progress',
    WorkOrder::ST_DONE       => 'Done',
  ];
@endphp

<div class="space-y-4">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold">{{ $title }}</h1>
      <p class="text-sm text-gray-600">Kelola perintah kerja dari tiket/PM/insiden.</p>
    </div>
    <div>
      <a href="{{ route('wo.create') }}" class="inline-flex items-center rounded-xl bg-sky-600 px-3 py-2 text-white shadow hover:bg-sky-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5a1 1 0 1 1 2 0v6h6a1 1 0 1 1 0 2h-6v6a1 1 0 1 1-2 0v-6H5a1 1 0 1 1 0-2h6z"/></svg>
        Buat WO
      </a>
    </div>
  </div>

  {{-- Filter --}}
  <form method="get" class="bg-white rounded-2xl shadow p-3 md:p-4">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
      <div class="md:col-span-2">
        <label class="block text-xs text-gray-600 mb-1">Pencarian</label>
        <input type="text" name="q" value="{{ $q }}" placeholder="Cari kode WO / ringkasan / tiket / aset…"
               class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
      </div>
      <div>
        <label class="block text-xs text-gray-600 mb-1">Jenis WO</label>
        <select name="type" class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
          <option value="">— Semua —</option>
          @foreach($typeOpts as $k=>$v)
            <option value="{{ $k }}" @selected($type===$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs text-gray-600 mb-1">Status</label>
        <select name="status" class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500">
          <option value="">— Semua —</option>
          @foreach($statusOpts as $k=>$v)
            <option value="{{ $k }}" @selected($status===$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div class="flex items-end gap-2">
        <button class="rounded-xl bg-gray-900 text-white px-4 py-2 w-full md:w-auto">Terapkan</button>
        @if(request()->query())
          <a href="{{ route('wo.index') }}" class="rounded-xl bg-gray-100 px-4 py-2 w-full md:w-auto">Reset</a>
        @endif
      </div>
    </div>
  </form>

  {{-- Table (Desktop) --}}
  <div class="hidden md:block bg-white rounded-2xl shadow overflow-hidden">
    <table class="min-w-full whitespace-nowrap">
      <thead class="bg-gray-50 text-xs uppercase text-gray-500">
        <tr>
          <th class="px-4 py-3 text-left">Kode WO</th>
          <th class="px-4 py-3 text-left">Jenis</th>
          <th class="px-4 py-3 text-left">Ringkasan</th>
          <th class="px-4 py-3 text-left">Aset</th>
          <th class="px-4 py-3 text-left">Ticket</th>
          <th class="px-4 py-3 text-left">PJ</th>
          <th class="px-4 py-3 text-left">Status</th>
          <th class="px-4 py-3 text-right">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse($rows as $r)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">
              <div class="font-medium">{{ $r->kode_wo }}</div>
              <div class="text-[11px] text-gray-500">#{{ $r->id }}</div>
            </td>
            <td class="px-4 py-3">
              <span class="inline-flex items-center rounded-full text-xs px-2 py-0.5
                {{ $r->type === WorkOrder::TYPE_CORR ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                {{ $typeOpts[$r->type] ?? $r->type }}
              </span>
            </td>
            <td class="px-4 py-3 max-w-[28ch] truncate">{{ $r->ringkasan_pekerjaan }}</td>
            <td class="px-4 py-3">
              <div class="font-medium">{{ $r->asset->kode_aset ?? '—' }}</div>
              <div class="text-[11px] text-gray-500 truncate max-w-[22ch]">{{ $r->asset->nama ?? '—' }}</div>
            </td>
            <td class="px-4 py-3">
              @if($r->ticket)
                <div class="font-medium">{{ $r->ticket->kode_tiket }}</div>
                <div class="text-[11px] text-gray-500 truncate max-w-[22ch]">{{ $r->ticket->judul }}</div>
              @else
                —
              @endif
            </td>
            <td class="px-4 py-3">
              {{ $r->assignee->name ?? '—' }}
              @if(optional($r->assignee)->divisi)
                <div class="text-[11px] text-gray-500">{{ $r->assignee->divisi }}</div>
              @endif
            </td>
            <td class="px-4 py-3">
              @php
                $badge = [
                  WorkOrder::ST_OPEN       => 'bg-gray-100 text-gray-700',
                  WorkOrder::ST_INPROGRESS => 'bg-sky-100 text-sky-700',
                  WorkOrder::ST_DONE       => 'bg-emerald-100 text-emerald-700',
                ][$r->status] ?? 'bg-gray-100 text-gray-700';
              @endphp
              <span class="inline-flex items-center rounded-full text-xs px-2 py-0.5 {{ $badge }}">
                {{ $statusOpts[$r->status] ?? $r->status }}
              </span>
            </td>
            <td class="px-4 py-3 text-right">
              <div class="inline-flex gap-2">
                <a href="{{ route('wo.show',$r->id) }}" class="rounded-lg px-3 py-1 text-sm bg-emerald-50 text-emerald-700 hover:bg-emerald-100">Detail</a>
                <a href="{{ route('wo.edit',$r->id) }}" class="rounded-lg px-3 py-1 text-sm bg-sky-50 text-sky-700 hover:bg-sky-100">Edit</a>
                <form method="post" action="{{ route('wo.destroy',$r->id) }}" onsubmit="return confirm('Hapus WO ini?')">
                  @csrf @method('DELETE')
                  <button class="rounded-lg px-3 py-1 text-sm bg-rose-50 text-rose-700 hover:bg-rose-100">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="px-4 py-8 text-center text-gray-600">Belum ada Work Order.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Cards (Mobile) --}}
  <div class="md:hidden space-y-3">
    @forelse($rows as $r)
      <div class="bg-white rounded-2xl shadow p-3">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="text-sm font-semibold">{{ $r->kode_wo }}</div>
            <div class="text-[11px] text-gray-500 truncate">{{ $r->ringkasan_pekerjaan }}</div>
          </div>
          <div>
            <span class="inline-flex items-center rounded-full text-[10px] px-2 py-0.5
              {{ $r->type === WorkOrder::TYPE_CORR ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
              {{ $typeOpts[$r->type] ?? $r->type }}
            </span>
          </div>
        </div>

        <div class="mt-2 grid grid-cols-3 gap-2 text-[11px]">
          <div class="bg-gray-50 rounded-xl px-2 py-1">
            <div class="text-gray-500">Aset</div>
            <div class="font-medium truncate">{{ $r->asset->kode_aset ?? '—' }}</div>
          </div>
          <div class="bg-gray-50 rounded-xl px-2 py-1">
            <div class="text-gray-500">Ticket</div>
            <div class="font-medium truncate">{{ $r->ticket->kode_tiket ?? '—' }}</div>
          </div>
          <div class="bg-gray-50 rounded-xl px-2 py-1">
            <div class="text-gray-500">Status</div>
            @php
              $badge = [
                WorkOrder::ST_OPEN       => 'bg-gray-100 text-gray-700',
                WorkOrder::ST_INPROGRESS => 'bg-sky-100 text-sky-700',
                WorkOrder::ST_DONE       => 'bg-emerald-100 text-emerald-700',
              ][$r->status] ?? 'bg-gray-100 text-gray-700';
            @endphp
            <div class="font-medium inline-flex items-center rounded-full px-2 py-0.5 {{ $badge }}">
              {{ $statusOpts[$r->status] ?? $r->status }}
            </div>
          </div>
        </div>

        <div class="mt-3 flex justify-end gap-2">
          <a href="{{ route('wo.show',$r->id) }}" class="rounded-lg px-3 py-1 text-xs bg-emerald-50 text-emerald-700">Detail</a>
          <a href="{{ route('wo.edit',$r->id) }}" class="rounded-lg px-3 py-1 text-xs bg-sky-50 text-sky-700">Edit</a>
          <form method="post" action="{{ route('wo.destroy',$r->id) }}" onsubmit="return confirm('Hapus WO ini?')">
            @csrf @method('DELETE')
            <button class="rounded-lg px-3 py-1 text-xs bg-rose-50 text-rose-700">Hapus</button>
          </form>
        </div>
      </div>
    @empty
      <div class="bg-white rounded-2xl shadow p-6 text-center text-gray-600">Belum ada Work Order.</div>
    @endforelse
  </div>

  {{-- Pagination --}}
  <div>
    {{ $rows->links() }}
  </div>
</div>
@endsection
