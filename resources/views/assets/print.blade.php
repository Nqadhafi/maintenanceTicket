<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Cetak Aset {{ $asset->kode_aset }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    *{box-sizing:border-box} body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Inter,Arial,sans-serif;margin:0;padding:24px;color:#111827}
    h1{font-size:20px;margin:0 0 6px} .muted{color:#6b7280}
    .row{display:flex;gap:24px;flex-wrap:wrap}
    .card{border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-top:16px}
    table{width:100%;border-collapse:collapse;font-size:12px}
    th,td{padding:8px;border-bottom:1px solid #e5e7eb;text-align:left}
    .meta dt{font-weight:600}.meta dd{margin:0 0 8px}
    @media print {.no-print{display:none}}
  </style>
</head>
<body>
  <div class="no-print" style="display:flex;gap:8px;margin-bottom:12px">
    <button onclick="window.print()" style="padding:8px 12px;border:1px solid #111;border-radius:10px;background:#111;color:#fff">Cetak</button>
    <a href="{{ route('assets.index') }}" style="padding:8px 12px;border:1px solid #e5e7eb;border-radius:10px;text-decoration:none;color:#111">Kembali</a>
  </div>

  <h1>{{ $asset->kode_aset }} — {{ $asset->nama }}</h1>
  <div class="muted">Kategori: {{ optional($asset->category)->nama ?: '—' }} • Lokasi: {{ optional($asset->location)->nama ?: '—' }} • Vendor: {{ optional($asset->vendor)->nama ?: '—' }} • Status: {{ $asset->status }}</div>

  <div class="row">
    <div class="card" style="flex:1 1 300px">
      <h2 style="font-size:16px;margin:0 0 8px">Detail Aset</h2>
      <dl class="meta">
        <dt>Kode Aset</dt><dd>{{ $asset->kode_aset }}</dd>
        <dt>Nama</dt><dd>{{ $asset->nama }}</dd>
        <dt>Kategori</dt><dd>{{ optional($asset->category)->nama ?: '—' }}</dd>
        <dt>Lokasi</dt><dd>{{ optional($asset->location)->nama ?: '—' }}</dd>
        <dt>Vendor</dt><dd>{{ optional($asset->vendor)->nama ?: '—' }}</dd>
        <dt>Status</dt><dd>{{ $asset->status }}</dd>
        <dt>Tanggal Beli</dt><dd>{{ optional($asset->tanggal_beli)->format('d/m/Y') ?: '—' }}</dd>
      </dl>
    </div>

    <div class="card" style="flex:2 1 480px">
      <h2 style="font-size:16px;margin:0 0 8px">Spesifikasi</h2>
      @php $spec = $asset->spesifikasi ?? []; @endphp
      @if(is_array($spec) && count($spec))
        <table>
          <thead><tr><th style="width:220px">Kunci</th><th>Nilai</th></tr></thead>
          <tbody>
          @foreach($spec as $k=>$v)
            <tr><td>{{ $k }}</td><td>{{ is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v }}</td></tr>
          @endforeach
          </tbody>
        </table>
      @else
        <div class="muted">Tidak ada spesifikasi.</div>
      @endif
    </div>
  </div>

  <div class="card">
    <h2 style="font-size:16px;margin:0 0 8px">Riwayat Tiket</h2>
    @if($asset->tickets->count())
      <table>
        <thead>
          <tr>
            <th style="width:120px">Kode</th>
            <th>Judul</th>
            <th style="width:110px">Status</th>
            <th style="width:110px">Urgensi</th>
            <th style="width:140px">Dibuat</th>
            <th style="width:140px">Deadline</th>
          </tr>
        </thead>
        <tbody>
          @foreach($asset->tickets as $t)
            <tr>
              <td>#{{ $t->kode_tiket }}</td>
              <td>{{ $t->judul }}</td>
              <td>{{ $t->status }}</td>
              <td>{{ $t->urgensi }}</td>
              <td>{{ optional($t->created_at)->format('d/m/Y H:i') }}</td>
              <td>{{ optional($t->sla_due_at)->format('d/m/Y H:i') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <div class="muted">Belum ada tiket.</div>
    @endif
  </div>

@if(method_exists($asset,'workOrders'))
  <div class="card">
    <h2 style="font-size:16px;margin:0 0 8px">Riwayat Maintenance/WO</h2>
    @if($asset->workOrders->count())
      <table>
        <thead>
          <tr>
            <th style="width:120px">Kode</th>
            <th>Judul (dari Tiket)</th>
            <th style="width:110px">Status</th>
            <th style="width:140px">Dibuat</th>
          </tr>
        </thead>
        <tbody>
          @foreach($asset->workOrders as $w)
            <tr>
              <td>{{ $w->kode_wo ?? ('WO-'.$w->id) }}</td>
              <td>{{ optional($w->ticket)->judul ?? '—' }}</td>
              <td>{{ $w->status ?? '—' }}</td>
              <td>{{ optional($w->created_at)->format('d/m/Y H:i') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @else
      <div class="muted">Belum ada work order.</div>
    @endif
  </div>
@endif
    </div>
</body>
</html>
