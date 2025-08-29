<?php

namespace App\Http\Controllers;

use App\Models\{Ticket, TicketComment, User};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketCommentController extends Controller
{
    // POST /tickets/{id}/comments
    public function store(Request $req, $id): JsonResponse
    {
        $user = $req->user();
        $ticket = Ticket::findOrFail($id);

        $data = $req->validate([
            'body' => 'required|string',
            'is_internal' => 'sometimes|boolean',
        ]);

        $isInternal = isset($data['is_internal']) ? (bool)$data['is_internal'] : false;

        // Akses komentar internal: hanya PJ (divisi sama) & Superadmin
        if ($isInternal) {
            if (!($user->role === User::ROLE_SUPERADMIN ||
                 ($user->role === User::ROLE_PJ && $user->divisi === $ticket->divisi_pj))) {
                return response()->json(['error' => 'Komentar internal hanya untuk PJ/Superadmin.'], 403);
            }
        } else {
            // Komentar publik: pelapor boleh, PJ divisi sama boleh
            if (!($user->id === $ticket->user_id ||
                  ($user->role === User::ROLE_PJ && $user->divisi === $ticket->divisi_pj) ||
                  $user->role === User::ROLE_SUPERADMIN)) {
                return response()->json(['error' => 'Tidak berwenang menambah komentar.'], 403);
            }
        }

        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user->id,
            'body'      => $data['body'],
            'is_internal' => $isInternal,
        ]);

        return response()->json([
            'message' => 'Komentar ditambahkan.',
            'data'    => $comment->load('user:id,name,role,divisi'),
        ], 201);
    }
}
