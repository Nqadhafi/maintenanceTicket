@extends('layouts.app')

@section('content')
<div class="card">

  {{-- Header + CTA --}}
  <div class="bar mb-3">
    <div>
      <h2 class="text-lg font-semibold">Laporan Tiket</h2>
      <div class="text-xs text-gray-500">
        Rentang: {{ optional($range['from'] ?? null)->format('d M Y') }} – {{ optional($range['to'] ?? null)->format('d M Y') }}
      </div>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('reports.tickets.export', request()->query()) }}" class="btn btn-outline text-sm">Export CSV</a>
    </div>
  </div>

  {{-- KPI Cards (lebih friendly) --}}
  <div class="grid grid-cols-2 md:grid-cols-6 gap-2 mb-3">
    @php
      $kpis = [
        ['label' => 'Total', 'value' => $summary['total'] ?? 0, 'hint' => 'Semua tiket dalam filter', 'tone'=>'info'],
        ['label' => 'Open', 'value' => $summary['status']['OPEN'] ?? 0, 'hint' => 'Belum diproses', 'tone'=>'warn'],
        ['label' => 'Assigned', 'value' => $summary['status']['ASSIGNED'] ?? 0, 'hint' => 'Sudah ditugaskan', 'tone'=>'info'],
        ['label' => 'In Progress', 'value' => $summary['status']['IN_PROGRESS'] ?? 0, 'hint' => 'Sedang dikerjakan', 'tone'=>'info'],
        ['label' => 'Resolved', 'value' => $summary['status']['RESOLVED'] ?? 0, 'hint' => 'Selesai teknis', 'tone'=>'ok'],
        ['label' => 'Closed', 'value' => $summary['status']['CLOSED'] ?? 0, 'hint' => 'Ditutup', 'tone'=>'ok'],
      ];
    @endphp

    @foreach ($kpis as $k)
      <div class="p-3 rounded-xl bg-white shadow-sm border hover:shadow transition">
        <div class="flex items-start justify-between">
          <div>
            <div class="text-xs text-gray-500">{{ $k['label'] }}</div>
            <div class="text-2xl font-semibold leading-tight">{{ number_format($k['value']) }}</div>
          </div>
          <div class="text-xs px-2 py-0.5 rounded-full {{ $k['tone']=='ok' ? 'bg-emerald-50 text-emerald-700' : ($k['tone']=='warn' ? 'bg-amber-50 text-amber-700' : 'bg-sky-50 text-sky-700') }}">
            {{ $k['hint'] }}
          </div>
        </div>
      </div>
    @endforeach

    <div class="p-3 rounded-xl bg-white shadow-sm border hover:shadow transition">
      <div class="text-xs text-gray-500">Due Today</div>
      <div class="text-2xl font-semibold">{{ number_format($summary['due_today'] ?? 0) }}</div>
      <div class="text-xs text-gray-500 mt-1">Deadline jatuh hari ini</div>
    </div>
    <div class="p-3 rounded-xl bg-white shadow-sm border hover:shadow transition">
      <div class="text-xs text-gray-500">Overdue</div>
      <div class="text-2xl font-semibold text-red-600">{{ number_format($summary['overdue'] ?? 0) }}</div>
      <div class="text-xs text-gray-500 mt-1">Melewati SLA</div>
    </div>
  </div>

  {{-- Filter sederhana + lanjutan (collapsible) --}}
  <form method="get" class="stack mb-3">
    <div class="grid gap-2 md:grid-cols-5">
      <input class="field md:col-span-2" name="q" placeholder="Cari kode / judul / deskripsi" value="{{ $filters['q'] }}">
      <input type="date" class="field" name="date_from" value="{{ $filters['date_from'] }}" placeholder="Dari">
      <input type="date" class="field" name="date_to" value="{{ $filters['date_to'] }}" placeholder="Sampai">
      <select class="field" name="status">
        <option value="">Status</option>
        @foreach ($status as $s)
          <option value="{{ $s }}" @selected($filters['status']===$s)>{{ $s }}</option>
        @endforeach
      </select>
    </div>

    <details class="mt-2">
      <summary class="cursor-pointer text-sm text-gray-600">Filter lanjutan</summary>
      <div class="grid gap-2 md:grid-cols-5 mt-2">
        <select class="field" name="kategori">
          <option value="">Kategori</option>
          @foreach ($kategori as $k)
            <option value="{{ $k }}" @selected($filters['kategori']===$k)>{{ $k }}</option>
          @endforeach
        </select>
        <select class="field" name="urgensi">
          <option value="">Prioritas</option>
          @foreach ($urgensi as $u)
            <option value="{{ $u }}" @selected($filters['urgensi']===$u)>{{ $u }}</option>
          @endforeach
        </select>
        <select class="field" name="divisi_pj">
          <option value="">Divisi PJ</option>
          @foreach ($divisi as $d)
            <option value="{{ $d }}" @selected($filters['divisi_pj']===$d)>{{ $d }}</option>
          @endforeach
        </select>
        <select class="field" name="assignee_id">
          <option value="">Penanggung Jawab</option>
          @foreach ($pjList as $u)
            <option value="{{ $u->id }}" @selected((string)$filters['assignee_id']===(string)$u->id)>{{ $u->name }} ({{ $u->divisi }})</option>
          @endforeach
        </select>
        <div></div>
      </div>
    </details>

    <div class="flex gap-2 mt-2">
      <button class="btn btn-primary">Terapkan</button>
      @if($filters['q'] || $filters['kategori'] || $filters['urgensi'] || $filters['status'] || $filters['divisi_pj'] || $filters['assignee_id'] || $filters['date_from'] || $filters['date_to'])
        <a href="{{ route('reports.tickets') }}" class="btn btn-outline">Reset</a>
      @endif
    </div>
  </form>

  {{-- Charts --}}
  <div class="grid grid-cols-1 xl:grid-cols-3 gap-3 mb-4">
    <div class="p-3 rounded-xl bg-white shadow-sm border">
      <div class="text-sm font-medium mb-2">Per Status</div>
      <canvas id="chartStatus" height="140"></canvas>
    </div>
    <div class="p-3 rounded-xl bg-white shadow-sm border xl:col-span-2">
      <div class="flex items-center justify-between mb-2">
        <div class="text-sm font-medium">Tren Tiket ({{ count($chart['trend']['labels'] ?? []) }} hari)</div>
        <div class="text-xs text-gray-500">Bar line—jumlah tiket per hari</div>
      </div>
      <canvas id="chartTrend" height="140"></canvas>
    </div>
  </div>

  {{-- MOBILE LIST (cards) --}}
  <div class="md:hidden stack mt-3">
    @forelse ($tickets as $t)
      @php
        $overdue = $t->sla_due_at && $t->sla_due_at->isPast() && !in_array($t->status, ['RESOLVED','CLOSED']);
        $toneRow = $overdue ? 'danger' : (in_array($t->status,['RESOLVED','CLOSED']) ? 'ok' : 'info');
      @endphp
      <div class="p-3 border rounded-xl row-accent {{ $toneRow }}">
        <div class="bar">
          <div class="font-medium">{{ $t->kode_tiket }}</div>
          <div class="flex items-center gap-1">
            <span class="chip ug-{{ $t->urgensi }}">{{ $t->urgensi }}</span>
            <span class="chip st-{{ $t->status }}">{{ $t->status }}</span>
          </div>
        </div>
        <div class="text-sm mt-1 line-clamp-2" title="{{ $t->judul }}">{{ $t->judul }}</div>
        <div class="text-xs text-gray-600 mt-1">
          {{ $t->kategori }} • {{ $t->divisi_pj }} • {{ optional($t->created_at)->format('d/m H:i') }}
        </div>
        <div class="text-xs text-gray-500 mt-1">
          • SLA: {{ optional($t->sla_due_at)->format('d/m H:i') ?? '—' }} @if($overdue) (lewat) @endif
        </div>
        <div class="text-xs text-gray-500 mt-1">
          Pelapor: {{ optional($t->pelapor)->name ?? '—' }} • PJ: {{ optional($t->assignee)->name ?? '—' }}
        </div>
        @if($t->closed_at)
          <div class="text-xs text-gray-500 mt-1">Closed: {{ $t->closed_at->format('d/m H:i') }}</div>
        @endif
      </div>
    @empty
      <div class="text-sm text-gray-500">Tidak ada data.</div>
    @endforelse
  </div>

  {{-- DESKTOP TABLE (ringkas + metrik) --}}
  <div class="hidden md:block mt-4 overflow-x-auto">
    <table class="w-full text-sm table">
      <thead style="position:sticky;top:0;background:#fff;z-index:1">
        <tr class="text-left">
          <th class="py-2 pr-2">Kode</th>
          <th class="py-2 pr-2">Dibuat</th>
          <th class="py-2 pr-2">Kategori • Divisi</th>
          <th class="py-2 pr-2">Prioritas • Status</th>
          <th class="py-2 pr-2">Judul</th>
          <th class="py-2 pr-2">Pelapor • PJ</th>

          <th class="py-2 pr-2">SLA / Closed</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($tickets as $t)
          @php
            $overdue = $t->sla_due_at && $t->sla_due_at->isPast() && !in_array($t->status, ['RESOLVED','CLOSED']);

          @endphp
          <tr>
            <td class="py-2 pr-2 font-medium">{{ $t->kode_tiket }}</td>
            <td class="py-2 pr-2 whitespace-nowrap">{{ optional($t->created_at)->format('d/m/Y H:i') }}</td>
            <td class="py-2 pr-2">
              <div>{{ $t->kategori }}</div>
              <div class="text-xs text-gray-500">{{ $t->divisi_pj }}</div>
            </td>
            <td class="py-2 pr-2">
              <div class="flex items-center gap-1">
                <span class="chip ug-{{ $t->urgensi }}">{{ $t->urgensi }}</span>
                <span class="chip st-{{ $t->status }}">{{ $t->status }}</span>
              </div>
            </td>
            <td class="py-2 pr-2"><div class="truncate max-w-[360px]" title="{{ $t->judul }}">{{ $t->judul }}</div></td>
            <td class="py-2 pr-2">
              <div class="truncate max-w-[220px]">{{ optional($t->pelapor)->name ?? '—' }}</div>
              <div class="text-xs text-gray-500 truncate max-w-[220px]">PJ: {{ optional($t->assignee)->name ?? '—' }}</div>
            </td>
            <td class="py-2 pr-2">
              <div class="text-xs {{ $overdue ? 'text-red-600' : 'text-gray-700' }}">
                SLA: {{ optional($t->sla_due_at)->format('d/m/Y H:i') ?? '—' }} @if($overdue) • lewat @endif
              </div>
              <div class="text-xs text-gray-500">Closed: {{ optional($t->closed_at)->format('d/m/Y H:i') ?? '—' }}</div>
            </td>
          </tr>
        @empty
          <tr><td colspan="9" class="py-3 text-gray-500">Tidak ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination --}}
  <div class="mt-4">{{ $tickets->links() }}</div>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Status Bar
  const statusCtx = document.getElementById('chartStatus');
  if (statusCtx) {
    new Chart(statusCtx, {
      type: 'bar',
      data: {
        labels: @json($chart['statusLabels'] ?? []),
        datasets: [{
          label: 'Jumlah',
          data: @json($chart['status'] ?? []),
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision:0 } } }
      }
    });
  }

  // Trend Line
  const trendCtx = document.getElementById('chartTrend');
  if (trendCtx) {
    new Chart(trendCtx, {
      type: 'line',
      data: {
        labels: @json($chart['trend']['labels'] ?? []),
        datasets: [{
          label: 'Tiket / hari',
          data: @json($chart['trend']['series'] ?? []),
          fill: false,
          tension: 0.2,
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision:0 } } }
      }
    });
  }
</script>
@endsection
