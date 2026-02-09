<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiSettlementFlight extends Model
{
    use HasUlids;

    protected $fillable = [
        'fi_settlement_id',
        'vf_flight_id',
        'fi_vf_uid',
        'fi_name',
        'flight_date',
        'departure_time',
        'arrival_time',
        'flighttime_minutes',
        'blocktime_minutes',
        'planetype',
        'callsign',
        'pilotname',
        'excluded_reason',
        'vf_workhour_id',
        'workhour_sent_at',
        'raw_payload',
    ];

    protected $casts = [
        'flight_date' => 'date',
        'raw_payload' => 'array',
        'workhour_sent_at' => 'datetime',
    ];

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(FiSettlement::class, 'fi_settlement_id');
    }
}
