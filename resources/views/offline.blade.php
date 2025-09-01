@extends('layouts.app')

@section('page_title','Sedang Offline')
@section('page_subtitle','Kamu tidak terhubung internet. Beberapa data mungkin tidak tampil.')

@section('content')
<div class="card tone-warn">
  <div class="text-sm">
    ⚠️ Aplikasi tidak bisa memuat data terbaru karena jaringan tidak tersedia.
    <ul class="list-disc ml-5 mt-2">
      <li>Kamu masih bisa membuka halaman yang pernah dibuka sebelumnya.</li>
      <li>Coba nyalakan koneksi, lalu tekan tombol di bawah.</li>
    </ul>
    <div class="mt-3 flex gap-2">
      <button class="btn btn-outline" onclick="location.reload()">Coba Muat Ulang</button>
      <a class="btn btn-brand" href="{{ route('dashboard') }}">Kembali ke Dashboard</a>
    </div>
  </div>
</div>
@endsection
