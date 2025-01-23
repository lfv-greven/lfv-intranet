<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class EventEnrollment extends Model
{
    use HasUlids;

    protected $fillable = [
        'event_slot_id',
        'user_id',
    ];
}
