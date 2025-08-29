<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAttachment extends Model
{
    protected $fillable = ['ticket_id','path','mime','size'];

    protected $casts = ['size' => 'integer'];

    public function ticket(): BelongsTo { return $this->belongsTo(Ticket::class); }
}
