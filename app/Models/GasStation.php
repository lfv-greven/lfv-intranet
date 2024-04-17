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

    public function getCurrentCounterReading(): int
    {
        return $this->refuelings()->orderByDesc('counter_reading')->first()?->counter_reading ?? 0;
    }
}
