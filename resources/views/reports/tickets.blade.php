@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded-xl shadow">
  <div class="flex items-center justify-between mb-3">
    <h2 class="text-lg font-semibold">Laporan Tiket</h2>
    <a href="{{ route('reports.tickets.export', request()->query()) }}" class="px-3 py-2 rounded-lg border text-sm">Export CSV</a>
  </div>

  <form method="get" class="grid gap-2 md:grid-cols-6">
    <input class="border rounded-lg p-2" name="q" placeholder="Cari kode/judul/deskripsi" value="{{ $filters['q'] }}">
    <select class="border rounded-lg p-2" name="kategori">
      <option value="">Kategori</option>
      @foreach ($kategori as $k)
        <option value="{{ $k }}" @selected($filters['kategori']===$k)>{{ $k }}</option>
      @endforeach
    </select>
    <select class="border rounded-lg p-2" name="urgensi">
      <option value="">Prioritas</option>
      @foreach ($urgensi as $u)
        <option value="{{ $u }}" @selected($filters['urgensi']===$u)>{{ $u }}</option>
      @endforeach
    </select>
    <select class="border rounded-lg p-2" name="status">
      <option value="">Status</option>
      @foreach ($status as $s)
        <option value="{{ $s }}" @selected($filters['status']===$s)>{{ $s }}</option>
      @endforeach
    </select>
    <input type="date" class="border rounded-lg p-2" name="date_from" value="{{ $filters['date_from'] }}" placeholder="Dari">
    <input type="date" class="border rounded-lg p-2" name="date_to" value="{{ $filters['date_to'] }}" placeholder="Sampai">

    <select class="border rounded-lg p-2" name="divisi_pj">
      <option value="">Divisi PJ</option>
      @foreach ($divisi as $d)
        <option value="{{ $d }}" @selected($filters['divisi_pj']===$d)>{{ $d }}</option>
      @endforeach
    </select>
    <select class="border rounded-lg p-2" name="assignee_id">
      <option value="">Penanggung Jawab</option>
      @foreach ($pjList as $u)
        <option value="{{ $u->id }}" @selected((string)$filters['assignee_id']===(string)$u->id)>{{ $u->name }} ({{ $u->divisi }})</option>
      @endforeach
    </select>

    <div class="md:col-span-6">
      <button class="px-3 py-2 rounded-lg bg-black text-white text-sm">Terapkan Filter</button>
    </div>
  </form>

  <div class="mt-4 overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left border-b">
          <th class="py-2 pr-2">Kode</th>
          <th class="py-2 pr-2">Dibuat</th>
          <th class="py-2 pr-2">Kategori</th>
          <th class="py-2 pr-2">Divisi</th>
          <th class="py-2 pr-2">Urgensi</th>
          <th class="py-2 pr-2">Status</th>
          <th class="py-2 pr-2">Judul</th>
          <th class="py-2 pr-2">Pelapor</th>
          <th class="py-2 pr-2">PJ</th>
          <th class="py-2 pr-2">Deadline</th>
          <th class="py-2 pr-2">Closed</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($tickets as $t)
          <tr class="border-b">
            <td class="py-2 pr-2">{{ $t->kode_tiket }}</td>
            <td class="py-2 pr-2">{{ optional($t->created_at)->format('d/m/Y H:i') }}</td>
            <td class="py-2 pr-2">{{ $t->kategori }}</td>
            <td class="py-2 pr-2">{{ $t->divisi_pj }}</td>
            <td class="py-2 pr-2">{{ $t->urgensi }}</td>
            <td class="py-2 pr-2">{{ $t->status }}</td>
            <td class="py-2 pr-2">{{ $t->judul }}</td>
            <td class="py-2 pr-2">{{ optional($t->pelapor)->name }}</td>
            <td class="py-2 pr-2">{{ optional($t->assignee)->name }}</td>
            <td class="py-2 pr-2">{{ optional($t->sla_due_at)->format('d/m/Y H:i') }}</td>
            <td class="py-2 pr-2">{{ optional($t->closed_at)->format('d/m/Y H:i') }}</td>
          </tr>
        @empty
          <tr><td colspan="11" class="py-3 text-gray-500">Tidak ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $tickets->links() }}</div>
</div>
@endsection
