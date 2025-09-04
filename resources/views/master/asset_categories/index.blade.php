@extends('layouts.app')

@section('content')
<div class="card">
  {{-- Header + CTA --}}
  <div class="bar mb-3">
    <div>
      <h2 class="text-lg font-semibold">Master: Kategori Aset</h2>
      <div class="text-sm text-gray-500">Kelola grup kategori untuk mengorganisir aset.</div>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('master.asset_categories.index') }}" class="btn btn-outline text-sm">Refresh</a>
    </div>
  </div>

  {{-- Create form --}}
  <form method="post" action="{{ route('master.asset_categories.store') }}" class="grid gap-2 md:grid-cols-3 mb-3">
    @csrf
    <div class="md:col-span-1">
      <label class="block text-xs text-gray-600 mb-1">Nama Kategori</label>
      <input name="nama" class="field" placeholder="Contoh: IT / PRODUKSI / GA" required>
      @includeWhen($errors->has('nama'),'partials.field-error',['field'=>'nama'])
    </div>
    <div class="md:col-span-1">
      <label class="block text-xs text-gray-600 mb-1">Deskripsi (opsional)</label>
      <input name="deskripsi" class="field" placeholder="Keterangan singkat">
      @includeWhen($errors->has('deskripsi'),'partials.field-error',['field'=>'deskripsi'])
    </div>
    <div class="md:col-span-1 flex items-end">
      <button class="btn btn-primary w-full md:w-auto">Tambah</button>
    </div>
  </form>

  {{-- Desktop table --}}
  <div class="hidden md:block overflow-x-auto">
    <table class="w-full text-sm table">
      <thead>
        <tr class="text-left">
          <th class="py-2 pr-2">Nama</th>
          <th class="py-2 pr-2">Deskripsi</th>
          <th class="py-2 pr-2 w-40">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $r)
          <tr>
            <td class="py-2 pr-2 font-medium">{{ $r->nama }}</td>
            <td class="py-2 pr-2 text-gray-700">{{ $r->deskripsi }}</td>
            <td class="py-2 pr-2">
              <div class="flex items-center gap-2">
                <a href="{{ route('master.asset_categories.edit',$r->id) }}" class="btn btn-outline text-sm">Edit</a>
                <form method="post" action="{{ route('master.asset_categories.destroy',$r->id) }}"
                      onsubmit="return confirm('Hapus kategori ini?')">
                  @csrf @method('delete')
                  <button class="btn btn-danger text-sm">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="3" class="py-4 text-center text-gray-500">Belum ada data.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Mobile list --}}
  <div class="md:hidden stack">
    @forelse($rows as $r)
      <div class="border rounded-xl p-3">
        <div class="bar">
          <div class="font-semibold">{{ $r->nama }}</div>
          <div class="flex items-center gap-2">
            <a href="{{ route('master.asset_categories.edit',$r->id) }}" class="btn btn-outline text-sm">Edit</a>
            <form method="post" action="{{ route('master.asset_categories.destroy',$r->id) }}"
                  onsubmit="return confirm('Hapus kategori ini?')">
              @csrf @method('delete')
              <button class="btn btn-danger text-sm">Hapus</button>
            </form>
          </div>
        </div>
        @if($r->deskripsi)
          <div class="mt-1 text-xs text-gray-600">{{ $r->deskripsi }}</div>
        @endif
      </div>
    @empty
      <div class="text-sm text-gray-500">Belum ada data.</div>
    @endforelse
  </div>

  {{-- Pagination --}}
  <div class="mt-4">{{ $rows->links() }}</div>
</div>
@endsection
