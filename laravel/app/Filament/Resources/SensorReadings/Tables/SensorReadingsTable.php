<?php

namespace App\Filament\Resources\SensorReadings\Tables;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
// EditAction intentionally removed — sensor data must not be manually modified
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Models\Faculty;

class SensorReadingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Kelola Data')
            ->description('Tambah data kualitas udara atau hapus data korup dari sensor. Data tidak dapat diedit secara manual.')
            ->columns([
                TextColumn::make('faculty.name')
                    ->label('Fakultas')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('recorded_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('temperature')
                    ->label('Suhu (°C)')
                    ->numeric(decimalPlaces: 1)
                    ->sortable()
                    ->color('warning'),

                TextColumn::make('humidity')
                    ->label('Kelembaban (%)')
                    ->numeric(decimalPlaces: 1)
                    ->sortable(),

                TextColumn::make('co2')
                    ->label('CO₂ (ppm)')
                    ->numeric(decimalPlaces: 1)
                    ->sortable(),

                TextColumn::make('air_quality_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Baik'               => 'success',
                        'Sedang'             => 'warning',
                        'Tidak Sehat'        => 'danger',
                        'Sangat Tidak Sehat' => 'danger',
                        'Berbahaya'          => 'danger',
                        default              => 'gray',
                    }),
            ])
            ->defaultSort('recorded_at', 'desc')
            ->filters([
                SelectFilter::make('faculty_id')
                    ->label('Semua Fakultas')
                    ->options(fn () => Faculty::orderBy('name')->pluck('name', 'id'))
                    ->placeholder('Semua Fakultas'),
            ])
            ->filtersFormMaxHeight('300px')
            ->recordActions([
                // Edit removed — sensor data must not be manually modified
                DeleteAction::make()
                    ->icon('heroicon-m-trash')
                    ->iconButton()
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Data Sensor?')
                    ->modalDescription('Hanya hapus jika data ini korup atau tidak valid dari sensor. Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus'),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Data Terpilih?')
                        ->modalDescription('Hanya hapus jika data-data ini korup atau tidak valid dari sensor. Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus Semua'),
                ]),
            ])
            // No headerActions — Create removed (IoT-only data source)
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Data')
                    ->icon('heroicon-m-plus')
                    ->color('primary'),
            ])
            ->emptyStateHeading('Belum Ada Data Sensor')
            ->emptyStateDescription('Data akan muncul otomatis ketika perangkat IoT mengirimkan pembacaan.')
            ->emptyStateIcon('heroicon-o-signal')
            ->striped();
    }
}
