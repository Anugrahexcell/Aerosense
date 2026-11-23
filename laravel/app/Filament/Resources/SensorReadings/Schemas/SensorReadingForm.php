<?php

namespace App\Filament\Resources\SensorReadings\Schemas;

use App\Models\Faculty;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SensorReadingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Fakultas — full-width select
                Select::make('faculty_id')
                    ->label('Fakultas')
                    ->options(Faculty::orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),

                // Waktu — full-width datetime picker
                DateTimePicker::make('recorded_at')
                    ->label('Waktu')
                    ->required()
                    ->default(now())
                    ->columnSpanFull(),

                // Suhu, Kelembaban, CO₂ — side by side using columns on schema
                TextInput::make('temperature')
                    ->label('Suhu (°C)')
                    ->numeric()
                    ->step(0.1)
                    ->minValue(-50)
                    ->maxValue(100)
                    ->required()
                    ->suffix('°C'),

                TextInput::make('humidity')
                    ->label('Kelembaban (%)')
                    ->numeric()
                    ->step(0.1)
                    ->minValue(0)
                    ->maxValue(100)
                    ->required()
                    ->suffix('%'),

                TextInput::make('co2')
                    ->label('CO₂ (ppm)')
                    ->numeric()
                    ->step(0.1)
                    ->minValue(0)
                    ->required()
                    ->suffix('ppm'),
            ])
            ->columns(3);
    }
}
