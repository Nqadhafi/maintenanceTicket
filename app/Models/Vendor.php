<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $fillable = ['nama','kontak','no_wa','alamat','catatan'];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
