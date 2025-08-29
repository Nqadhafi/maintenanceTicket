<?php

namespace App\Http\Controllers;

use App\Models\{Ticket, TicketAttachment, User};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketAttachmentController extends Controller
{
    // POST /tickets/{id}/attachments
    public function store(Request $req, $id): JsonResponse
    {
        $user = $req->user();
        $ticket = Ticket::findOrFail($id);

        // Pelapor boleh; PJ (divisi sama) & Superadmin boleh
        if (!($user->id === $ticket->user_id ||
              ($user->role === User::ROLE_PJ && $user->divisi === $ticket->divisi_pj) ||
              $user->role === User::ROLE_SUPERADMIN)) {
            return response()->json(['error' => 'Tidak berwenang unggah lampiran.'], 403);
        }

        $req->validate([
            'file' => 'required|file|max:5120|mimes:jpg,jpeg,png,mp4,pdf,doc,docx,xls,xlsx',
        ]);

        $file = $req->file('file');
        $path = $file->store('tickets/' . $ticket->id, 'public');

        $att = TicketAttachment::create([
            'ticket_id' => $ticket->id,
            'path' => $path,
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        // ✅ pakai facade-level url() untuk hindari warning Intelephense
        $publicUrl = Storage::url($path); // pastikan: php artisan storage:link

        return response()->json([
            'message' => 'Lampiran diunggah.',
            'data'    => $att,
            'url'     => $publicUrl,
        ], 201);
    }
}
