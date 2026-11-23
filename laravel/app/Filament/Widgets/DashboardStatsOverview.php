<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends BaseWidget
{
    // Only show on the admin Dashboard page, NOT on any other page
    protected static string|array $pages = ['filament.admin.pages.dashboard'];

    protected function getStats(): array
    {
        return [
            Stat::make('Sensor Aktif', '13/13')
                ->description('Fakultas Terhubung')
                ->descriptionIcon('heroicon-m-battery-100')
                ->color('success'),

            Stat::make('Sensor Bermasalah', '0')
                ->description('Tidak ada')
                ->descriptionIcon('heroicon-m-fire')
                ->color('success'),

            Stat::make('Status Sistem', 'Normal')
                ->description('Semua berjalan baik')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
