@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded-xl shadow md:-mx-6 lg:-mx-10 xl:-mx-16">
  <div class="max-w-none">
    <h2 class="text-lg font-semibold mb-1">Edit User</h2>
    <p class="text-xs text-gray-500 mb-3">Perbarui data akun. Kosongkan password bila tidak diubah.</p>

    @if (session('ok'))
      <div class="p-3 mb-3 rounded-lg bg-emerald-50 text-emerald-700 text-sm">{{ session('ok') }}</div>
    @endif
    @if ($errors->any())
      <div class="p-3 mb-3 rounded-lg bg-red-50 text-red-700 text-sm">
        <b>Periksa isian:</b>
        <ul class="list-disc pl-5">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="post" action="{{ route('admin.users.update',$user->id) }}" class="grid gap-3">
      @csrf @method('put')

      <div class="grid md:grid-cols-3 gap-3">
        <div>
          <label class="label">Nama</label>
          <input name="name" class="field @error('name') ring-2 ring-red-300 @enderror" value="{{ old('name',$user->name) }}" required>
          @error('name') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="label">Email</label>
          <input name="email" type="email" class="field @error('email') ring-2 ring-red-300 @enderror" value="{{ old('email',$user->email) }}" required>
          @error('email') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="label">No. WA (opsional)</label>
          <input name="no_wa" class="field @error('no_wa') ring-2 ring-red-300 @enderror" value="{{ old('no_wa',$user->no_wa) }}" placeholder="08xxxxxxxxxx">
          @error('no_wa') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="label">Password (biarkan kosong jika tidak diubah)</label>
          <input name="password" type="password" class="field @error('password') ring-2 ring-red-300 @enderror">
          @error('password') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="label">Konfirmasi Password (jika mengubah)</label>
          <input name="password_confirmation" type="password" class="field">
        </div>

        <div>
          <label class="label">Role</label>
          <select name="role" id="role" class="field" required>
            @foreach (['SUPERADMIN','PJ','USER'] as $r)
              <option value="{{ $r }}" @selected(old('role',$user->role)===$r)>{{ $r }}</option>
            @endforeach
          </select>
        </div>

        <div id="divisiBox" class="{{ old('role',$user->role)==='PJ' ? '' : 'hidden' }}">
          <label class="label">Divisi (khusus PJ)</label>
          <select name="divisi" class="field">
            <option value="">—</option>
            @foreach (['IT','PRODUKSI','GA'] as $d)
              <option value="{{ $d }}" @selected(old('divisi',$user->divisi)===$d)>{{ $d }}</option>
            @endforeach
          </select>
          @error('divisi') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="flex items-center gap-2">
          <input type="checkbox" name="aktif" value="1" id="aktif" {{ old('aktif',$user->aktif) ? 'checked' : '' }}>
          <label for="aktif" class="text-sm">Aktif</label>
        </div>
      </div>

      <div class="flex gap-2 pt-1">
        <button class="btn btn-primary">Simpan</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline">Batal</a>
      </div>
    </form>
  </div>
</div>

<style>
  .label{ @apply block text-xs text-gray-600 mb-1; }
  .field{ @apply border rounded-lg p-2 w-full; }
  .err{ @apply text-xs text-red-600 mt-1; }
</style>

<script>
  const role = document.getElementById('role');
  const box = document.getElementById('divisiBox');
  const update = () => box.classList.toggle('hidden', role.value !== 'PJ');
  role.addEventListener('change', update); update();
</script>
@endsection
