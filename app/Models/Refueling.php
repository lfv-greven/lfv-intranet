<?php

namespace App\Models;

use App\Casts\Uppercase;
use App\Enums\RefuelingType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Refueling extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'user_id',
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
            'vf_exported_at' => 'immutable_datetime',
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

    public function previous()
    {
        return $this
            ->hasOne(static::class, 'gas_station_id', 'gas_station_id')
            ->where('date', '<', $this->date)
            ->orderByDesc('date');
    }

    public function isExported()
    {
        return filled($this->vf_exported_at);
    }

    public function vfExport(): bool
    {
        $vf = auth()->user()->vf();

        $comment = "Intranet-Vorgang: {$this->id}";
        if ($this->aircraft->billing_memberid) {
            $comment .= " | Pilot: {$this->buyer_name}";
        }

        $success = $vf->InsertSale([
            'bookingdate' => $this->date->format('Y-m-d'),
            'articleid' => $this->gasStation->vf_articleid,
            'amount' => abs($this->amount),
            'callsign' => $this->buyer_registration,
            'memberid' => $this->aircraft->billing_memberid ?? $this->user?->memberid,
            'counter' => $this->counter_reading,
            'comment' => $comment,
        ]);

        if ($success) {
            $this->vf_exported_at = now();
            $this->save();

            Log::debug('Inserted VF sale', [
                'refueling' => $this->id,
                'response' => $vf->GetResponse(),
            ]);

            return true;
        }

        Log::error('Failed to insert refueling sale', [
            'vf_response' => $vf->GetResponse(),
        ]);

        return false;
    }
}
