@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded-xl shadow">
  {{-- Header --}}
  <div class="flex items-center justify-between gap-3 flex-wrap mb-3">
    <div>
      <h2 class="text-lg font-semibold">Manajemen User</h2>
      <p class="text-xs text-gray-500">Kelola akun, peran, dan status aktif/nonaktif.</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('admin.users.create') }}" class="btn btn-primary text-sm px-3 py-2">Tambah User</a>
      @if(request()->query())
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline text-sm px-3 py-2">Reset</a>
      @endif
    </div>
  </div>

  {{-- Filter (compact + responsif) --}}
  <form method="get" class="grid gap-2 md:grid-cols-5">
    <input class="field md:col-span-2" name="q" placeholder="Cari nama / email / WA" value="{{ $filters['q'] }}" autofocus>
    <select class="field" name="role" aria-label="Filter Role">
      <option value="">Role (semua)</option>
      @foreach ($roles as $r)
        <option value="{{ $r }}" @selected($filters['role']===$r)>{{ $r }}</option>
      @endforeach
    </select>
    <select class="field" name="divisi" aria-label="Filter Divisi">
      <option value="">Divisi (semua)</option>
      @foreach ($divisi as $d)
        <option value="{{ $d }}" @selected($filters['divisi']===$d)>{{ $d }}</option>
      @endforeach
    </select>
    <select class="field" name="aktif" aria-label="Filter Status">
      <option value="">Status (semua)</option>
      <option value="1" @selected($filters['aktif']==='1')>AKTIF</option>
      <option value="0" @selected($filters['aktif']==='0')>NONAKTIF</option>
    </select>
    <div class="md:col-span-5">
      <button class="btn btn-primary">Terapkan</button>
    </div>
  </form>

  {{-- Chips ringkasan filter --}}
  @php
    $chips = [];
    if($filters['q']) $chips[] = ['q',$filters['q']];
    if($filters['role']) $chips[] = ['role',$filters['role']];
    if($filters['divisi']) $chips[] = ['divisi',$filters['divisi']];
    if(strlen((string)$filters['aktif'])) $chips[] = ['aktif', $filters['aktif']=='1'?'AKTIF':'NONAKTIF'];
  @endphp
  @if(count($chips))
    <div class="flex flex-wrap gap-2 mt-2">
      @foreach($chips as [$k,$v])
        <span class="chip">{{ $k }}: <b class="ml-1">{{ $v }}</b></span>
      @endforeach
    </div>
  @endif

  {{-- MOBILE CARDS --}}
  <div class="md:hidden mt-4 space-y-2">
    @forelse ($users as $u)
      <div class="p-3 border rounded-xl">
        <div class="flex items-start justify-between gap-2">
          <div>
            <div class="font-medium">{{ $u->name }}</div>
            <div class="text-xs text-gray-600">{{ $u->email }}</div>
            <div class="text-xs text-gray-500 mt-0.5">WA: {{ $u->no_wa ?: '—' }}</div>
            <div class="flex items-center gap-1 mt-2">
              <span class="chip">{{ $u->role }}</span>
              @if($u->divisi) <span class="chip">{{ $u->divisi }}</span> @endif
              <span class="chip {{ $u->aktif ? 'st-OK' : 'st-PENDING' }}">{{ $u->aktif ? 'AKTIF' : 'NONAKTIF' }}</span>
            </div>
          </div>
          <div class="text-right">
            <a href="{{ route('admin.users.edit',$u->id) }}" class="btn btn-outline text-xs">Edit</a>
            @if(auth()->id() !== $u->id)
              <form method="post" action="{{ route('admin.users.toggle',$u->id) }}" class="mt-1">
                @csrf
                <button class="btn text-xs {{ $u->aktif ? 'btn-danger' : 'btn-success' }}" onclick="return confirm('Ubah status user ini?')">
                  {{ $u->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
              </form>
            @endif
          </div>
        </div>
      </div>
    @empty
      <div class="text-sm text-gray-500">Tidak ada data.</div>
    @endforelse
  </div>

  {{-- DESKTOP TABLE --}}
  <div class="hidden md:block mt-4 overflow-x-auto">
    <table class="w-full text-sm table">
      <thead style="position:sticky;top:0;background:#fff;z-index:1">
        <tr class="text-left">
          <th class="py-2 pr-2">Nama</th>
          <th class="py-2 pr-2">Email</th>
          <th class="py-2 pr-2">Role • Divisi</th>
          <th class="py-2 pr-2">No. WA</th>
          <th class="py-2 pr-2">Status</th>
          <th class="py-2 pr-2">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($users as $u)
          <tr class="border-b">
            <td class="py-2 pr-2">{{ $u->name }}</td>
            <td class="py-2 pr-2">{{ $u->email }}</td>
            <td class="py-2 pr-2">
              <div class="flex items-center gap-1">
                <span class="chip">{{ $u->role }}</span>
                @if($u->divisi) <span class="chip">{{ $u->divisi }}</span> @endif
              </div>
            </td>
            <td class="py-2 pr-2">{{ $u->no_wa ?: '—' }}</td>
            <td class="py-2 pr-2">
              <span class="px-2 py-1 rounded text-xs {{ $u->aktif ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                {{ $u->aktif ? 'AKTIF' : 'NONAKTIF' }}
              </span>
            </td>
            <td class="py-2 pr-2 whitespace-nowrap">
              <a href="{{ route('admin.users.edit',$u->id) }}" class="underline">Edit</a>
              @if(auth()->id() !== $u->id)
                <form method="post" action="{{ route('admin.users.toggle',$u->id) }}" class="inline-block ml-2">
                  @csrf
                  <button class="underline {{ $u->aktif ? 'text-red-600' : 'text-green-600' }}" onclick="return confirm('Ubah status user ini?')">
                    {{ $u->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                  </button>
                </form>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="py-3 text-gray-500">Tidak ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination + info --}}
  <div class="mt-4 flex items-center justify-between flex-wrap gap-2">
    <div class="text-xs text-gray-500">
      Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} data
    </div>
    {{ $users->links() }}
  </div>
</div>
@endsection
