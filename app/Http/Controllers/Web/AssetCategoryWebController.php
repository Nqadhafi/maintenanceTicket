<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AssetCategory;
use Illuminate\Http\Request;

class AssetCategoryWebController extends Controller
{
    public function index()
    {
        $rows = AssetCategory::orderBy('nama')->paginate(20);
        return view('master.asset_categories.index', compact('rows'));
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'nama' => 'required|string|max:120|unique:asset_categories,nama',
            'deskripsi' => 'nullable|string|max:255',
        ]);
        AssetCategory::create($data);
        return back()->with('ok','Kategori dibuat.');
    }

    public function edit($id)
    {
        $row = AssetCategory::findOrFail($id);
        return view('master.asset_categories.edit', compact('row'));
    }

    public function update(Request $req, $id)
    {
        $row = AssetCategory::findOrFail($id);
        $data = $req->validate([
            'nama' => 'required|string|max:120|unique:asset_categories,nama,'.$row->id,
            'deskripsi' => 'nullable|string|max:255',
        ]);
        $row->update($data);
        return redirect()->route('master.asset_categories.index')->with('ok','Kategori diperbarui.');
    }

    public function destroy($id)
    {
        $row = AssetCategory::findOrFail($id);
        $row->delete();
        return back()->with('ok','Kategori dihapus.');
    }
}
