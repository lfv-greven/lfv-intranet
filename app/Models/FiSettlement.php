<?php

namespace App\Models;

use App\Enums\FiSettlementStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiSettlement extends Model
{
    use HasUlids;

    protected $fillable = [
        'period_from',
        'period_to',
        'status',
        'created_by',
        'settings',
        'started_at',
        'completed_at',
        'error_message',
    ];

    protected $casts = [
        'period_from' => 'date',
        'period_to' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'settings' => 'array',
        'status' => FiSettlementStatus::class,
    ];

    public function flights(): HasMany
    {
        return $this->hasMany(FiSettlementFlight::class);
    }
}
