<?php

namespace App\Filament\Resources\Refuelings\Schemas;

use App\Enums\RefuelingType;
use App\Models\Aircraft;
use App\Models\GasStation;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RefuelingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        Select::make('gas_station_id')
                            ->required()
                            ->label('Tankstelle')
                            ->options(GasStation::pluck('name', 'id')),

                        DateTimePicker::make('date')
                            ->label('Datum')
                            ->seconds(false)
                            ->required()
                            ->default(now()),

                        Select::make('aircraft_id')
                            ->label('Flugzeug')
                            ->disabled(fn ($get) => $get('type')->value == RefuelingType::filling->value)
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                $aircraft = Aircraft::find($state);

                                $set('buyer_registration', $aircraft->registration);
                            })
                            ->options(Aircraft::pluck('registration', 'id')),

                        TextInput::make('buyer_registration')
                            ->label('Kennzeichen')
                            ->disabled(fn ($get) => $get('type')->value == RefuelingType::filling->value)
                            ->requiredIf('type', 'refueling'),

                        Select::make('type')
                            ->label('Art des Vorgangs')
                            ->selectablePlaceholder(false)
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state == RefuelingType::filling->value) {
                                    $set('aircraft_id', null);
                                    $set('buyer_registration', null);
                                }
                            })
                            ->required()
                            ->default(RefuelingType::refueling->value)
                            ->options(RefuelingType::class),

                        TextInput::make('buyer_name')
                            ->label('Pilot')
                            ->required()
                            ->default(auth()->user()->name),

                        TextInput::make('counter_reading')
                            ->label('Zählerstand nach Betankung')
                            ->required()
                            ->minValue(0)
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $set, $get, $operation) {
                                if ($operation != 'create') {
                                    return;
                                }

                                $gasStationId = $get('gas_station_id');
                                if (! $gasStationId) {
                                    return;
                                }

                                $gasStation = GasStation::findOrFail($gasStationId);

                                $set('amount', $state - $gasStation->getCurrentCounterReading());
                            })
                            ->suffix(' l'),

                        TextInput::make('amount')
                            ->label('Menge')
                            ->formatStateUsing(fn ($state) => abs($state))
                            ->required()
                            ->minValue(0)
                            ->numeric()
                            ->suffix(' l'),

                        Textarea::make('comment')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
