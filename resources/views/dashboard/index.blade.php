@extends('layouts.app')

@section('content')
{{-- HERO / Quick actions --}}
<div class="card">
  <div class="bar">
    <div>
      <div class="text-lg font-semibold">Ringkasan Hari Ini</div>
      <div class="text-sm text-gray-500">Lihat sekilas kondisi tiket dan lakukan tindakan penting.</div>
    </div>
    @if(!empty($quick))
      <div class="flex items-center gap-2">
        @foreach($quick as $q)
          <a href="{{ $q['route'] }}" class="btn {{ $q['style'] }}">{{ $q['label'] }}</a>
        @endforeach
      </div>
    @endif
  </div>
</div>

{{-- KARTU UTAMA: bahasanya manusiawi --}}
<div class="grid md:grid-cols-4 gap-3 mt-3">
  @foreach($cards as $c)
    <div class="card {{ !empty($c['tone']) ? 'tone-'.$c['tone'] : '' }}">
      <div class="text-xs opacity-80">{{ $c['title'] }}</div>
      <div class="text-3xl font-semibold mt-1">{{ $c['value'] }}</div>
      <div class="text-xs opacity-80 mt-1">{{ $c['hint'] }}</div>
    </div>
  @endforeach
</div>


{{-- LIST SINGKAT: tiket terbaru/prioritas --}}
<div class="card mt-3">
  <div class="bar mb-2">
    <div class="legend mb-2">
  <span><i class="ok"></i> Selesai</span>
  <span><i class="warn"></i> Jatuh Tempo Hari Ini</span>
  <span><i class="danger"></i> Overdue</span>
  <span><i class="info"></i> Lainnya</span>
</div>

    <div class="text-sm font-medium">Tiket Terbaru</div>
    <a href="{{ route('tickets.index') }}" class="btn btn-outline text-sm">Lihat semua</a>
  </div>

  @if($recent->isEmpty())
    <div class="text-sm text-gray-600 p-6 text-center">
      Belum ada tiket untuk ditampilkan.<br>
      <a class="btn btn-outline mt-2" href="{{ route('tickets.create') }}">Buat Tiket</a>
    </div>
  @else
    <div class="stack">
      @foreach($recent as $t)
@php
  $overdue = $t->sla_due_at && $t->sla_due_at->isPast() && !in_array($t->status, ['RESOLVED','CLOSED']);
  // tentukan tone untuk row: overdue=merah, due hari ini=kuning, resolved/closed=hijau, lainnya=info
  $toneRow = 'info';
  if ($overdue) {
    $toneRow = 'danger';
  } elseif ($t->sla_due_at && $t->sla_due_at->isToday() && !in_array($t->status,['RESOLVED','CLOSED'])) {
    $toneRow = 'warn';
  } elseif (in_array($t->status, ['RESOLVED','CLOSED'])) {
    $toneRow = 'ok';
  }
@endphp

<div class="border rounded-xl p-3 row-accent {{ $toneRow }}">
  <div class="bar">
    <div class="font-medium">
      <a href="{{ route('tickets.show',$t->id) }}" class="underline">{{ $t->kode_tiket }}</a>
      <span class="text-gray-500">— {{ $t->judul }}</span>
    </div>
    <div class="text-right">
      <div class="text-xs {{ $overdue ? 'text-red-600' : 'text-gray-500' }}">
        @if($t->sla_due_at)
          Deadline: {{ $t->sla_due_at->format('d/m H:i') }} @if($overdue) • lewat deadline @endif
        @else
          Deadline: —
        @endif
      </div>
      <div class="text-xs text-gray-500">
        {{ optional($t->created_at)->format('d/m H:i') }}
      </div>
    </div>
  </div>

  <div class="flex items-center gap-2 mt-2 flex-wrap">
    <span class="chip">{{ $t->kategori }}</span>
    <span class="chip ug-{{ $t->urgensi }}">{{ $t->urgensi }}</span>
    <span class="chip st-{{ $t->status }}">{{ $t->status }}</span>
    @if($t->divisi_pj)
      <span class="chip">Divisi: {{ $t->divisi_pj }}</span>
    @endif
    @if($t->assignee)
      <span class="chip">PJ: {{ $t->assignee->name }}</span>
    @endif
  </div>
</div>

      @endforeach
    </div>
  @endif
</div>
@endsection
