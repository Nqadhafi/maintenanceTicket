@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded-xl shadow">
  <h2 class="text-lg font-semibold mb-3">Edit User</h2>
  <form method="post" action="{{ route('admin.users.update',$user->id) }}" class="grid gap-3">
    @csrf @method('put')
    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-xs text-gray-600">Nama</label>
        <input name="name" class="border rounded-lg p-2 w-full" value="{{ old('name',$user->name) }}" required>
      </div>
      <div>
        <label class="block text-xs text-gray-600">Email</label>
        <input name="email" type="email" class="border rounded-lg p-2 w-full" value="{{ old('email',$user->email) }}" required>
      </div>
      <div>
        <label class="block text-xs text-gray-600">Password (kosongkan jika tidak diubah)</label>
        <input name="password" type="password" class="border rounded-lg p-2 w-full">
      </div>
      <div>
        <label class="block text-xs text-gray-600">Role</label>
        <select name="role" id="role" class="border rounded-lg p-2 w-full" required>
          @foreach (['SUPERADMIN','PJ','USER'] as $r)
            <option value="{{ $r }}" @selected(old('role',$user->role)===$r)>{{ $r }}</option>
          @endforeach
        </select>
      </div>
      <div id="divisiBox" class="hidden">
        <label class="block text-xs text-gray-600">Divisi (hanya untuk PJ)</label>
        <select name="divisi" class="border rounded-lg p-2 w-full">
          <option value="">—</option>
          @foreach (['IT','PRODUKSI','GA'] as $d)
            <option value="{{ $d }}" @selected(old('divisi',$user->divisi)===$d)>{{ $d }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs text-gray-600">No. WA (opsional)</label>
        <input name="no_wa" class="border rounded-lg p-2 w-full" value="{{ old('no_wa',$user->no_wa) }}">
      </div>
      <div class="flex items-center gap-2">
        <input type="checkbox" name="aktif" value="1" id="aktif" {{ old('aktif',$user->aktif) ? 'checked' : '' }}>
        <label for="aktif" class="text-sm">Aktif</label>
      </div>
    </div>
    <div class="flex gap-2">
      <button class="px-4 py-2 rounded-lg bg-black text-white text-sm">Simpan</button>
      <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-lg border text-sm">Batal</a>
    </div>
  </form>
</div>

<script>
  const role = document.getElementById('role');
  const box = document.getElementById('divisiBox');
  const update = () => box.classList.toggle('hidden', role.value !== 'PJ');
  role.addEventListener('change', update); update();
</script>
@endsection
