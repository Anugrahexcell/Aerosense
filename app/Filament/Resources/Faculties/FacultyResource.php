<?php

namespace App\Filament\Resources\Faculties;

use App\Filament\Resources\Faculties\Pages\ListFaculties;
use App\Models\Faculty;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class FacultyResource extends Resource
{
    protected static ?string $model = Faculty::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $navigationLabel = 'Daftar Fakultas';

    protected static ?string $modelLabel = 'Fakultas';

    protected static ?string $pluralModelLabel = 'Daftar Fakultas';

    protected static ?int $navigationSort = 1;

    // Faculty data is managed via seeders — no create/edit/delete in admin
    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Daftar Fakultas & Node ID')
            ->description('ID fakultas sesuai dengan FacultyId yang terdaftar pada perangkat IoT (Arduino/ESP32) di setiap gedung.')
            ->columns([
                TextColumn::make('id')
                    ->label('Faculty ID')
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->alignCenter(),

                TextColumn::make('code')
                    ->label('Kode')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('name')
                    ->label('Nama Fakultas')
                    ->sortable()
                    ->searchable()
                    ->weight('medium'),

                IconColumn::make('is_active')
                    ->label('Status Node')
                    ->boolean()
                    ->trueIcon('heroicon-o-signal')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                TextColumn::make('sensorReadings_count')
                    ->label('Total Pembacaan')
                    ->counts('sensorReadings')
                    ->sortable()
                    ->alignCenter()
                    ->color('info'),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'asc')
            ->striped()
            ->emptyStateHeading('Belum Ada Data Fakultas')
            ->emptyStateDescription('Jalankan: php artisan db:seed --class=FacultySeeder')
            ->emptyStateIcon('heroicon-o-building-library');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFaculties::route('/'),
        ];
    }
}

