<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationWebController extends Controller
{
    public function index()
    {
        $rows = Location::orderBy('nama')->paginate(20);
        return view('master.locations.index', compact('rows'));
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'nama' => 'required|string|max:120|unique:locations,nama',
            'detail' => 'nullable|string|max:255',
        ]);
        Location::create($data);
        return back()->with('ok','Lokasi dibuat.');
    }

    public function edit($id)
    {
        $row = Location::findOrFail($id);
        return view('master.locations.edit', compact('row'));
    }

    public function update(Request $req, $id)
    {
        $row = Location::findOrFail($id);
        $data = $req->validate([
            'nama' => 'required|string|max:120|unique:locations,nama,'.$row->id,
            'detail' => 'nullable|string|max:255',
        ]);
        $row->update($data);
        return redirect()->route('master.locations.index')->with('ok','Lokasi diperbarui.');
    }

    public function destroy($id)
    {
        $row = Location::findOrFail($id);
        $row->delete();
        return back()->with('ok','Lokasi dihapus.');
    }
}
