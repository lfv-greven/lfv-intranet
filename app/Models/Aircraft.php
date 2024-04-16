<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aircraft extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'registration',
    ];

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope('sort', function ($q) {
            $q->orderBy('registration');
        });
    }
}
