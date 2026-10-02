<?php

namespace App\Filament\Widgets;

use App\Models\SensorReading;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PemantauanStatsWidget extends StatsOverviewWidget
{
    public array $parameters = [];
    public ?int $faculty_id = null;

    // Auto-refresh every 5 minutes — in sync with the hourly data cycle
    protected ?string $pollingInterval = '5m';

    public function getLivewireProperties(): array
    {
        return [
            'parameters' => $this->parameters,
            'faculty_id' => $this->faculty_id,
        ];
    }

    protected function getStats(): array
    {
        // Pull latest reading for the selected faculty
        $reading = SensorReading::query()
            ->when($this->faculty_id, fn ($q) => $q->where('faculty_id', $this->faculty_id))
            ->latest('recorded_at')
            ->first();

        $stats = [];

        if (in_array('suhu', $this->parameters)) {
            $temp = $reading ? number_format($reading->temperature, 1) : '—';
            $stats[] = Stat::make('Suhu (°C)', $temp)
                ->description($reading ? 'Terakhir: ' . $reading->recorded_at->diffForHumans() : 'Belum ada data')
                ->descriptionIcon('heroicon-m-fire')
                ->color('warning');
        }

        if (in_array('kelembapan', $this->parameters)) {
            $hum = $reading ? number_format($reading->humidity, 1) : '—';
            $stats[] = Stat::make('Kelembapan (%)', $hum)
                ->description($reading ? 'Terakhir: ' . $reading->recorded_at->diffForHumans() : 'Belum ada data')
                ->descriptionIcon('heroicon-m-cloud')
                ->color('info');
        }

        if (in_array('co2', $this->parameters)) {
            $co2    = $reading ? number_format($reading->co2, 1) : '—';
            $status = $reading?->computed_status ?? 'Belum ada data';
            $color  = match ($status) {
                'Baik'   => 'success',
                'Sedang' => 'warning',
                'Buruk'  => 'danger',
                default  => 'gray',
            };
            $stats[] = Stat::make('CO₂ (ppm)', $co2)
                ->description($status)
                ->descriptionIcon('heroicon-m-sparkles')
                ->color($color);
        }

        return $stats;
    }
}
