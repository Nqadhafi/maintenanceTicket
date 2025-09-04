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
public function peek($id)
{
    $asset = Asset::with([
        'category:id,nama',
        'location:id,nama',
        'vendor:id,nama',
    ])->findOrFail($id);

    $tickets = $asset->tickets()
        ->orderByDesc('created_at')
        ->limit(10)
        ->get(['id','kode_tiket','judul','status','urgensi','created_at','sla_due_at'])
        ->map(function($t){
            return [
                'id'         => $t->id,
                'kode'       => $t->kode_tiket,
                'judul'      => $t->judul,
                'status'     => $t->status,
                'urgensi'    => $t->urgensi,
                'created_at' => optional($t->created_at)->format('d/m/Y H:i'),
                'deadline'   => optional($t->sla_due_at)->format('d/m/Y H:i'),
                'url'        => route('tickets.show', $t->id),
            ];
        });

    // === PATCH: ambil judul dari tiket terkait ===
    $workOrders = method_exists($asset, 'workOrders')
        ? $asset->workOrders()
            ->with('ticket:id,judul')                    // judul dari tiket
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id','kode_wo','ticket_id','status','created_at'])
            ->map(function($w){
                return [
                    'id'         => $w->id,
                    'kode'       => $w->kode_wo ?? ('WO-'.$w->id),
                    'judul'      => optional($w->ticket)->judul ?? '-',  // diambil dari tiket
                    'status'     => $w->status ?? '-',
                    'created_at' => optional($w->created_at)->format('d/m/Y H:i'),
                    'url'        => $w->ticket_id ? route('tickets.show', $w->ticket_id) : '#',
                ];
            })
        : collect();

    return response()->json([
        'asset' => [
            'id'           => $asset->id,
            'kode_aset'    => $asset->kode_aset,
            'nama'         => $asset->nama,
            'kategori'     => optional($asset->category)->nama,
            'lokasi'       => optional($asset->location)->nama,
            'vendor'       => optional($asset->vendor)->nama,
            'status'       => $asset->status,
            'tanggal_beli' => optional($asset->tanggal_beli)->format('d/m/Y'),
        ],
        'tickets'     => $tickets,
        'work_orders' => $workOrders,
    ], 200);
}

public function print($id)
{
$asset = Asset::with([
    'category:id,nama',
    'location:id,nama',
    'vendor:id,nama',
    'tickets' => fn($q) => $q->orderByDesc('created_at')->limit(200),
    'workOrders' => fn($q) => $q->orderByDesc('created_at')->limit(200),
    'workOrders.ticket:id,judul', // <— tambahkan ini
])->findOrFail($id);

    return view('assets.print', compact('asset'));
}

}
