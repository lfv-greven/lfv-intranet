<?php

namespace App\Jobs;

use App\Models\Expense;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Image\Image;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class CompressExpenseImage implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Expense $expense)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $expense = $this->expense;

        // Only applicable if receipt is an image, so get mime type first
        $expenseMimeType = Storage::mimeType($expense->receipt_filename);

        // Early exit if not an image
        if (! Str::contains($expenseMimeType, 'image')) {
            return;
        }

        $dir = TemporaryDirectory::make()->deleteWhenDestroyed();
        $originalImagePath = $dir->path(basename($expense->receipt_filename));
        $compressedImagePath = $dir->path('image.jpg');
        file_put_contents($originalImagePath, Storage::read($expense->receipt_filename));

        // Load image
        $image = Image::load($originalImagePath);
        $image->width(1600);
        $image->quality(90);
        $image->save($compressedImagePath);

        // Store back the manipulated image
        $newStorageFilename = Str::replaceLast(pathinfo($expense->receipt_filename, PATHINFO_EXTENSION), 'jpg', $expense->receipt_filename);
        Storage::put($expense->receipt_filename, file_get_contents($compressedImagePath));
        Storage::move($expense->receipt_filename, $newStorageFilename);
        $expense->receipt_filename = $newStorageFilename;
        $expense->save();
    }
}
