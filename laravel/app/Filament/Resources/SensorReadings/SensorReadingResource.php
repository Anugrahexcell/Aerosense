<?php

namespace App\Filament\Resources\SensorReadings;

use App\Filament\Resources\SensorReadings\Pages\CreateSensorReading;
use App\Filament\Resources\SensorReadings\Pages\ListSensorReadings;
use App\Filament\Resources\SensorReadings\Schemas\SensorReadingForm;
use App\Filament\Resources\SensorReadings\Tables\SensorReadingsTable;
use App\Models\SensorReading;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SensorReadingResource extends Resource
{
    protected static ?string $model = SensorReading::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCircleStack;

    protected static ?string $navigationLabel = 'Kelola Data';

    protected static ?string $modelLabel = 'Data Kualitas Udara';

    protected static ?string $pluralModelLabel = 'Data Kualitas Udara';

    protected static ?string $recordTitleAttribute = 'recorded_at';

    public static function form(Schema $schema): Schema
    {
        return SensorReadingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SensorReadingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSensorReadings::route('/'),
            'create' => CreateSensorReading::route('/create'),
            // 'edit' route removed — sensor data must not be manually modified
        ];
    }
}
