<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileWebController extends Controller
{
    public function show(Request $req)
    {
        $u = $req->user();
        return view('profile.show', compact('u'));
    }

    public function edit(Request $req)
    {
        $u = $req->user();
        return view('profile.edit', compact('u'));
    }

    public function update(Request $req)
    {
        $u = $req->user();

        $data = $req->validate([
            'name'  => ['required','string','max:120'],
            'email' => ['required','email:rfc,dns', Rule::unique('users','email')->ignore($u->id)],
            'no_wa' => ['nullable','regex:/^0[0-9]{9,14}$/'], // angka, mulai 0, 10-15 digit
        ]);

        $u->name  = $data['name'];
        $u->email = $data['email'];
        $u->no_wa = $data['no_wa'] ?? null;
        $u->save();

        return redirect()->route('profile.show')->with('ok','Profil diperbarui.');
    }

    public function editPassword(Request $req)
    {
        $u = $req->user();
        return view('profile.password', compact('u'));
    }

    public function updatePassword(Request $req)
    {
        $req->validate([
            'current_password'      => ['required','current_password'],
            'password'              => ['required','string','min:6','confirmed'],
        ]);

        $u = $req->user();
        $u->password = Hash::make($req->password);
        $u->save();

        return redirect()->route('profile.show')->with('ok','Password berhasil diganti.');
    }
}
