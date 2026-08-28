<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Farmer extends Model
{
    use HasFactory;

    protected $fillable = [
        'rsbsa_number',
        'source',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'birth_date',
        'sex',
        'civil_status',
        'contact_number',
        'email',
        'province_id',
        'municipality_id',
        'barangay_id',
        'farm_area',
        'tenurial_status',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'farm_area'  => 'decimal:2',
    ];

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    public function surveys()
    {
        return $this->hasMany(Survey::class);
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }

    public function getAgeGroupAttribute(): ?string
    {
        $age = $this->age;

        if ($age === null) {
            return null;
        }

        return match (true) {
            $age < 25  => 'Under 25',
            $age <= 34 => '25–34',
            $age <= 44 => '35–44',
            $age <= 54 => '45–54',
            $age <= 64 => '55–64',
            default    => '65+',
        };
    }

    protected function cleanPlace(?string $name): ?string
    {
        if (! $name) {
            return null;
        }

        return str_replace(
            ['ñ', 'Ñ'],
            ['n', 'N'],
            \App\Support\PlaceName::clean($name)
        );
    }

    public function getProvinceNameAttribute(): ?string
    {
        return $this->cleanPlace(
            $this->relationLoaded('province')
                ? optional($this->getRelation('province'))->name
                : optional($this->province()->first())->name
        );
    }

    public function getMunicipalityNameAttribute(): ?string
    {
        return $this->cleanPlace(
            $this->relationLoaded('municipality')
                ? optional($this->getRelation('municipality'))->name
                : optional($this->municipality()->first())->name
        );
    }

    public function getBarangayNameAttribute(): ?string
    {
        return $this->cleanPlace(
            $this->relationLoaded('barangay')
                ? optional($this->getRelation('barangay'))->name
                : optional($this->barangay()->first())->name
        );
    }
}