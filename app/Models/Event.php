<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasUlids;

    public function slots()
    {
        return $this->hasMany(EventSlot::class);
    }

    public function enrollment()
    {
        return $this
            ->hasOneThrough(EventEnrollment::class, EventSlot::class)
            ->where('user_id', auth()->id());
    }
}
