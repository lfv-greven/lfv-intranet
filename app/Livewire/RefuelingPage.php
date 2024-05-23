<?php

namespace App\Livewire;

use App\Enums\RefuelingType;
use App\Models\Aircraft;
use App\Models\GasStation;
use App\Models\Refueling;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Number;
use Livewire\Component;

class RefuelingPage extends Component implements HasForms
{
    use InteractsWithForms;

    public array $data = [];

    public function mount()
    {
        $this->form->fill([
            'date' => now(),
            'buyer_name' => auth()->user()?->name,
        ]);
    }

    public function save()
    {
        if (! $this->form->validate()) {
            return;
        }

        $data = fluent($this->form->getState());
        $reg = $data->buyer_registration ?? Aircraft::find($data->aircraft_id)->registration;

        Refueling::create([
            'user_id' => auth()->id(),
            'type' => RefuelingType::refueling,
            'gas_station_id' => $data->gas_station_id,
            'date' => $data->date,
            'aircraft_id' => $data->aircraft_id,
            'buyer_name' => $data->buyer_name,
            'buyer_registration' => $reg,
            'counter_reading' => $data->counter_reading,
            'amount' => $data->amount,
            'comment' => $data->comment,
        ]);

        Notification::make()
            ->success()
            ->title('Betankung wurde eingetragen')
            ->send();

        return $this->redirectRoute('home', navigate: true);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->columns(2)
            ->schema([
                Select::make('gas_station_id')
                    ->required()
                    ->placeholder('')
                    ->label('Tankstelle')
                    ->live()
                    ->helperText(function ($state) {
                        if (! $state) {
                            return;
                        }

                        $filling = GasStation::withSum('refuelings', 'amount')
                            ->find($state)
                            ->refuelings_sum_amount;

                        return 'Aktueller Füllstand: '.Number::format($filling, locale: 'de').' L';
                    })
                    ->options(GasStation::pluck('name', 'id')),

                DateTimePicker::make('date')
                    ->native(false)
                    ->displayFormat('d.m.Y H:i')
                    ->seconds(false)
                    ->label('Zeitpunkt der Betankung')
                    ->required()
                    ->maxDate(now())
                    ->minDate(today()->startOfMonth())
                    ->default(now()),

                TextInput::make('buyer_name')
                    ->placeholder('Max Mustermann')
                    ->label('Name')
                    ->label('Name des Piloten')
                    ->columnSpanFull()
                    ->required(),

                Select::make('aircraft_id')
                    ->placeholder('Fremdes LFZ/KFZ')
                    ->label('Flugzeug')
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if (! $state) {
                            return;
                        }

                        $aircraft = Aircraft::find($state);

                        $set('buyer_registration', $aircraft->registration);
                    })
                    ->options([
                        'Verein' => Aircraft::owned()->pluck('registration', 'id')->toArray(),
                        'Privat' => Aircraft::foreign()->pluck('registration', 'id')->toArray(),
                    ]),

                TextInput::make('buyer_registration')
                    ->disabled(fn ($get) => $get('aircraft_id'))
                    ->label('Kennzeichen')
                    ->placeholder('D-EABC')
                    ->required(),

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
            ]);
    }

    public function render()
    {
        return view('livewire.refueling-page');
    }
}
