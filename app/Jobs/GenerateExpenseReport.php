<?php

namespace App\Jobs;

use App\External\Gotenberg;
use App\Models\Expense;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GenerateExpenseReport implements ShouldQueue
{
    use Queueable;

    public function __construct(public Expense $expense) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $expense = $this->expense;
        $gotenberg = app()->make(Gotenberg::class);

        // At first, generate expense report and import as page 1
        $report = $gotenberg->htmlToPdf(view('pdf.expense-report', compact('expense')));

        // Get expense mime type
        $expenseMimeType = Storage::mimeType($expense->receipt_filename);

        // If the file to append is a PDF, merge with the report
        if ($expenseMimeType === 'application/pdf') {
            $report = $gotenberg->merge([
                $report,
                Storage::read($expense->receipt_filename),
            ]);
        }

        // Output the new PDF
        $expense->expense_report_filename = 'expenses/'.$expense->id.'.pdf';
        Storage::put($expense->expense_report_filename, $report);
        $expense->save();
    }
}
