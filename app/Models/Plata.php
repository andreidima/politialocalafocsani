<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plata extends Model
{
    use HasFactory;

    protected $table = 'plati';
    protected $guarded = [];

    public function path()
    {
        return "plati/{$this->id}";
    }

    /**
     * Get the tarif that owns the Plata
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tarif(): BelongsTo
    {
        return $this->belongsTo(Tarif::class, 'tarif_id');
    }
}
