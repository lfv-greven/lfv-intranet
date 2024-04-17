<?php

namespace App\Models;

use App\Casts\Uppercase;
use App\Enums\RefuelingType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refueling extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'gas_station_id',
        'aircraft_id',
        'date',
        'type',
        'buyer_name',
        'buyer_registration',
        'counter_reading',
        'amount',
        'comment',
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function ($refueling) {
            if ($refueling->type == RefuelingType::refueling) {
                $refueling->amount = -abs($refueling->amount);
            } elseif ($refueling->type == RefuelingType::filling) {
                $refueling->amount = abs($refueling->amount);
            }
        });
    }

    protected function casts()
    {
        return [
            'type' => RefuelingType::class,
            'date' => 'immutable_datetime',
            'buyer_registration' => Uppercase::class,
        ];
    }

    public function gasStation()
    {
        return $this->belongsTo(GasStation::class);
    }

    public function aircraft()
    {
        return $this->belongsTo(Aircraft::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
