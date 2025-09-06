@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded-xl shadow md:-mx-6 lg:-mx-10 xl:-mx-16">
  <div class="max-w-none">
    <h2 class="text-lg font-semibold mb-1">Tambah User</h2>
    <p class="text-xs text-gray-500 mb-3">Isi data dasar akun. Untuk role <b>PJ</b>, wajib pilih divisi.</p>

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

    <form method="post" action="{{ route('admin.users.store') }}" class="grid gap-3">
      @csrf
<div class="grid gap-3">
      {{-- Grid 3 kolom di desktop, tetap 1 kolom di mobile --}}
      <div class="grid md:grid-cols-3 gap-3">
        <div>
          <label class="label">Nama</label>
          <input name="name" class="field @error('name') ring-2 ring-red-300 @enderror" value="{{ old('name') }}" required>
          @error('name') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="label">Email</label>
          <input name="email" type="email" class="field @error('email') ring-2 ring-red-300 @enderror" value="{{ old('email') }}" required>
          <p class="hint">Gunakan email aktif untuk reset password.</p>
          @error('email') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="label">No. WA (opsional)</label>
          <input name="no_wa" class="field @error('no_wa') ring-2 ring-red-300 @enderror" value="{{ old('no_wa') }}" placeholder="08xxxxxxxxxx">
          <p class="hint">Format angka saja, tanpa spasi/tanda. Contoh: 081234567890</p>
          @error('no_wa') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="label">Password</label>
          <input name="password" type="password" class="field @error('password') ring-2 ring-red-300 @enderror" required>
          <p class="hint">Min. 6 karakter. Gunakan kombinasi huruf & angka.</p>
          @error('password') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="label">Konfirmasi Password</label>
          <input name="password_confirmation" type="password" class="field" required>
        </div>

        <div>
          <label class="label">Role</label>
          <select name="role" id="role" class="field" required>
            @foreach (['SUPERADMIN','PJ','USER'] as $r)
              <option value="{{ $r }}" @selected(old('role')===$r)>{{ $r }}</option>
            @endforeach
          </select>
        </div>

        {{-- Divisi tampil hanya saat role = PJ --}}
        <div id="divisiBox" class="{{ old('role')==='PJ' ? '' : 'hidden' }}">
          <label class="label">Divisi (khusus PJ)</label>
          <select name="divisi" class="field">
            <option value="">—</option>
            @foreach (['IT','PRODUKSI','GA'] as $d)
              <option value="{{ $d }}" @selected(old('divisi')===$d)>{{ $d }}</option>
            @endforeach
          </select>
          @error('divisi') <div class="err">{{ $message }}</div> @enderror
        </div>

        <div class="flex items-center gap-2">
          <input type="checkbox" name="aktif" value="1" id="aktif" {{ old('aktif',1) ? 'checked' : '' }}>
          <label for="aktif" class="text-sm">Aktif</label>
        </div>
      </div>

      <div class="flex gap-2 pt-1">
        <button class="btn btn-primary">Simpan</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline">Batal</a>
      </div>
      </div>
    </form>
  </div>
</div>

<script>
  const role = document.getElementById('role');
  const box = document.getElementById('divisiBox');
  const update = () => box.classList.toggle('hidden', role.value !== 'PJ');
  role.addEventListener('change', update); update();
</script>
@endsection
