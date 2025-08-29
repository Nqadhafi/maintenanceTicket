@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded-xl shadow">
  <div class="flex items-center justify-between mb-3">
    <h2 class="text-lg font-semibold">Manajemen User</h2>
    <a href="{{ route('admin.users.create') }}" class="px-3 py-2 rounded-lg bg-black text-white text-sm">Tambah User</a>
  </div>

  <form method="get" class="grid gap-2 md:grid-cols-4">
    <input class="border rounded-lg p-2" name="q" placeholder="Cari nama/email/WA" value="{{ $filters['q'] }}">
    <select class="border rounded-lg p-2" name="role">
      <option value="">Role (semua)</option>
      @foreach ($roles as $r)
        <option value="{{ $r }}" @selected($filters['role']===$r)>{{ $r }}</option>
      @endforeach
    </select>
    <select class="border rounded-lg p-2" name="divisi">
      <option value="">Divisi (semua)</option>
      @foreach ($divisi as $d)
        <option value="{{ $d }}" @selected($filters['divisi']===$d)>{{ $d }}</option>
      @endforeach
    </select>
    <select class="border rounded-lg p-2" name="aktif">
      <option value="">Status (semua)</option>
      <option value="1" @selected($filters['aktif']==='1')>AKTIF</option>
      <option value="0" @selected($filters['aktif']==='0')>NONAKTIF</option>
    </select>
    <div class="md:col-span-4">
      <button class="px-3 py-2 rounded-lg bg-gray-900 text-white text-sm">Terapkan Filter</button>
    </div>
  </form>

  <div class="mt-4 overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left border-b">
          <th class="py-2 pr-2">Nama</th>
          <th class="py-2 pr-2">Email</th>
          <th class="py-2 pr-2">Role</th>
          <th class="py-2 pr-2">Divisi</th>
          <th class="py-2 pr-2">No. WA</th>
          <th class="py-2 pr-2">Aktif</th>
          <th class="py-2 pr-2">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($users as $u)
          <tr class="border-b">
            <td class="py-2 pr-2">{{ $u->name }}</td>
            <td class="py-2 pr-2">{{ $u->email }}</td>
            <td class="py-2 pr-2">{{ $u->role }}</td>
            <td class="py-2 pr-2">{{ $u->divisi }}</td>
            <td class="py-2 pr-2">{{ $u->no_wa }}</td>
            <td class="py-2 pr-2">
              <span class="px-2 py-1 rounded text-xs {{ $u->aktif ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                {{ $u->aktif ? 'AKTIF' : 'NONAKTIF' }}
              </span>
            </td>
            <td class="py-2 pr-2">
              <a href="{{ route('admin.users.edit',$u->id) }}" class="underline">Edit</a>
              @if(auth()->id() !== $u->id)
              <form method="post" action="{{ route('admin.users.toggle',$u->id) }}" class="inline-block ml-2">
                @csrf
                <button class="underline text-{{ $u->aktif ? 'red' : 'green' }}-600" onclick="return confirm('Ubah status user ini?')">
                  {{ $u->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
              </form>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="py-3 text-gray-500">Tidak ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection
