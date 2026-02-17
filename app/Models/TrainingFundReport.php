<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingFundReport extends Model
{
    protected $fillable = [
        'month',
        'status',
        'started_at',
        'completed_at',
        'motor_ul_minutes',
        'motor_ul_amount',
        'winch_starts',
        'winch_amount',
        'tow_minutes',
        'tow_amount',
        'start_pauschale_quarterly_count',
        'start_pauschale_monthly_count',
        'start_pauschale_amount',
        'total_amount',
        'source_meta',
        'error_message',
    ];

    protected $casts = [
        'month' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'source_meta' => 'array',
        'status' => \App\Enums\TrainingFundReportStatus::class,
        'motor_ul_amount' => 'decimal:2',
        'winch_amount' => 'decimal:2',
        'tow_amount' => 'decimal:2',
        'start_pauschale_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];
}
