<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class MotortimeReminder extends Model
{
    use HasUlids;

    protected $fillable = [
        'flight_id',
    ];
}
