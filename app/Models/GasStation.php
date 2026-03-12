<?php

namespace App\Models;

use App\Enums\FuelType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GasStation extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'name',
        'fuel_type',
        'capacity',
        'vf_articleid',
    ];

    protected function casts()
    {
        return [
            'fuel_type' => FuelType::class,
        ];
    }

    public function refuelings()
    {
        return $this->hasMany(Refueling::class);
    }

    public function scopeWithCurrentFilling($query)
    {
        return $query->withSum('refuelings as current_filling_amount', 'amount');
    }

    public function getCurrentFillingAttribute(): int
    {
        $filling = $this->getAttributeFromArray('current_filling_amount')
            ?? $this->refuelings()->sum('amount');

        return max(0, (int) round($filling));
    }

    public function getCurrentCounterReading(): int
    {
        return $this->refuelings()->orderByDesc('date')->first()?->counter_reading ?? 0;
    }
}
