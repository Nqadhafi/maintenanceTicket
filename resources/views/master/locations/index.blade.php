@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded-xl shadow">
  <div class="flex items-center justify-between mb-3">
    <h2 class="text-lg font-semibold">Master: Lokasi</h2>
    <a href="{{ route('master.locations.index') }}" class="underline">Refresh</a>
  </div>

  <form method="post" action="{{ route('master.locations.store') }}" class="grid md:grid-cols-3 gap-3 mb-4">
    @csrf
    <input name="nama" class="border rounded-lg p-2 w-full" placeholder="Nama lokasi" required>
    <input name="detail" class="border rounded-lg p-2 w-full" placeholder="Detail (opsional)">
    <button class="px-3 py-2 rounded-lg bg-black text-white text-sm">Tambah</button>
  </form>

  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-left border-b"><th class="py-2 pr-2">Nama</th><th class="py-2 pr-2">Detail</th><th class="py-2 pr-2">Aksi</th></tr></thead>
      <tbody>
        @forelse($rows as $r)
          <tr class="border-b">
            <td class="py-2 pr-2">{{ $r->nama }}</td>
            <td class="py-2 pr-2">{{ $r->detail }}</td>
            <td class="py-2 pr-2">
              <a href="{{ route('master.locations.edit',$r->id) }}" class="underline">Edit</a>
              <form method="post" action="{{ route('master.locations.destroy',$r->id) }}" class="inline-block ml-2" onsubmit="return confirm('Hapus?')">
                @csrf @method('delete')
                <button class="underline text-red-600">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="3" class="py-3 text-gray-500">Belum ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $rows->links() }}</div>
</div>
@endsection
