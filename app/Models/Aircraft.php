<?php

namespace App\Models;

use App\Casts\Uppercase;
use App\Enums\OilLevelType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aircraft extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'billing_memberid',
        'registration',
        'oil_level_type',
        'owned',
    ];

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope('sort', function ($q) {
            $q->orderBy('registration');
        });
    }

    protected function casts()
    {
        return [
            'registration' => Uppercase::class,
            'oil_level_type' => OilLevelType::class,
            'owned' => 'boolean',
        ];
    }

    public function oilLogs()
    {
        return $this->hasMany(OilLog::class);
    }

    public function getOilLevel(): float
    {
        return $this->oilLogs()->orderByDesc('created_at')->first()?->oil_level ?? 0;
    }

    public function scopeOwned($q)
    {
        $q->where('owned', true);
    }

    public function scopeForeign($q)
    {
        $q->where('owned', false);
    }
}
