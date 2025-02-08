<?php

namespace App\Models;

use App\Notifications\Event\EnrollmentConfirmation;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class EventEnrollment extends Model
{
    use HasUlids;

    protected $fillable = [
        'event_slot_id',
        'user_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updated(function (EventEnrollment $eventEnrollment) {
            $eventEnrollment->user->notify(new EnrollmentConfirmation($eventEnrollment));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function slot()
    {
        return $this->belongsTo(EventSlot::class, 'event_slot_id');
    }

    public function event()
    {
        return $this->hasOneThrough(Event::class, EventSlot::class, 'id', 'id', 'event_slot_id', 'event_id');
    }
}
