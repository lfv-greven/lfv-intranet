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

    public function retryableRejectedWorkHours(): HasMany
    {
        return $this->flights()
            ->where('excluded_reason', 'vf_rejected')
            ->whereNull('workhour_sent_at');
    }

    public function hasRetryableRejectedWorkHours(): bool
    {
        $loadedCount = $this->getAttribute('retryable_rejected_work_hours_count');

        if ($loadedCount !== null) {
            return (int) $loadedCount > 0;
        }

        return $this->retryableRejectedWorkHours()->exists();
    }

    public function queueRejectedWorkHourRetry(): bool
    {
        return self::query()
            ->whereKey($this->getKey())
            ->whereNotIn('status', [
                FiSettlementStatus::QUEUED->value,
                FiSettlementStatus::PROCESSING->value,
            ])
            ->whereHas('retryableRejectedWorkHours')
            ->update([
                'status' => FiSettlementStatus::QUEUED->value,
                'error_message' => null,
                'completed_at' => null,
                'updated_at' => $this->freshTimestamp(),
            ]) === 1;
    }
}
