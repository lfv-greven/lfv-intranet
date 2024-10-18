<?php

namespace App\Jobs;

use App\Models\Expense;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use setasign\Fpdi\Tcpdf\Fpdi;
use Spatie\TemporaryDirectory\TemporaryDirectory;

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
        $dir = TemporaryDirectory::make();
        $expenseReportPath = $dir->path('expense-report.pdf');
        $expenseReceiptPath = $dir->path(basename($expense->receipt_filename));

        // Configure PDF file
        $pdf = new Fpdi;
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // At first, generate expense report and import as page 1
        \Spatie\LaravelPdf\Facades\Pdf::view('pdf.expense-report', compact('expense'))
            ->format('A4')
            ->margins(2, 2, 2, 2, \Spatie\LaravelPdf\Enums\Unit::Centimeter)
            ->save($expenseReportPath);

        // Import expense report
        $pageCount = $pdf->setSourceFile($expenseReportPath);
        for ($i = 1; $i <= $pageCount; $i++) {
            $templateId = $pdf->importPage($i);
            $pdf->AddPage();
            $pdf->useTemplate($templateId);
        }

        // Download expense receipt
        file_put_contents($expenseReceiptPath, \Storage::read($expense->receipt_filename));

        // Get expense mime type
        $expenseMimeType = \Storage::mimeType($expense->receipt_filename);

        // If the file to append is an image
        if (str_contains($expenseMimeType, 'image')) {
            $pdf->AddPage();

            [$imgWidth, $imgHeight] = getimagesize($expenseReceiptPath);
            $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];

            // If the image is wider than the available page width, resize it
            if ($imgWidth > $pageWidth) {
                $scale = $pageWidth / $imgWidth; // Calculate the scale ratio
                $imgWidth = $pageWidth; // Set the image width to the page width
                $imgHeight = $imgHeight * $scale; // Scale the image height proportionally
            }

            // Add the image to the PDF
            $pdf->Image($expenseReceiptPath, '', '', $imgWidth, $imgHeight);
        }

        // If the file to append is a PDF
        if ($expenseMimeType === 'application/pdf') {
            $pageCountToAppend = $pdf->setSourceFile($expenseReceiptPath);
            for ($i = 1; $i <= $pageCountToAppend; $i++) {
                $templateId = $pdf->importPage($i);
                $pdf->AddPage();
                $pdf->useTemplate($templateId);
            }
        }

        // Output the new PDF
        $expense->expense_report_filename = 'expenses/'.$expense->id.'.pdf';
        \Storage::put($expense->expense_report_filename, $pdf->Output('', 'S'));
        $expense->save();

        // Cleanup temp directory
        $dir->delete();
    }
}
