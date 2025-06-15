<?php

namespace App\Http\Controllers;

use App\External\Gotenberg;
use App\Models\Department;
use Illuminate\Support\Facades\Cache;

class DepartmentController extends Controller
{
    public function teamDescriptions()
    {
        $pdf = Cache::remember('pdf:team-descriptions', 60 * 60, function () {
            $departments = Department::orderBy('name')->lazy();
            $gotenberg = app()->make(Gotenberg::class);

            return $gotenberg->htmlToPdf(view('pdf.department-descriptions', compact('departments')));
        });

        return response()->pdf($pdf, 'Teams.pdf');
    }
}
