<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeedVariety extends Model
{
    protected $fillable = [
        'variety_name',
        'seed_type',
        'season',
        'crop_type',
        'is_active',
    ];


    public function seedPreferences()
    {
        return $this->hasMany(SeedPreference::class);
    }
}