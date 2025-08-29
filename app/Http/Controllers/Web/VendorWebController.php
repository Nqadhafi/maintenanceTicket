<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorWebController extends Controller
{
    public function index()
    {
        $rows = Vendor::orderBy('nama')->paginate(20);
        return view('master.vendors.index', compact('rows'));
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'nama' => 'required|string|max:150|unique:vendors,nama',
            'kontak' => 'nullable|string|max:120',
            'no_wa' => 'nullable|string|max:30',
            'alamat' => 'nullable|string|max:255',
        ]);
        Vendor::create($data);
        return back()->with('ok','Vendor dibuat.');
    }

    public function edit($id)
    {
        $row = Vendor::findOrFail($id);
        return view('master.vendors.edit', compact('row'));
    }

    public function update(Request $req, $id)
    {
        $row = Vendor::findOrFail($id);
        $data = $req->validate([
            'nama' => 'required|string|max:150|unique:vendors,nama,'.$row->id,
            'kontak' => 'nullable|string|max:120',
            'no_wa' => 'nullable|string|max:30',
            'alamat' => 'nullable|string|max:255',
        ]);
        $row->update($data);
        return redirect()->route('master.vendors.index')->with('ok','Vendor diperbarui.');
    }

    public function destroy($id)
    {
        $row = Vendor::findOrFail($id);
        $row->delete();
        return back()->with('ok','Vendor dihapus.');
    }
}
