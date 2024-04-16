<?php

namespace App\Livewire;

use App\Models\Aircraft;
use App\Models\GasStation;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
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
            'date' => today(),
            'buyer_name' => auth()->user()?->name,
        ]);
    }

    public function save()
    {
        if (! $this->form->validate()) {
            return;
        }
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

                DatePicker::make('date')
                    ->label('Datum')
                    ->required()
                    ->maxDate(today())
                    ->minDate(today()->startOfMonth())
                    ->default(today()),

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
