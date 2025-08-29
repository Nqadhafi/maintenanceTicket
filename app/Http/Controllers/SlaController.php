<?php

namespace App\Http\Controllers;

use App\Models\SettingSla;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SlaController extends Controller
{
    // GET /settings/sla
    public function index(): JsonResponse
    {
        $rows = SettingSla::orderBy('divisi')->orderBy('urgensi')->get();
        return response()->json(['data' => $rows]);
    }

    // PUT /settings/sla  (opsional: bulk update)
    // Body: [{divisi, urgensi, target_duration_minutes}, ...]
    public function bulkUpdate(Request $req): JsonResponse
    {
        $items = $req->validate([
            '*.divisi' => ['required', Rule::in(['IT','PRODUKSI','GA'])],
            '*.urgensi' => ['required', Rule::in(['RENDAH','SEDANG','TINGGI','DARURAT'])],
            '*.target_duration_minutes' => ['required','integer','min:1'],
        ]);

        foreach ($items as $it) {
            SettingSla::updateOrCreate(
                ['divisi' => $it['divisi'], 'urgensi' => $it['urgensi']],
                ['target_duration_minutes' => $it['target_duration_minutes']]
            );
        }

        return response()->json(['message' => 'SLA diperbarui.']);
    }
}
