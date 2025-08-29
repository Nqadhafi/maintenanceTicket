<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{Asset, AssetCategory, Location, Vendor, User};
use Illuminate\Http\Request;

class AssetWebController extends Controller
{
    public function index(Request $req)
    {
        /** @var User $user */ $user = $req->user();

        $q = Asset::query()
            ->with(['category:id,nama', 'location:id,nama', 'vendor:id,nama'])
            ->when($req->query('q'), function ($qq, $v) {
                $like = '%'.$v.'%';
                $qq->where(function ($x) use ($like) {
                    $x->where('nama','like',$like)->orWhere('kode_aset','like',$like);
                });
            })
            ->when($req->query('category_id'), fn($qq,$v)=>$qq->where('asset_category_id',$v))
            ->when($req->query('location_id'), fn($qq,$v)=>$qq->where('location_id',$v))
            ->when($req->query('status'), fn($qq,$v)=>$qq->where('status',$v))
            ->orderBy('kode_aset');

        $assets = $q->paginate(15)->withQueryString();

        return view('assets.index', [
            'assets' => $assets,
            'filters' => [
                'q' => $req->query('q'),
                'category_id' => $req->query('category_id'),
                'location_id' => $req->query('location_id'),
                'status' => $req->query('status'),
            ],
            'categories' => AssetCategory::orderBy('nama')->get(['id','nama']),
            'locations'  => Location::orderBy('nama')->get(['id','nama']),
        ]);
    }

    public function create()
    {
        return view('assets.create', [
            'categories' => AssetCategory::orderBy('nama')->get(['id','nama']),
            'locations'  => Location::orderBy('nama')->get(['id','nama']),
            'vendors'    => Vendor::orderBy('nama')->get(['id','nama']),
        ]);
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'kode_aset' => 'required|string|max:100|unique:assets,kode_aset',
            'nama' => 'required|string|max:150',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'location_id' => 'nullable|exists:locations,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'spesifikasi' => 'required|string', // textarea JSON
            'status' => 'nullable|in:AKTIF,RUSAK,SCRAP',
            'tanggal_beli' => 'nullable|date',
        ]);

        // JSON decode ringkas (tanpa ribet)
        $json = json_decode($data['spesifikasi'], true);
        if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
            return back()->withInput()->withErrors(['spesifikasi'=>'Format JSON tidak valid.']);
        }
        $data['spesifikasi'] = $json;

        Asset::create($data);

        return redirect()->route('assets.index')->with('ok', 'Aset dibuat.');
    }

    public function edit($id)
    {
        $asset = Asset::findOrFail($id);
        return view('assets.edit', [
            'asset' => $asset,
            'categories' => AssetCategory::orderBy('nama')->get(['id','nama']),
            'locations'  => Location::orderBy('nama')->get(['id','nama']),
            'vendors'    => Vendor::orderBy('nama')->get(['id','nama']),
        ]);
    }

    public function update(Request $req, $id)
    {
        $asset = Asset::findOrFail($id);

        $data = $req->validate([
            'nama' => 'required|string|max:150',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'location_id' => 'nullable|exists:locations,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'spesifikasi' => 'required|string',
            'status' => 'nullable|in:AKTIF,RUSAK,SCRAP',
            'tanggal_beli' => 'nullable|date',
        ]);

        $json = json_decode($data['spesifikasi'], true);
        if ($json === null && json_last_error() !== JSON_ERROR_NONE) {
            return back()->withInput()->withErrors(['spesifikasi'=>'Format JSON tidak valid.']);
        }
        $data['spesifikasi'] = $json;

        $asset->update($data);

        return redirect()->route('assets.index')->with('ok','Aset diperbarui.');
    }

    public function destroy($id)
    {
        $asset = Asset::findOrFail($id);
        $asset->delete();

        return redirect()->route('assets.index')->with('ok','Aset dihapus.');
    }
}
