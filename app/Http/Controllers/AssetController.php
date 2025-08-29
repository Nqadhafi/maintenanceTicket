<?php

namespace App\Http\Controllers;

use App\Models\{Asset, AssetCategory, Location, Vendor};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    // GET /assets?q=&category_id=&location_id=&status=
    public function index(Request $req): JsonResponse
    {
        $q = Asset::query()
            ->with(['category:id,nama','location:id,nama','vendor:id,nama'])
            ->when($req->query('q'), function ($qq, $v) {
                $like = '%'.$v.'%';
                $qq->where(function ($x) use ($like) {
                    $x->where('nama','like',$like)->orWhere('kode_aset','like',$like);
                });
            })
            ->when($req->query('category_id'), function ($qq, $v) { $qq->where('asset_category_id', $v); })
            ->when($req->query('location_id'), function ($qq, $v) { $qq->where('location_id', $v); })
            ->when($req->query('status'), function ($qq, $v) { $qq->where('status', $v); })
            ->orderBy('kode_aset');

        return response()->json(['data' => $q->paginate(20)]);
    }

    // POST /assets
    public function store(Request $req): JsonResponse
    {
        $data = $req->validate([
            'kode_aset' => 'required|string|max:100|unique:assets,kode_aset',
            'nama' => 'required|string|max:150',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'location_id' => 'nullable|exists:locations,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'spesifikasi' => 'required|array',
            'status' => 'nullable|in:AKTIF,RUSAK,SCRAP',
            'tanggal_beli' => 'nullable|date',
        ]);

        $asset = Asset::create($data);

        return response()->json([
            'message' => 'Aset dibuat.',
            'data' => $asset->load('category:id,nama','location:id,nama','vendor:id,nama'),
        ], 201);
    }

    // GET /assets/{id}
    public function show($id): JsonResponse
    {
        $asset = Asset::with(['category:id,nama','location:id,nama','vendor:id,nama'])->findOrFail($id);
        return response()->json(['data' => $asset]);
    }

    // PUT /assets/{id}
    public function update(Request $req, $id): JsonResponse
    {
        $asset = Asset::findOrFail($id);

        $data = $req->validate([
            'nama' => 'sometimes|string|max:150',
            'asset_category_id' => 'sometimes|exists:asset_categories,id',
            'location_id' => 'sometimes|nullable|exists:locations,id',
            'vendor_id' => 'sometimes|nullable|exists:vendors,id',
            'spesifikasi' => 'sometimes|array',
            'status' => 'sometimes|in:AKTIF,RUSAK,SCRAP',
            'tanggal_beli' => 'sometimes|nullable|date',
        ]);

        $asset->update($data);

        return response()->json([
            'message' => 'Aset diperbarui.',
            'data' => $asset->load('category:id,nama','location:id,nama','vendor:id,nama'),
        ]);
    }

    // DELETE /assets/{id}
    public function destroy($id): JsonResponse
    {
        $asset = Asset::findOrFail($id);
        $asset->delete();

        return response()->json(['message' => 'Aset dihapus.']);
    }
}
