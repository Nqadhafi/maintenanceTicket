@extends('layouts.app')

@section('content')
<div class="stack-lg">

  @if(session('ok'))
    <div id="successToast" class="success-toast" role="alert" aria-live="polite">
      <div class="toast-icon">✅</div>
      <div class="toast-text">{{ session('ok') }}</div>
    </div>
    <script>
      setTimeout(()=>{ const t=document.getElementById('successToast'); if(t){ t.classList.add('hide'); setTimeout(()=>t.remove(),300);}}, 1400);
    </script>
  @endif

  <div class="card">
    <div class="bar">
      <div class="min-w-0">
        <div class="text-xs text-gray-500">Profil</div>
        <h1 class="text-lg font-semibold truncate">{{ $u->name }}</h1>
      </div>

      {{-- Aksi kanan: Edit, Ganti Password, Logout --}}
      <div class="flex items-center gap-2">
        <a href="{{ route('profile.edit') }}" class="btn btn-brand">Edit Profil</a>
        <a href="{{ route('profile.password.edit') }}" class="btn btn-outline">Ganti Password</a>

        {{-- Logout harus POST + CSRF --}}
        <form method="POST" action="{{ route('logout') }}" class="inline js-logout-form"
              onsubmit="this.querySelector('button').disabled=true;">
          @csrf
          <button type="submit" class="btn btn-outline danger">Logout</button>
        </form>
      </div>
    </div>

    <div class="grid md:grid-cols-2 gap-3 text-sm mt-2">
      <div>
        <div class="text-gray-500">Nama</div>
        <div class="font-medium">{{ $u->name }}</div>
      </div>
      <div>
        <div class="text-gray-500">Email</div>
        <div class="font-medium">{{ $u->email }}</div>
      </div>
      <div>
        <div class="text-gray-500">No. WA</div>
        <div class="font-medium">{{ $u->no_wa ?: '—' }}</div>
      </div>
      <div>
        <div class="text-gray-500">Peran</div>
        <div class="flex items-center gap-2 flex-wrap">
          <span class="chip st-{{ $u->role ?? 'USER' }}">{{ $u->role ?? 'USER' }}</span>
          @if($u->divisi)
            <span class="chip tone-muted">Divisi: {{ $u->divisi }}</span>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.success-toast{
  position:fixed;left:50%;top:20px;transform:translateX(-50%);
  background:#111;color:#fff;padding:.6rem .9rem;border-radius:10px;display:flex;gap:.5rem;align-items:center;z-index:70;
  box-shadow:0 10px 20px rgba(0,0,0,.18);transition:opacity .25s ease, transform .25s ease;
}
.success-toast.hide{opacity:0;transform:translateX(-50%) translateY(-6px)}
.toast-icon{font-size:1rem}
.toast-text{font-size:.9rem}

/* Aksen kecil untuk tombol logout */
.btn.danger { border-color:#ef4444; color:#ef4444; }
.btn.danger:hover { background:#fef2f2; }
@media (max-width: 640px){
  /* rapikan bar di mobile */
  .bar { gap:.5rem; }
  .bar > div:last-child { flex-wrap:wrap; justify-content:flex-end; }
}
</style>

<script>
  // Cegah double-logout (tambahan proteksi, di samping onsubmit di form)
  document.querySelectorAll('.js-logout-form').forEach(f=>{
    f.addEventListener('submit', ()=>{
      const btn = f.querySelector('button[type="submit"]');
      if(btn){ btn.disabled = true; btn.textContent = 'Logout…'; }
    });
  });
</script>
@endsection
