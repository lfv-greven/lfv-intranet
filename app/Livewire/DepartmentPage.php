<?php

namespace App\Livewire;

use App\Models\Department;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
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
            'department_note' => $user->department_note,
            'department_lead_interest' => $user->department_lead_interest,
        ]);

        // Only for later, for now changing is fully open
        $this->canChange = true;
        //        if ($user->department_id == null || ! $user->department_joined_at->isCurrentYear()) {
        //            $this->canChange = true;
        //        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Fieldset::make('')
                    ->columns(1)
                    ->schema([
                        Select::make('department_id')
                            ->placeholder('Wähle ein Team')
                            ->required()
                            ->disabled(fn () => ! $this->canChange)
                            ->helperText(fn () => $this->canChange ? '' : 'Das Ändern deines Team ist nur zum Jahresende möglich.')
                            ->label('Team')
                            ->disableOptionWhen(function (string $value) {
                                $department = Department::find($value);

                                return $department->free_seats < 1;
                            })
                            ->options(
                                Department::orderBy('name')->withCount('users')->get()->mapWithKeys(function (Department $department) {
                                    return [
                                        $department->id => sprintf(
                                            '%s (%s / %s)',
                                            $department->name,
                                            $department->users_count,
                                            $department->max_members,
                                        ),
                                    ];
                                })
                            ),
                        Textarea::make('department_note')
                            ->label('Besondere Hinweise')
                            ->hint('Wünsche, Interessen oder Qualifikationen.'),
                        Toggle::make('department_lead_interest')
                            ->label('Ich kann mir vorstellen, ein Team zu organisieren'),
                    ]),
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
        $user->department_note = $this->data['department_note'];
        $user->department_lead_interest = $this->data['department_lead_interest'];
        $user->department_joined_at = now();
        $user->save();

        Notification::make()
            ->success()
            ->title('Änderungen wurden gespeichert.')
            ->send();

        $this->canChange = false;
    }
}
