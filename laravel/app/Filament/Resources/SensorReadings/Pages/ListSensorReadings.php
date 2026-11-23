<?php

namespace App\Filament\Resources\SensorReadings\Pages;

use App\Filament\Resources\SensorReadings\SensorReadingResource;
use App\Models\Faculty;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSensorReadings extends ListRecords
{
    protected static string $resource = SensorReadingResource::class;

    // Custom view injects wire:poll.10s so the table refreshes automatically every 10s
    protected string $view = 'filament.resources.sensor-readings.pages.list-sensor-readings';

    /**
     * Pre-apply the "Fakultas Teknik" filter so users see Teknik data immediately.
     * Create action removed — data is IoT-only and must not be manually added.
     */
    public function mount(): void
    {
        parent::mount();

        $teknikId = Faculty::where('name', 'like', '%Teknik%')->value('id');

        if ($teknikId) {
            $this->tableFilters = [
                'faculty_id' => ['value' => (string) $teknikId],
            ];
        }
    }

    // Create restored — Edit removed (sensor data must not be manually modified)
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
