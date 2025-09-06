<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserWebController extends Controller
{
    private array $roles  = ['SUPERADMIN','PJ','USER'];
    private array $divisi = ['IT','PRODUKSI','GA'];

    private function authorizeSuperadmin(): void
    {
        $u = auth()->user();
        if (!$u || ($u->role ?? null) !== 'SUPERADMIN') abort(403);
    }

    public function index(Request $req)
    {
        $this->authorizeSuperadmin();

        $q = User::query()
            ->when($req->query('q'), function($qq,$v){
                $like = '%'.$v.'%';
                $qq->where(function($x) use($like){
                    $x->where('name','like',$like)->orWhere('email','like',$like)->orWhere('no_wa','like',$like);
                });
            })
            ->when($req->query('role'), fn($qq,$v)=>$qq->where('role',$v))
            ->when($req->query('divisi'), fn($qq,$v)=>$qq->where('divisi',$v))
            ->when(strlen($req->query('aktif',''))>0, fn($qq,$v)=>$qq->where('aktif', (bool)$v))
            ->orderBy('name');

        $users = $q->paginate(20)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => [
                'q' => $req->query('q'),
                'role' => $req->query('role'),
                'divisi' => $req->query('divisi'),
                'aktif' => $req->query('aktif'),
            ],
            'roles'  => $this->roles,
            'divisi' => $this->divisi,
        ]);
    }

    public function create()
    {
        $this->authorizeSuperadmin();
        return view('admin.users.create', [
            'roles'  => $this->roles,
            'divisi' => $this->divisi,
        ]);
    }

    public function store(Request $req)
    {
        $this->authorizeSuperadmin();

        $data = $req->validate([
        'name'   => 'required|string|max:120',
        'email'  => 'required|email:rfc,dns|unique:users,email',
        'password' => 'required|string|min:6|confirmed', // <-- tambahkan confirmed
        'role'   => 'required|in:SUPERADMIN,PJ,USER',
        'divisi' => 'nullable|in:IT,PRODUKSI,GA',
        'no_wa'  => 'nullable|regex:/^0[0-9]{9,14}$/', // angka, mulai 0, 10-15 digit
        'aktif'  => 'sometimes|boolean',
        ]);

        // PJ wajib punya divisi
        if ($data['role'] === 'PJ' && empty($data['divisi'])) {
            return back()->withInput()->withErrors(['divisi'=>'PJ wajib memilih divisi.']);
        }

        User::create([
            'name'   => $data['name'],
            'email'  => $data['email'],
            'password' => Hash::make($data['password']),
            'role'   => $data['role'],
            'divisi' => $data['role']==='PJ' ? ($data['divisi'] ?? null) : null,
            'no_wa'  => $data['no_wa'] ?? null,
            'aktif'  => (bool)($data['aktif'] ?? true),
        ]);

        return redirect()->route('admin.users.index')->with('ok','User dibuat.');
    }

    public function edit($id)
    {
        $this->authorizeSuperadmin();
        $user = User::findOrFail($id);
        return view('admin.users.edit', [
            'user'   => $user,
            'roles'  => $this->roles,
            'divisi' => $this->divisi,
        ]);
    }

    public function update(Request $req, $id)
    {
        $this->authorizeSuperadmin();
        $user = User::findOrFail($id);

        $data = $req->validate([
        'name'   => 'required|string|max:120',
        'email'  => 'required|email:rfc,dns|unique:users,email,'.$user->id,
        'password' => 'nullable|string|min:6|confirmed', // <-- tambahkan confirmed
        'role'   => 'required|in:SUPERADMIN,PJ,USER',
        'divisi' => 'nullable|in:IT,PRODUKSI,GA',
        'no_wa'  => 'nullable|regex:/^0[0-9]{9,14}$/',
        'aktif'  => 'sometimes|boolean',
        ]);

        if ($data['role'] === 'PJ' && empty($data['divisi'])) {
            return back()->withInput()->withErrors(['divisi'=>'PJ wajib memilih divisi.']);
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) $user->password = Hash::make($data['password']);
        $user->role = $data['role'];
        $user->divisi = $data['role']==='PJ' ? ($data['divisi'] ?? null) : null;
        $user->no_wa = $data['no_wa'] ?? null;
        $user->aktif = (bool)($data['aktif'] ?? false);
        $user->save();

        return redirect()->route('admin.users.index')->with('ok','User diperbarui.');
    }

    public function toggle($id)
    {
        $this->authorizeSuperadmin();
        $user = User::findOrFail($id);
        $user->aktif = !$user->aktif;
        $user->save();
        return back()->with('ok', 'Status user diubah menjadi '.($user->aktif ? 'AKTIF' : 'NONAKTIF').'.');
    }
}
