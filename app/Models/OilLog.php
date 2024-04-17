<?php

namespace App\Models;

use App\Casts\Uppercase;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OilLog extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'user_id',
        'pilot',
        'registration',
        'oil_level',
    ];

    protected function casts()
    {
        return [
            'registration' => Uppercase::class,
        ];
    }

    public function aircraft()
    {
        return $this->belongsTo(Aircraft::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
