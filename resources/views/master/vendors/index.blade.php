@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded-xl shadow">
  <div class="flex items-center justify-between mb-3">
    <h2 class="text-lg font-semibold">Master: Vendor</h2>
    <a href="{{ route('master.vendors.index') }}" class="underline">Refresh</a>
  </div>

  <form method="post" action="{{ route('master.vendors.store') }}" class="grid md:grid-cols-4 gap-3 mb-4">
    @csrf
    <input name="nama" class="border rounded-lg p-2 w-full" placeholder="Nama vendor" required>
    <input name="kontak" class="border rounded-lg p-2 w-full" placeholder="Kontak (opsional)">
    <input name="no_wa" class="border rounded-lg p-2 w-full" placeholder="No. WA (opsional)">
    <input name="alamat" class="border rounded-lg p-2 w-full" placeholder="Alamat (opsional)">
    <div class="md:col-span-4">
      <button class="px-3 py-2 rounded-lg bg-black text-white text-sm">Tambah</button>
    </div>
  </form>

  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead><tr class="text-left border-b">
        <th class="py-2 pr-2">Nama</th><th class="py-2 pr-2">Kontak</th><th class="py-2 pr-2">No. WA</th><th class="py-2 pr-2">Alamat</th><th class="py-2 pr-2">Aksi</th>
      </tr></thead>
      <tbody>
        @forelse($rows as $r)
          <tr class="border-b">
            <td class="py-2 pr-2">{{ $r->nama }}</td>
            <td class="py-2 pr-2">{{ $r->kontak }}</td>
            <td class="py-2 pr-2">{{ $r->no_wa }}</td>
            <td class="py-2 pr-2">{{ $r->alamat }}</td>
            <td class="py-2 pr-2">
              <a href="{{ route('master.vendors.edit',$r->id) }}" class="underline">Edit</a>
              <form method="post" action="{{ route('master.vendors.destroy',$r->id) }}" class="inline-block ml-2" onsubmit="return confirm('Hapus?')">
                @csrf @method('delete')
                <button class="underline text-red-600">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" class="py-3 text-gray-500">Belum ada data.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $rows->links() }}</div>
</div>
@endsection
