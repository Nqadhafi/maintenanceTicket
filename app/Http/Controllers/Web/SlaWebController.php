<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SettingSla;
use Illuminate\Http\Request;

class SlaWebController extends Controller
{
    private array $divisi = ['IT','PRODUKSI','GA'];
    private array $urgensi = ['RENDAH','SEDANG','TINGGI','DARURAT'];

    public function index(Request $req)
    {
        $this->authorizeSuperadmin();

        // Ambil semua kombinasi, kalau belum ada buat tampilan default null
        $matrix = [];
        foreach ($this->divisi as $d) {
            foreach ($this->urgensi as $u) {
                $row = SettingSla::where('divisi',$d)->where('urgensi',$u)->first();
                $matrix[$d][$u] = $row ? $row->target_duration_minutes : null;
            }
        }

        return view('settings.sla.index', [
            'divisi' => $this->divisi,
            'urgensi' => $this->urgensi,
            'matrix' => $matrix,
        ]);
    }

    public function update(Request $req)
    {
        $this->authorizeSuperadmin();

        // Expect: minutes[DIVISI][URGENSI] = int
        $data = $req->input('minutes', []);
        foreach ($this->divisi as $d) {
            foreach ($this->urgensi as $u) {
                if (!isset($data[$d][$u])) continue;
                $val = $data[$d][$u];
                if ($val === '' || $val === null) continue;
                $minutes = (int)$val;
                if ($minutes < 0) $minutes = 0;

                SettingSla::updateOrCreate(
                    ['divisi'=>$d,'urgensi'=>$u],
                    ['target_duration_minutes'=>$minutes]
                );
            }
        }
        return redirect()->route('settings.sla.index')->with('ok','Aturan deadline diperbarui.');
    }

    private function authorizeSuperadmin(): void
    {
        $user = auth()->user();
        if (!$user || ($user->role ?? null) !== 'SUPERADMIN') {
            abort(403);
        }
    }
}
