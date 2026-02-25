<?php

namespace App\Livewire;

use App\Enums\RefuelingType;
use App\Models\Aircraft;
use App\Models\GasStation;
use App\Models\Refueling;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Throwable;

class RefuelingPage extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'gas_station_id' => null,
            'aircraft_id' => null,
            'buyer_registration' => null,
            'counter_reading' => null,
            'amount' => null,
            'comment' => null,
        ]);
    }

    public function save()
    {
        $this->dispatch('umami-track', name: 'refueling_submit_attempt');

        try {
            $this->form->validate();
        } catch (ValidationException $exception) {
            $this->dispatch('umami-track', name: 'refueling_submit_error', data: [
                'error_type' => 'validation',
            ]);

            throw $exception;
        }

        try {
            $data = fluent($this->form->getState());
            $reg = $data->buyer_registration ?? Aircraft::find($data->aircraft_id)->registration;

            Refueling::create([
                'user_id' => auth()->id(),
                'type' => RefuelingType::refueling,
                'gas_station_id' => $data->gas_station_id,
                'date' => now(),
                'aircraft_id' => $data->aircraft_id,
                'buyer_name' => auth()->user()->name,
                'buyer_registration' => $reg,
                'counter_reading' => $data->counter_reading,
                'amount' => $data->amount,
                'comment' => $data->comment,
            ]);
        } catch (Throwable $exception) {
            $this->dispatch('umami-track', name: 'refueling_submit_error', data: [
                'error_type' => 'save_failure',
            ]);

            throw $exception;
        }

        $this->dispatch('umami-track', name: 'refueling_submit_success', data: [
            'gas_station_selected' => filled($data->gas_station_id),
            'aircraft_selected' => filled($data->aircraft_id),
        ]);

        return $this->redirectRoute('refueling.success', navigate: true);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Tankstelle wählen')
                    ->schema([
                        ToggleButtons::make('gas_station_id')
                            ->required()
                            ->inline()
                            ->label('Tankstelle wählen')
                            ->columnSpanFull()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                if (! $state) {
                                    return;
                                }

                                $this->dispatch('umami-track', name: 'refueling_gas_station_selected', data: [
                                    'has_selection' => true,
                                ]);
                            })
                            ->helperText(function ($state) {
                                if (! $state) {
                                    return;
                                }

                                $filling = GasStation::withSum('refuelings', 'amount')
                                    ->find($state)
                                    ->refuelings_sum_amount;

                                if ($filling) {
                                    return 'Aktueller Füllstand: '.Number::format($filling, locale: 'de').' L';
                                }
                            })
                            ->options(GasStation::pluck('name', 'id')),

                        Select::make('aircraft_id')
                            ->placeholder('Fremdes LFZ/KFZ')
                            ->label('Flugzeug auswählen')
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if (! $state) {
                                    return;
                                }

                                $aircraft = Aircraft::find($state);

                                $set('buyer_registration', $aircraft->registration);
                                $this->dispatch('umami-track', name: 'refueling_aircraft_selected', data: [
                                    'has_selection' => true,
                                    'aircraft_type' => $aircraft->owned ? 'owned' : 'foreign',
                                ]);
                            })
                            ->options([
                                'Verein' => Aircraft::owned()->pluck('registration', 'id')->toArray(),
                                'Privat' => Aircraft::foreign()->pluck('registration', 'id')->toArray(),
                            ]),

                        TextInput::make('buyer_registration')
                            ->hidden(fn ($get) => $get('aircraft_id'))
                            ->label('Kennzeichen')
                            ->placeholder('D-EABC')
                            ->required(),
                    ]),

                Section::make('Betankung erfassen')
                    ->visible(fn ($get) => filled($get('gas_station_id')))
                    ->schema([
                        TextInput::make('counter_reading')
                            ->label('Zählerstand nach Betankung')
                            ->helperText('Lies den Zählerstand nach Abschluss der Betankung ab.')
                            ->required()
                            ->minValue(0)
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $set, $get, $operation) {
                                $gasStationId = $get('gas_station_id');
                                if (! $gasStationId) {
                                    return;
                                }

                                $gasStation = GasStation::findOrFail($gasStationId);

                                $set('amount', $state - $gasStation->getCurrentCounterReading());
                            })
                            ->suffix(' l'),

                        TextInput::make('amount')
                            ->label('Getankte Menge')
                            ->helperText('Wird automatisch berechnet. Falls der berechnete Wert nicht passt, kann er manuell korrigiert werden.')
                            ->required()
                            ->minValue(0)
                            ->numeric()
                            ->suffix(' l'),

                        Textarea::make('comment')
                            ->label('Hinweis / Notizen')
                            ->placeholder('optional')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function render()
    {
        return view('livewire.refueling-page');
    }
}
