<?php

namespace App\Filament\Resources\SensorReadings\Pages;

use App\Filament\Resources\SensorReadings\SensorReadingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSensorReading extends EditRecord
{
    protected static string $resource = SensorReadingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
