@extends('layouts.app')

@section('content')
<div class="bg-white p-4 rounded-xl shadow relative">
  {{-- Loading overlay --}}
  <div id="loadingOverlay" class="loading-overlay hidden" aria-hidden="true">
    <div class="spinner" role="status" aria-label="Memproses..."></div>
    <div class="loading-text">Memproses...</div>
  </div>

  <div class="bar mb-2">
    <h2 class="text-lg font-semibold">Edit Profil</h2>
    <a href="{{ route('profile.show') }}" class="btn btn-outline">Kembali</a>
  </div>

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

  <form method="post" action="{{ route('profile.update') }}" class="js-block-on-submit grid gap-3">
    @csrf @method('put')

    <div class="grid md:grid-cols-2 gap-3">
      <div>
        <label class="block text-xs text-gray-600">Nama</label>
        <input name="name" class="field w-full" value="{{ old('name', $u->name) }}" required>
        @include('partials.field-error',['field'=>'name'])
      </div>
      <div>
        <label class="block text-xs text-gray-600">Email</label>
        <input name="email" type="email" class="field w-full" value="{{ old('email', $u->email) }}" required>
        <p class="text-[11px] text-gray-500 mt-1">Pastikan email aktif untuk reset password.</p>
        @include('partials.field-error',['field'=>'email'])
      </div>
      <div>
        <label class="block text-xs text-gray-600">No. WA (opsional)</label>
        <input name="no_wa" class="field w-full" value="{{ old('no_wa', $u->no_wa) }}" placeholder="081234567890">
        <p class="text-[11px] text-gray-500 mt-1">Format angka, mulai 0, 10–15 digit.</p>
        @include('partials.field-error',['field'=>'no_wa'])
      </div>

      <div>
        <label class="block text-xs text-gray-600">Peran</label>
        <div class="flex items-center gap-2">
          <span class="chip st-{{ $u->role ?? 'USER' }}">{{ $u->role ?? 'USER' }}</span>
          @if($u->divisi)
            <span class="chip tone-muted">Divisi: {{ $u->divisi }}</span>
          @endif
        </div>
        <p class="text-[11px] text-gray-500 mt-1">Peran & divisi hanya dapat diubah oleh admin.</p>
      </div>
    </div>

    <div class="flex gap-2">
      <button type="submit" class="btn btn-primary">Simpan</button>
      <a href="{{ route('profile.show') }}" class="btn btn-outline">Batal</a>
    </div>
  </form>
</div>

{{-- Loading/anti double submit --}}
<style>
  .loading-overlay{ position:absolute; inset:0; background:rgba(0,0,0,.35); display:flex; align-items:center; justify-content:center; flex-direction:column; border-radius:12px; z-index:20; }
  .loading-overlay.hidden{ display:none; }
  .spinner{ width:42px; height:42px; border-radius:9999px; border:4px solid #fff; border-top-color:transparent; animation:spin .8s linear infinite; }
  .loading-text{ margin-top:.5rem; color:#fff; font-weight:600 }
  @keyframes spin{ to{ transform:rotate(360deg) } }
  .form-blocked{ pointer-events: none; opacity: .85; }
  .form-blocked :where(button,[type="submit"]){ pointer-events:auto; }
</style>

<script>
(function(){
  const form = document.querySelector('form.js-block-on-submit');
  const overlay = document.getElementById('loadingOverlay');

  function blockForm(){
    if (form.dataset.submitting === '1') return;
    form.dataset.submitting = '1';
    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(btn=>{
      btn.disabled = true;
      btn.dataset.prevText = btn.innerHTML;
      btn.innerHTML = 'Menyimpan…';
    });
    form.classList.add('form-blocked');
    if (overlay){ overlay.classList.remove('hidden'); overlay.setAttribute('aria-hidden','false'); }
  }

  form?.addEventListener('submit', blockForm);
})();
</script>
@endsection
