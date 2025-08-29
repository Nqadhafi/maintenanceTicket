@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded-xl shadow">
  <div class="flex items-center justify-between mb-3">
    <h2 class="text-lg font-semibold">Atur Deadline per Divisi & Prioritas</h2>
    <a href="{{ route('settings.sla.index') }}" class="text-sm underline">Refresh</a>
  </div>

  <form method="post" action="{{ route('settings.sla.update') }}">
    @csrf
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left border-b">
            <th class="py-2 pr-2">Divisi</th>
            @foreach ($urgensi as $u)
              <th class="py-2 pr-2">{{ $u }} (menit)</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach ($divisi as $d)
            <tr class="border-b">
              <td class="py-2 pr-2 font-medium">{{ $d }}</td>
              @foreach ($urgensi as $u)
                <td class="py-2 pr-2">
                  <input type="number" min="0"
                         name="minutes[{{ $d }}][{{ $u }}]"
                         class="border rounded-lg p-2 w-32"
                         value="{{ old('minutes.'.$d.'.'.$u, $matrix[$d][$u]) }}">
                </td>
              @endforeach
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-black text-white text-sm">Simpan</button>
      <a href="{{ route('home') }}" class="px-4 py-2 rounded-lg border text-sm">Batal</a>
    </div>
  </form>

  <div class="mt-3 text-xs text-gray-500">
    Catatan: Nilai = target **deadline** (menit) untuk kombinasi Divisi × Prioritas.
  </div>
</div>
@endsection
