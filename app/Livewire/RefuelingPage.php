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
use Livewire\Component;

class RefuelingPage extends Component implements HasForms
{
    use InteractsWithForms;

    public $gas_station_id;

    public $date;

    public $aircraft_id;

    public $buyer_registration;

    public $buyer_name;

    public $counter_reading;

    public $amount;

    public $comment;

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

        $data = $this->form->getState();

        Refueling::create([
            'type' => RefuelingType::refueling,
            'gas_station_id' => $data['gas_station_id'],
            'date' => $data['date'],
            'aircraft_id' => $data['aircraft_id'],
            'buyer_registration' => $data['buyer_registration'],
            'buyer_name' => $data['buyer_name'],
            'counter_reading' => $data['counter_reading'],
            'amount' => $data['amount'],
            'comment' => $data['comment'],
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
            ->columns(2)
            ->schema([
                Select::make('gas_station_id')
                    ->required()
                    ->label('Tankstelle')
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if (! $state) {
                            return;
                        }

                        $gasStation = GasStation::findOrFail($state);

                        $set('counter_reading', $gasStation->getCurrentCounterReading());
                    })
                    ->options(GasStation::pluck('name', 'id')),

                DateTimePicker::make('date')
                    ->native(false)
                    ->displayFormat('d.m.Y H:i')
                    ->seconds(false)
                    ->label('Datum')
                    ->required()
                    ->maxDate(now())
                    ->minDate(today()->startOfMonth())
                    ->default(now()),

                Select::make('aircraft_id')
                    ->label('Flugzeug')
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        $aircraft = Aircraft::find($state);

                        $set('buyer_registration', $aircraft->registration);
                    })
                    ->options(Aircraft::pluck('registration', 'id')),

                TextInput::make('buyer_registration')
                    ->label('Kennzeichen')
                    ->required(),

                TextInput::make('buyer_name')
                    ->label('Name')
                    ->label('Pilot')
                    ->columnSpanFull()
                    ->required(),

                TextInput::make('counter_reading')
                    ->label('Zählerstand nach Betankung')
                    ->required()
                    ->minValue(0)
                    ->numeric()
                    ->live()
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
                    ->required()
                    ->minValue(0)
                    ->numeric()
                    ->suffix(' l'),

                Textarea::make('comment')
                    ->label('Hinweis')
                    ->columnSpanFull(),
            ]);
    }

    public function render()
    {
        return view('livewire.refueling-page');
    }
}
