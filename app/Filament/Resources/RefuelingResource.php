<?php

namespace App\Filament\Resources;

use App\Enums\RefuelingType;
use App\Filament\Resources\RefuelingResource\Pages;
use App\Jobs\Vf\SendRefueling;
use App\Models\Aircraft;
use App\Models\GasStation;
use App\Models\Refueling;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class RefuelingResource extends Resource
{
    protected static ?string $model = Refueling::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Tankstelle';

    protected static ?string $navigationLabel = 'Betankungen';

    public static function form(Form $form): Form
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
                    ->seconds(false)
                    ->required()
                    ->default(now()),

                Select::make('aircraft_id')
                    ->label('Flugzeug')
                    ->disabled(fn ($get) => $get('type') == RefuelingType::filling->value)
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        $aircraft = Aircraft::find($state);

                        $set('buyer_registration', $aircraft->registration);
                    })
                    ->options(Aircraft::pluck('registration', 'id')),

                TextInput::make('buyer_registration')
                    ->disabled(fn ($get) => $get('type') == RefuelingType::filling->value)
                    ->requiredIf('type', 'refueling'),

                Select::make('type')
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
                    ->required()
                    ->minValue(0)
                    ->numeric()
                    ->live()
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('counter_reading', 'desc')
            ->modifyQueryUsing(function ($query) {
                return $query
                    ->select()
                    ->addSelect(DB::raw('(SELECT SUM(amount) FROM refuelings r2 WHERE refuelings.gas_station_id = r2.gas_station_id AND refuelings.counter_reading >= r2.counter_reading) AS fill_level'))
                    ->addSelect(DB::raw('CASE WHEN type = "filling" THEN NULL ELSE LAG(counter_reading) OVER (partition by gas_station_id order by counter_reading asc) - counter_reading - amount END AS diff'));
            })
            ->columns([
                TextColumn::make('date')
                    ->label('Datum')
                    ->date('d.m.Y H:i'),
                TextColumn::make('buyer_registration')
                    ->badge(fn ($record) => filled($record->aircraft_id))
                    ->color('gray')
                    ->label('Kennzeichen'),
                TextColumn::make('buyer_name')
                    ->label('Name'),
                TextColumn::make('counter_reading')
                    ->numeric(0, null, '.')
                    ->alignRight()
                    ->label('Zählerstand'),
                TextColumn::make('diff')
                    ->numeric(0, null, '.')
                    ->color(fn ($state) => $state < 0 ? 'danger' : null)
                    ->alignRight()
                    ->label('Diff'),
                TextColumn::make('fill_level')
                    ->label('Füllstand')
                    ->numeric(0, null, '.')
                    ->suffix(' l')
                    ->alignRight()
                    ->toggleable(),
                TextColumn::make('amount')
                    ->label('Menge')
                    ->alignRight()
                    ->numeric()
                    ->color(function (Refueling $record, $state) {
                        if ($record->amount < 0) {
                            return 'danger';
                        }
                    })
                    ->suffix(' l'),
            ])
            ->filters([
                SelectFilter::make('gas_station_id')
                    ->options(GasStation::pluck('name', 'id'))
                    ->label('Tankstelle'),
            ])
            ->actions([
                Action::make('send_vf')
                    ->icon('heroicon-s-banknotes')
                    ->iconButton()
                    ->tooltip('Verkauf an Vereinsflieger übertragen')
                    ->visible(fn ($record) => $record->type == RefuelingType::refueling && ! $record->aircraft?->owned)
                    ->disabled(function (Refueling $record) {
                        if ($record->isExported()) {
                            return true;
                        } elseif (! $record->gasStation->vf_articleid) {
                            return true;
                        }

                        return false;
                    })
                    ->action(function (Refueling $record) {
                        SendRefueling::dispatch($record);

                        Notification::make()
                            ->success()
                            ->title('Verkauf wird im Hintergrund übertragen.')
                            ->send();
                    }),
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->paginationPageOptions([50, 100, 250]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRefuelings::route('/'),
        ];
    }
}
