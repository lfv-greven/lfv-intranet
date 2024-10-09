<?php

namespace App\Livewire;

use App\Models\Department;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Attributes\Locked;
use Livewire\Component;

class DepartmentPage extends Component implements HasForms
{
    use InteractsWithForms;

    public $data = [];

    #[Locked]
    public $canChange = false;

    public function mount()
    {
        $user = auth()->user();

        $this->form->fill([
            'department_id' => $user->department_id,
        ]);

        if ($user->department_id == null || ! $user->department_joined_at->isCurrentYear()) {
            $this->canChange = true;
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->disabled(fn () => ! $this->canChange)
            ->schema([
                Select::make('department_id')
                    ->required()
                    ->label('Abteilung')
                    ->options(Department::orderBy('name')->get()->pluck('name', 'id')),
            ]);
    }

    public function render()
    {
        return view('livewire.department-page');
    }

    public function store()
    {
        $user = auth()->user();

        $user->department_id = $this->data['department_id'];
        $user->department_joined_at = now();
        $user->save();

        $this->canChange = false;
    }
}
