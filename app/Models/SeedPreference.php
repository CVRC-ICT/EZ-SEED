<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeedPreference extends Model
{
    protected $fillable = [
        'survey_id',
        'seed_variety_id',
        'season',
        'seed_type',
        'preference_rank',
        'reason',
        'reason_category',
        'problems_encountered',
    ];


    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }


    public function seedVariety()
    {
        return $this->belongsTo(SeedVariety::class);
    }
}
