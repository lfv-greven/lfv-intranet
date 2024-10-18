<?php

namespace App\Models;

use App\Enums\ExpenseStatus;
use App\Events\ExpenseCreated;
use App\Events\ExpenseUpdated;
use App\Jobs\EnrichExpenseWithIban;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Storage;

class Expense extends Model
{
    use HasFactory;
    use HasUlids;
    use Prunable;

    protected $fillable = [
        'user_id',
        'reason',
        'receipt_filename',
    ];

    protected $dispatchesEvents = [
        'created' => ExpenseCreated::class,
        'updated' => ExpenseUpdated::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function (Expense $expense) {
            EnrichExpenseWithIban::dispatch($expense);
        });

        static::deleted(function (Expense $expense) {
            Storage::delete($expense->receipt_filename);
            Storage::delete($expense->expense_report_filename);
        });
    }

    protected function casts(): array
    {
        return [
            'status' => ExpenseStatus::class,
        ];
    }

    public function prunable()
    {
        return static::where('status', ExpenseStatus::REJECTED);
    }

    public function receiptUrl(): Attribute
    {
        return new Attribute(
            get: fn () => Storage::url($this->receipt_filename),
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
