@extends('layouts.app')

@section('content')
<div class="card">
  {{-- Header + CTA --}}
  <div class="bar mb-3">
    <h2 class="text-lg font-semibold">Master: Vendor</h2>
    <div class="flex items-center gap-2">
      <a href="{{ route('master.vendors.index') }}" class="btn btn-outline text-sm">Refresh</a>
    </div>
  </div>

  {{-- Create form --}}
  <form method="post" action="{{ route('master.vendors.store') }}" class="grid md:grid-cols-4 gap-3 mb-4">
    @csrf
    <div>
      <label class="block text-xs text-gray-600">Nama Vendor</label>
      <input name="nama" value="{{ old('nama') }}" class="field" placeholder="Nama vendor" required>
      @includeWhen(View::exists('partials.field-error'), 'partials.field-error', ['field' => 'nama'])
    </div>
    <div>
      <label class="block text-xs text-gray-600">Kontak (opsional)</label>
      <input name="kontak" value="{{ old('kontak') }}" class="field" placeholder="Nama PIC / email">
      @includeWhen(View::exists('partials.field-error'), 'partials.field-error', ['field' => 'kontak'])
    </div>
    <div>
      <label class="block text-xs text-gray-600">No. WA (opsional)</label>
      <input name="no_wa" value="{{ old('no_wa') }}" class="field" placeholder="08xxxxxxxxxx">
      @includeWhen(View::exists('partials.field-error'), 'partials.field-error', ['field' => 'no_wa'])
    </div>
    <div>
      <label class="block text-xs text-gray-600">Alamat (opsional)</label>
      <input name="alamat" value="{{ old('alamat') }}" class="field" placeholder="Alamat lengkap">
      @includeWhen(View::exists('partials.field-error'), 'partials.field-error', ['field' => 'alamat'])
    </div>
    <div class="md:col-span-4">
      <button class="btn btn-primary">Tambah</button>
    </div>
  </form>

  {{-- List --}}
  <div class="overflow-x-auto">
    <table class="w-full text-sm table">
      <thead>
        <tr class="text-left">
          <th class="py-2 pr-2">Nama</th>
          <th class="py-2 pr-2">Kontak</th>
          <th class="py-2 pr-2">No. WA</th>
          <th class="py-2 pr-2">Alamat</th>
          <th class="py-2 pr-2 w-40">Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $r)
          <tr>
            <td class="py-2 pr-2 font-medium">{{ $r->nama }}</td>
            <td class="py-2 pr-2">
              @if($r->kontak)
                <span class="chip tone-muted">{{ $r->kontak }}</span>
              @else
                <span class="text-gray-400">—</span>
              @endif
            </td>
            <td class="py-2 pr-2">
              @if($r->no_wa)
                <div class="flex items-center gap-2">
                  <a class="underline" href="tel:{{ preg_replace('/\s+/', '', $r->no_wa) }}">{{ $r->no_wa }}</a>
                  <a class="underline" target="_blank"
                     href="https://wa.me/{{ ltrim(preg_replace('/\D/','',$r->no_wa),'0') ? '62'.ltrim(preg_replace('/\D/','',$r->no_wa),'0') : '' }}">
                    WhatsApp
                  </a>
                </div>
              @else
                <span class="text-gray-400">—</span>
              @endif
            </td>
            <td class="py-2 pr-2">
              <div class="truncate max-w-[260px]">{{ $r->alamat ?: '—' }}</div>
            </td>
            <td class="py-2 pr-2">
              <div class="flex items-center gap-2">
                <a href="{{ route('master.vendors.edit',$r->id) }}" class="btn btn-outline text-sm">Edit</a>
                <form method="post" action="{{ route('master.vendors.destroy',$r->id) }}"
                      onsubmit="return confirm('Hapus vendor ini?')">
                  @csrf @method('delete')
                  <button class="btn btn-danger text-sm">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="py-3 text-gray-500">Belum ada data.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $rows->links() }}</div>
</div>
@endsection
