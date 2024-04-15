<?php

namespace App\Models;

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

    public function refuelings()
    {
        return $this->hasMany(Refueling::class);
    }

    public function getCurrentCounterReading(): int
    {
        return $this->refuelings()->orderByDesc('date', 'created_at')->first()?->counter_reading ?? 0;
    }
}
