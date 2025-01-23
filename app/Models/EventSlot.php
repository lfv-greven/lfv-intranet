<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class EventSlot extends Model
{
    use HasUlids;

    protected $fillable = [
        'start_time',
        'max_participants',
    ];

    protected function casts()
    {
        return [
            'start_time' => 'datetime',
        ];
    }

    public function enrollments()
    {
        return $this->hasMany(EventEnrollment::class);
    }

    public function freeSeats(): Attribute
    {
        return new Attribute(
            get: fn() => max(0, $this->max_participants - $this->enrollments()->count()),
        );
    }
}
