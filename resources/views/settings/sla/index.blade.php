@extends('layouts.app')

@section('content')
<div class="card">
  {{-- Header + CTA --}}
  <div class="bar mb-3">
    <div>
      <h2 class="text-lg font-semibold">Atur Deadline per Divisi & Prioritas</h2>
      <div class="text-sm text-gray-500 mt-0.5">
        Tentukan target <strong>deadline (menit)</strong> untuk setiap kombinasi divisi × prioritas.
      </div>
    </div>
    <a href="{{ route('settings.sla.index') }}" class="btn btn-outline text-sm">Muat Ulang</a>
  </div>

  <form method="post" action="{{ route('settings.sla.update') }}">
    @csrf

    <div class="overflow-x-auto">
      <table class="w-full text-sm table">
        <thead>
          <tr class="text-left">
            <th class="py-2 pr-2">Divisi</th>
            @foreach ($urgensi as $u)
              <th class="py-2 pr-2">
                <span class="chip ug-{{ $u }}">{{ $u }}</span>
                <span class="text-xs text-gray-500 ml-1">(menit)</span>
              </th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach ($divisi as $d)
            <tr>
              <td class="py-2 pr-2 font-medium">{{ $d }}</td>
              @foreach ($urgensi as $u)
                <td class="py-2 pr-2">
                  <input
                    type="number"
                    min="0"
                    inputmode="numeric"
                    name="minutes[{{ $d }}][{{ $u }}]"
                    class="field w-32"
                    value="{{ old('minutes.'.$d.'.'.$u, $matrix[$d][$u]) }}"
                    aria-label="Deadline menit untuk {{ $d }} - {{ $u }}">
                </td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex items-center gap-2">
      <button class="btn btn-primary">Simpan Perubahan</button>
      <a href="{{ route('home') }}" class="btn btn-outline">Batal</a>
    </div>
  </form>

  <div class="mt-3 text-xs text-gray-500">
    Catatan:
    <ul class="list-disc ml-5 mt-1 space-y-1">
      <li>Angka adalah target <strong>deadline</strong> dalam menit (mis. 240 = 4 jam).</li>
      <li>Isi <code>0</code> untuk tanpa batas waktu <em>(tidak disarankan)</em>.</li>
      <li>Perubahan berlaku untuk tiket baru atau saat prioritas/divisi diperbarui.</li>
    </ul>
  </div>
</div>
@endsection
