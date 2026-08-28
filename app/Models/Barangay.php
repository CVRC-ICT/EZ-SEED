<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barangay extends Model
{
    protected $fillable = [
        'municipality_id',
        'name',
        'code',
    ];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }
}