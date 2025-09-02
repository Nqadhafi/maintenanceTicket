<?php
// app/Http/Controllers/Web/AssetLookupWebController.php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request;

class AssetLookupController extends Controller
{
    public function __invoke(Request $req)
    {
        $limit = (int) $req->query('limit', 20);
        $limit = max(1, min(50, $limit));
        $kat   = $req->query('kategori'); // contoh: IT, PRODUKSI, GA, LAINNYA
        $q     = $req->query('q');

        $query = Asset::query()
            ->with([
                'category:id,nama',
                'location:id,nama',
                'vendor:id,nama',
            ])
            ->when($kat && $kat !== 'LAINNYA', function ($qq) use ($kat) {
                $qq->whereHas('category', function ($c) use ($kat) {
                    $c->where('nama', $kat);
                });
            })
            ->when($q, function ($qq) use ($q) {
                $like = '%'.$q.'%';
                $qq->where(function ($w) use ($like) {
                    $w->where('kode_aset', 'like', $like)
                      ->orWhere('nama', 'like', $like);
                });
            });

        $rows = $query
            ->orderBy('kode_aset')
            ->limit($limit)
            ->get(['id','kode_aset','nama','asset_category_id','location_id','vendor_id']);

        $data = $rows->map(function ($a) {
            return [
                'id'        => $a->id,
                'kode_aset' => $a->kode_aset,
                'nama'      => $a->nama,
                'kategori'  => optional($a->category)->nama,
                'lokasi'    => optional($a->location)->nama,
                'vendor'    => optional($a->vendor)->nama,
            ];
        });

        return response()->json(['data' => $data], 200);
    }
}
