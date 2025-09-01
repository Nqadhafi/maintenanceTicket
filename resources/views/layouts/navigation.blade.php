<nav x-data="{ profileOpen: false }" class="bg-white border-b border-gray-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="h-16 flex items-center justify-between">
      <!-- Left: Hamburger (mobile) + Logo -->
      <div class="flex items-center gap-3">
        <!-- Hamburger: buka drawer global -->
        <button
          class="sm:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:bg-gray-100 focus-visible:outline-none"
          type="button"
          aria-label="Buka menu"
          aria-controls="app-drawer"
          onclick="(function(){var d=document.querySelector('.drawer'); if(d){ d.classList.add('active'); d.querySelector('.panel')?.focus(); }})();">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>

        <!-- Logo -->
        <a href="{{ route('dashboard') }}" class="flex items-center">
          <x-application-logo class="block h-8 w-auto fill-current text-gray-700" />
        </a>
      </div>

      <!-- Center: Primary links (desktop only) -->
      <div class="hidden sm:flex items-center gap-1">
        @php $active = request()->routeIs('dashboard'); @endphp
        <a href="{{ route('dashboard') }}"
           class="px-3 py-2 rounded-md text-sm font-medium {{ $active ? 'text-gray-900 bg-gray-100' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}"
           aria-current="{{ $active ? 'page' : 'false' }}">
          Dashboard
        </a>
        {{-- Tambahkan link lain di sini bila perlu, tetap sedikit & jelas --}}
      </div>

      <!-- Right: User menu -->
      <div class="flex items-center">
        <div class="hidden sm:flex sm:items-center">
          <div class="relative" x-data>
            <button
              @click="profileOpen = !profileOpen"
              @keydown.escape.window="profileOpen=false"
              :aria-expanded="profileOpen"
              class="inline-flex items-center gap-2 px-3 py-2 rounded-md text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 focus-visible:outline-none"
              type="button">
              <!-- Avatar inisial -->
              <span class="inline-flex items-center justify-center h-7 w-7 rounded-full bg-gray-100 text-gray-700 text-xs font-semibold">
                {{ strtoupper(mb_substr(Auth::user()->name ?? 'U', 0, 1)) }}
              </span>
              <span class="hidden md:inline">{{ Auth::user()->name }}</span>
              <svg class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
              </svg>
            </button>

            <!-- Dropdown -->
            <div
              x-cloak
              x-show="profileOpen"
              x-transition.origin.top.right
              @click.away="profileOpen=false"
              class="absolute right-0 mt-2 w-48 bg-white border border-gray-100 rounded-lg shadow-lg z-50">
              <div class="py-2 text-sm">
                {{-- (Opsional) tambahkan item lain seperti "Profil" --}}
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button
                    type="submit"
                    class="w-full text-left px-3 py-2 hover:bg-gray-50 text-gray-700">
                    Logout
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- On mobile: tombol avatar kecil (buka dropdown sederhana) -->
        <div class="sm:hidden">
          <div class="relative" x-data="{ openM:false }">
            <button
              @click="openM=!openM"
              @keydown.escape.window="openM=false"
              class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-gray-100 text-gray-700 focus-visible:outline-none"
              aria-label="Menu pengguna">
              {{ strtoupper(mb_substr(Auth::user()->name ?? 'U', 0, 1)) }}
            </button>
            <div
              x-cloak
              x-show="openM"
              x-transition.origin.top.right
              @click.away="openM=false"
              class="absolute right-0 mt-2 w-40 bg-white border border-gray-100 rounded-lg shadow-lg z-50">
              <div class="py-2 text-sm">
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit" class="w-full text-left px-3 py-2 hover:bg-gray-50 text-gray-700">
                    Logout
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</nav>
