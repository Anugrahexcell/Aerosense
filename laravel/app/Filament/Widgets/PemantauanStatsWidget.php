<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PemantauanStatsWidget extends StatsOverviewWidget
{
    public array $parameters = [];

    public function getLivewireProperties(): array
    {
        return ['parameters' => $this->parameters];
    }

    protected function getStats(): array
    {
        $stats = [];

        if (in_array('suhu', $this->parameters)) {
            $stats[] = Stat::make('Suhu (°C)', '17.5')
                ->description('Normal')
                ->descriptionIcon('heroicon-m-fire')
                ->color('orange');
        }

        if (in_array('kelembapan', $this->parameters)) {
            $stats[] = Stat::make('Kelembapan (%)', '42.9')
                ->description('Optimal')
                ->descriptionIcon('heroicon-m-cloud')
                ->color('blue');
        }

        if (in_array('co2', $this->parameters)) {
            $stats[] = Stat::make('CO₂ (ppm)', '461')
                ->description('Sehat')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('success');
        }

        return $stats;
    }
}
