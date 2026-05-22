<?php

namespace App\Livewire;

use App\Enums\OilLevelType;
use App\Models\Aircraft;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

class OilLogPage extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public $pilot;

    public $aircraft_id;

    public $oil_level;

    public $oil_refilled;

    #[Locked]
    public ?OilLevelType $oilLevelType = null;

    public function mount()
    {
        $this->form->fill([
            'oil_refilled' => 0,
        ]);
    }

    public function save()
    {
        $this->dispatch('umami-track', name: 'oil_log_submit_attempt');

        try {
            $this->form->validate();
        } catch (ValidationException $exception) {
            $this->dispatch('umami-track', name: 'oil_log_submit_error', data: [
                'error_type' => 'validation',
            ]);

            throw $exception;
        }

        try {
            $data = $this->form->getState();

            $aircraft = Aircraft::findOrFail($data['aircraft_id']);
            $aircraft->oilLogs()->create([
                'user_id' => auth()->id(),
                'pilot' => auth()->user()?->name ?? $data['pilot'],
                'registration' => $aircraft->registration,
                'oil_level' => $data['oil_level'],
                'oil_refilled' => $data['oil_refilled'],
            ]);
        } catch (Throwable $exception) {
            $this->dispatch('umami-track', name: 'oil_log_submit_error', data: [
                'error_type' => 'save_failure',
            ]);

            throw $exception;
        }

        $this->dispatch('umami-track', name: 'oil_log_submit_success', data: [
            'oil_level_type' => $this->oilLevelType?->value ?? 'unknown',
        ]);

        return $this->redirectRoute('oil.success', navigate: true);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('')
                    ->columns(1)
                    ->schema([

                        ToggleButtons::make('aircraft_id')
                            ->required()
                            ->inline()
                            ->label('Flugzeug auswählen')
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if (! $state) {
                                    return;
                                }

                                $aircraft = Aircraft::findOrFail($state);

                                $set('oil_level', $aircraft->getOilLevel());
                                $this->oilLevelType = $aircraft->oil_level_type;
                                $this->dispatch('umami-track', name: 'oil_log_aircraft_selected', data: [
                                    'oil_level_type' => $aircraft->oil_level_type?->value ?? 'unknown',
                                ]);
                            })
                            ->options(
                                Aircraft::owned()
                                    ->where('registration', 'not like', 'X-%')
                                    ->pluck('registration', 'id')
                            ),

                        TextInput::make('pilot')
                            ->visible(fn () => auth()->guest())
                            ->required(fn () => auth()->guest())
                            ->label('Name')
                            ->placeholder('Vor- und Nachname'),

                        TextInput::make('oil_level')
                            ->visible(fn () => $this->oilLevelType == OilLevelType::absolute)
                            ->required()
                            ->numeric()
                            ->disabled(fn ($get) => blank($get('aircraft_id')))
                            ->suffix(' qts')
                            ->label('Aktueller Ölstand')
                            ->step(0.1),

                        Radio::make('oil_level')
                            ->visible(fn () => $this->oilLevelType == OilLevelType::relative)
                            ->required()
                            ->disabled(fn ($get) => blank($get('aircraft_id')))
                            ->label('Ölstand')
                            ->options([
                                100 => 'max',
                                75 => '3 / 4',
                                50 => '1 / 2',
                                25 => '1 / 4',
                                0 => 'min',
                            ]),

                        TextInput::make('oil_refilled')
                            ->required()
                            ->visible(fn ($get) => filled($get('aircraft_id')))
                            ->suffix(fn () => match ($this->oilLevelType) {
                                OilLevelType::absolute => ' qts',
                                OilLevelType::relative => ' ml',
                                default => null,
                            })
                            ->helperText(fn () => match ($this->oilLevelType) {
                                OilLevelType::absolute => '',
                                OilLevelType::relative => 'Bitte gib dem Nachfüllwert in ml an, wenn du Öl nachgefüllt hast!',
                                default => null,
                            })
                            ->numeric()
                            ->label('Menge, die du nachgefüllt hast')
                            ->step(0.5),
                    ]),
            ]);
    }

    public function render()
    {
        return view('livewire.oil-log-page');
    }
}
