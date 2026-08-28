<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    protected $fillable = [
        'farmer_id',
        'd_a_personnel_id',
        'assisted_by_da_personnel',
        'reference_number',
        'payload',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'submitted_at' => 'datetime',
        'assisted_by_da_personnel' => 'boolean',
    ];

    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    public function daPersonnel()
    {
        return $this->belongsTo(DAPersonnel::class, 'd_a_personnel_id');
    }

    public function seedPreferences()
    {
        return $this->hasMany(SeedPreference::class);
    }
}