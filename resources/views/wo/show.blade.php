@extends('layouts.app')

@section('content')
@php
  use App\Models\WorkOrder;
  use Carbon\Carbon;

  /** @var \App\Models\WorkOrder $wo */

  $typeLabel = [
    WorkOrder::TYPE_CORR => 'Corrective',
    WorkOrder::TYPE_PREV => 'Preventive',
  ][$wo->type] ?? $wo->type;

  $statusBadge = [
    WorkOrder::ST_OPEN       => 'bg-gray-100 text-gray-700',
    WorkOrder::ST_INPROGRESS => 'bg-sky-100 text-sky-700',
    WorkOrder::ST_DONE       => 'bg-emerald-100 text-emerald-700',
  ][$wo->status] ?? 'bg-gray-100 text-gray-700';

  $started = $wo->started_at ? Carbon::parse($wo->started_at) : null;
  $finished= $wo->finished_at ? Carbon::parse($wo->finished_at) : null;

  $dur = $wo->duration_minutes;
  if (!$dur && $started && $wo->status === WorkOrder::ST_INPROGRESS) {
    // estimasi durasi sementara (live) bila sedang berjalan
    $dur = now()->diffInMinutes($started);
  }

  function money_id($v) {
    return 'Rp ' . number_format((float)$v, 0, ',', '.');
  }

  $canAddItems = $wo->status !== WorkOrder::ST_DONE;
@endphp

<div class="space-y-4">
  {{-- Header --}}
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold">Detail Work Order</h1>
      <p class="text-sm text-gray-600">Kode: <span class="font-medium">{{ $wo->kode_wo }}</span></p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('wo.index') }}" class="rounded-xl bg-gray-100 px-3 py-2 text-sm hover:bg-gray-200">Kembali</a>
      <a href="{{ route('wo.edit',$wo->id) }}" class="rounded-xl bg-sky-600 text-white px-3 py-2 text-sm hover:bg-sky-700">Edit</a>
      <form method="post" action="{{ route('wo.destroy',$wo->id) }}" onsubmit="return confirm('Hapus WO ini?')">
        @csrf @method('DELETE')
        <button class="rounded-xl bg-rose-600 text-white px-3 py-2 text-sm hover:bg-rose-700">Hapus</button>
      </form>
    </div>
  </div>

  {{-- Ringkasan --}}
  <div class="bg-white rounded-2xl shadow p-3 md:p-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
      <div>
        <div class="text-gray-500">Jenis</div>
        <div>
          <span class="inline-flex items-center rounded-full text-xs px-2 py-0.5
            {{ $wo->type === WorkOrder::TYPE_CORR ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
            {{ $typeLabel }}
          </span>
        </div>
      </div>
      <div>
        <div class="text-gray-500">Status</div>
        <div class="inline-flex items-center rounded-full text-xs px-2 py-0.5 {{ $statusBadge }}">
          {{ ucfirst(strtolower(str_replace('_',' ', $wo->status))) }}
        </div>
      </div>
      <div>
        <div class="text-gray-500">Aset</div>
        <div class="font-medium">{{ $wo->asset->kode_aset ?? '—' }}</div>
        <div class="text-xs text-gray-500 truncate">{{ $wo->asset->nama ?? '—' }}</div>
      </div>
      <div>
        <div class="text-gray-500">Ticket</div>
        @if($wo->ticket)
          <div class="font-medium">{{ $wo->ticket->kode_tiket }}</div>
          <div class="text-xs text-gray-500 truncate">{{ $wo->ticket->judul }}</div>
        @else
          <div class="text-gray-700">—</div>
        @endif
      </div>

      <div>
        <div class="text-gray-500">Penanggung Jawab</div>
        <div class="font-medium">{{ $wo->assignee->name ?? '—' }}</div>
        @if(optional($wo->assignee)->divisi)
          <div class="text-xs text-gray-500">{{ $wo->assignee->divisi }}</div>
        @endif
      </div>
      <div>
        <div class="text-gray-500">Mulai</div>
        <div class="font-medium">{{ $started ? $started->format('d/m/Y H:i') : '—' }}</div>
      </div>
      <div>
        <div class="text-gray-500">Selesai</div>
        <div class="font-medium">{{ $finished ? $finished->format('d/m/Y H:i') : '—' }}</div>
      </div>
      <div>
        <div class="text-gray-500">Durasi</div>
        <div class="font-medium">
          @if(is_null($dur))
            —
          @else
            {{ floor($dur/60) }}j {{ $dur%60 }}m
          @endif
        </div>
      </div>
    </div>

    <div class="mt-3">
      <div class="text-gray-500 text-sm">Ringkasan Pekerjaan</div>
      <div class="mt-1 text-sm">{{ $wo->ringkasan_pekerjaan ?: '—' }}</div>
    </div>
  </div>

  {{-- Kontrol Status --}}
  <div class="bg-white rounded-2xl shadow p-3 md:p-4">
    <div class="flex flex-wrap items-center gap-2">
      @if($wo->status === WorkOrder::ST_OPEN)
        <form method="post" action="{{ route('wo.start', $wo->id) }}">
          @csrf
          <button class="rounded-xl bg-sky-600 text-white px-4 py-2 text-sm hover:bg-sky-700">Mulai WO</button>
        </form>
      @endif

      @if($wo->status === WorkOrder::ST_INPROGRESS)
        <form method="post" action="{{ route('wo.done', $wo->id) }}">
          @csrf
          <button class="rounded-xl bg-emerald-600 text-white px-4 py-2 text-sm hover:bg-emerald-700">Tandai Selesai</button>
        </form>
      @endif

      @if($wo->status === WorkOrder::ST_DONE)
        <span class="text-sm text-emerald-700 bg-emerald-50 rounded-xl px-3 py-2">WO telah selesai.</span>
      @endif
    </div>
  </div>

  {{-- Item Biaya (Mobile cards) --}}
  <div class="md:hidden space-y-3">
    <div class="flex items-center justify-between">
      <h2 class="text-base font-semibold">Item Biaya</h2>
      @if($canAddItems)
        <a href="#addItemForm" class="rounded-xl bg-gray-900 text-white px-3 py-2 text-sm">Tambah Item</a>
      @endif
    </div>

    @forelse($wo->items as $it)
      <div class="bg-white rounded-2xl shadow p-3">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="text-sm font-semibold truncate">{{ $it->item_name }}</div>
            <div class="text-[11px] text-gray-500">Qty: {{ rtrim(rtrim(number_format($it->qty,2,'.',''), '0'), '.') }} × {{ money_id($it->unit_cost) }}</div>
          </div>
          <div class="text-sm font-semibold">{{ money_id($it->total_cost) }}</div>
        </div>
        @if($canAddItems)
          <div class="mt-2 text-right">
            <form method="post" action="{{ route('wo.items.remove', [$wo->id, $it->id]) }}" onsubmit="return confirm('Hapus item ini?')">
              @csrf @method('DELETE')
              <button class="rounded-lg px-3 py-1 text-xs bg-rose-50 text-rose-700">Hapus</button>
            </form>
          </div>
        @endif
      </div>
    @empty
      <div class="bg-white rounded-2xl shadow p-6 text-center text-gray-600">Belum ada item biaya.</div>
    @endforelse
  </div>

  {{-- Item Biaya (Desktop table) --}}
  <div class="hidden md:block bg-white rounded-2xl shadow overflow-hidden">
    <div class="flex items-center justify-between p-3 border-b">
      <h2 class="text-base font-semibold">Item Biaya</h2>
      @if($canAddItems)
        <a href="#addItemForm" class="rounded-xl bg-gray-900 text-white px-3 py-2 text-sm">Tambah Item</a>
      @endif
    </div>
    <table class="min-w-full whitespace-nowrap">
      <thead class="bg-gray-50 text-xs uppercase text-gray-500">
        <tr>
          <th class="px-4 py-3 text-left">Nama Item</th>
          <th class="px-4 py-3 text-right">Qty</th>
          <th class="px-4 py-3 text-right">Unit Cost</th>
          <th class="px-4 py-3 text-right">Total</th>
          <th class="px-4 py-3 text-right">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse($wo->items as $it)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">{{ $it->item_name }}</td>
            <td class="px-4 py-3 text-right">{{ rtrim(rtrim(number_format($it->qty,2,'.',''), '0'), '.') }}</td>
            <td class="px-4 py-3 text-right">{{ money_id($it->unit_cost) }}</td>
            <td class="px-4 py-3 text-right">{{ money_id($it->total_cost) }}</td>
            <td class="px-4 py-3 text-right">
              @if($canAddItems)
                <form method="post" action="{{ route('wo.items.remove', [$wo->id, $it->id]) }}" onsubmit="return confirm('Hapus item ini?')">
                  @csrf @method('DELETE')
                  <button class="rounded-lg px-3 py-1 text-sm bg-rose-50 text-rose-700 hover:bg-rose-100">Hapus</button>
                </form>
              @else
                —
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-4 py-8 text-center text-gray-600">Belum ada item biaya.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Rekap Biaya --}}
  <div class="bg-white rounded-2xl shadow p-3 md:p-4">
    @php
      $subtotal = $wo->items->sum('total_cost');
      $grand    = $subtotal; // kalau ada pajak/biaya lain bisa ditambahkan
    @endphp
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
      <div class="text-sm text-gray-600">
        Subtotal item: <span class="font-medium">{{ money_id($subtotal) }}</span>
        @if($wo->cost_total && abs($wo->cost_total - $subtotal) > 0.009)
          <span class="ml-2 text-[11px] text-amber-700 bg-amber-50 px-2 py-0.5 rounded">Catatan: cost_total WO ({{ money_id($wo->cost_total) }}) tidak sinkron dengan subtotal item.</span>
        @endif
      </div>
      <div class="text-right">
        <div class="text-sm text-gray-600">Grand Total</div>
        <div class="text-2xl font-semibold">{{ money_id($grand) }}</div>
      </div>
    </div>
  </div>

  {{-- Form Tambah Item --}}
  @if($canAddItems)
  <div id="addItemForm" class="bg-white rounded-2xl shadow p-3 md:p-4">
    <h3 class="text-base font-semibold mb-3">Tambah Item Biaya</h3>
    @if ($errors->any())
      <div class="bg-rose-50 text-rose-700 rounded-xl p-3 mb-3">
        <div class="text-sm font-medium">Periksa input berikut:</div>
        <ul class="text-sm list-disc pl-5">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="post" action="{{ route('wo.items.add', $wo->id) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
      @csrf
      <div class="md:col-span-2">
        <label class="block text-xs text-gray-600 mb-1">Nama Item <span class="text-rose-600">*</span></label>
        <input type="text" name="item_name" value="{{ old('item_name') }}"
               class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500" placeholder="Contoh: Oli mesin / Bearing / Jasa teknisi" required>
      </div>
      <div>
        <label class="block text-xs text-gray-600 mb-1">Qty <span class="text-rose-600">*</span></label>
        <input type="number" name="qty" step="0.01" min="0" value="{{ old('qty', 1) }}"
               class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500" required>
      </div>
      <div>
        <label class="block text-xs text-gray-600 mb-1">Unit Cost <span class="text-rose-600">*</span></label>
        <input type="number" name="unit_cost" step="0.01" min="0" value="{{ old('unit_cost', 0) }}"
               class="w-full rounded-xl border-gray-300 focus:ring-sky-500 focus:border-sky-500" required>
      </div>
      <div class="md:col-span-4 flex justify-end">
        <button class="rounded-xl bg-gray-900 text-white px-4 py-2">Tambah Item</button>
      </div>
    </form>
  </div>
  @endif

</div>
@endsection
