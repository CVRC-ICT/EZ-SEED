<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveySyncLog extends Model
{
    protected $fillable = [
        'offline_uuid',
        'survey_id',
        'synced_at',
    ];

    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }
}