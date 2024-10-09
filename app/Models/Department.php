<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'name',
        'max_members',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function freeSeats(): Attribute
    {
        return new Attribute(
            get: fn () => max(0, $this->max_members - $this->users()->count()),
        );
    }
}
