@extends('layouts.guest')

@section('content')
<div class="card">
  <div class="mb-3">
    <div class="text-lg font-semibold">Masuk</div>
    <div class="text-sm text-gray-500">Gunakan email kantor dan kata sandi Anda.</div>
  </div>

  <form method="POST" action="{{ route('login') }}" id="loginForm" class="stack" novalidate>
    @csrf

    <div>
      <label for="email" class="block text-xs text-gray-600 mb-1">Email</label>
      <input id="email" type="email" name="email" class="field" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="nama@perusahaan.com">
      @includeIf('partials.field-error', ['field' => 'email'])
    </div>

    <div>
      <label for="password" class="block text-xs text-gray-600 mb-1">Kata Sandi</label>
      <div class="relative">
        <input id="password" type="password" name="password" class="field pr-24" required autocomplete="current-password" placeholder="••••••••">
        <button type="button" id="togglePwd" class="btn btn-outline absolute right-1 top-1 h-[38px] px-3">Lihat</button>
      </div>
      @includeIf('partials.field-error', ['field' => 'password'])
      <div class="hint mt-1">Jangan bagikan kata sandi kepada siapa pun.</div>
    </div>

    <div class="flex items-center justify-between text-sm">
      <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="remember" class="rounded" {{ old('remember') ? 'checked' : '' }}>
        Ingat saya
      </label>
      @if (Route::has('password.request'))
        <a class="underline" href="{{ route('password.request') }}">Lupa kata sandi?</a>
      @endif
    </div>

    <div class="grid gap-2">
      <button type="submit" class="btn btn-brand btn-block">Masuk</button>
      <a href="mailto:it@perusahaan.com" class="btn btn-outline btn-block">Butuh bantuan IT?</a>
    </div>
  </form>
</div>

<div class="text-center text-xs text-gray-500 mt-3">
  Tips: pasang aplikasi ke layar utama untuk akses cepat di ponsel.
</div>

<script>
  (function(){
    const pwd = document.getElementById('password');
    const btn = document.getElementById('togglePwd');
    if (btn && pwd) {
      btn.addEventListener('click', () => {
        const is = pwd.type === 'password';
        pwd.type = is ? 'text' : 'password';
        btn.textContent = is ? 'Sembunyikan' : 'Lihat';
        pwd.focus();
      });
    }
    const form = document.getElementById('loginForm');
    form?.addEventListener('submit', () => {
      const btn = form.querySelector('button[type="submit"]');
      if (btn) btn.disabled = true;
    });
  })();
</script>
@endsection
