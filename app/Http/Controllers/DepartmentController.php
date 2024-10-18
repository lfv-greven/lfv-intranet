<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Spatie\LaravelPdf\Facades\Pdf;

class DepartmentController extends Controller
{
    public function teamDescriptions()
    {
        $departments = Department::orderBy('name')->lazy();

        return Pdf::view('pdf.department-descriptions', compact('departments'))
            ->margins(20, 20, 20, 20)
            ->format('A4')
            ->name('Teams.pdf');
    }
}
