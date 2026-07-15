<?php

namespace App\Models;

use App\Casts\Uppercase;
use App\Enums\RefuelingType;
use App\Enums\VereinsfliegerPriority;
use App\Services\VereinsfliegerClient;
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
            // Make amount positive or negative, depending on type
            if ($refueling->type == RefuelingType::refueling) {
                $refueling->amount = -abs($refueling->amount);
            } elseif ($refueling->type == RefuelingType::filling) {
                $refueling->amount = abs($refueling->amount);
            }

            // Be sure to map refueling to a stored aircraft if possible
            if (blank($refueling->aircraft_id)) {
                // Double check if that aircraft does not exist locally
                $refueling->aircraft_id = Aircraft::whereRegistration($refueling->buyer_registration)->first()?->id;
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

    public function scopeRefueling($q)
    {
        $q->where('type', RefuelingType::refueling);
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

    public function isExported()
    {
        return filled($this->vf_exported_at);
    }

    /**
     * This methods checks if this sale is intended to be exported
     * as a sale.
     *
     * Reasons for not salable is e.g. aircraft is owned.
     */
    public function mayBeSold(): bool
    {
        // Only refuelings, not fillings
        if ($this->type != RefuelingType::refueling) {
            return false;
        }

        // No article linked
        if (! $this->gasStation?->vf_articleid) {
            return false;
        }

        // Is own aircraft
        if ($this->aircraft?->owned) {
            return false;
        }

        return true;
    }

    public function vfExport(): bool
    {
        throw_unless($this->mayBeSold(), 'Refueling is not intended to be sold');

        // In case this is already exported, just warn but not block the export.
        if ($this->isExported()) {
            Log::warning('Resending already exported sale', [
                'refueling' => $this->id,
            ]);
        }

        $comment = "Intranet-Vorgang: {$this->id}";
        if ($this->aircraft?->billing_memberid) {
            $comment .= " | Pilot: {$this->buyer_name}";
        }

        $client = app(VereinsfliegerClient::class);
        [$success, $status, $response] = $client->callWithRetry(VereinsfliegerPriority::LOW, function ($vf) use ($comment) {
            return $vf->InsertSale([
                'bookingdate' => $this->date->format('Y-m-d'),
                'articleid' => $this->gasStation->vf_articleid,
                'amount' => abs($this->amount),
                'callsign' => $this->buyer_registration,
                'memberid' => $this->aircraft?->billing_memberid ?? $this->user?->memberid,
                'counter' => $this->counter_reading,
                'comment' => $comment,
            ]);
        });

        if ($success) {
            $this->vf_exported_at = now();
            $this->save();

            Log::debug('Inserted VF sale', [
                'refueling' => $this->id,
                'response' => $response,
            ]);

            return true;
        }

        Log::error('Failed to insert refueling sale', [
            'http_status' => $status,
            'vf_response' => $response,
        ]);

        return false;
    }
}
