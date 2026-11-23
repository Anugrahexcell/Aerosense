<?php

namespace App\Filament\Widgets;

use App\Models\SensorReading;
use Filament\Widgets\ChartWidget;

class PemantauanChartWidget extends ChartWidget
{
    protected ?string $heading = 'Dinamika Indikator';
    protected int|string|array $columnSpan = 'full';
    protected ?string $maxHeight = '320px';

    // Excluded from auto-discovery — rendered explicitly via @livewire() in the Pemantauan blade
    protected static bool $isDiscovered = false;

    public array $parameters = [];
    public ?int $faculty_id = null;
    public string $time_range = '08:00 - 13:00';

    public function getLivewireProperties(): array
    {
        return [
            'parameters' => $this->parameters,
            'faculty_id' => $this->faculty_id,
            'time_range' => $this->time_range,
        ];
    }

    protected function getData(): array
    {
        // Parse time range
        [$startTime, $endTime] = array_map('trim', explode(' - ', $this->time_range));
        [$startHour] = explode(':', $startTime);
        [$endHour]   = explode(':', $endTime);

        // Fetch readings for selected faculty within the last 7 days,
        // filtered to the chosen hour window, ordered by time.
        $readings = SensorReading::query()
            ->when($this->faculty_id, fn ($q) => $q->where('faculty_id', $this->faculty_id))
            ->where('recorded_at', '>=', now()->subDays(7))
            ->whereRaw('HOUR(recorded_at) >= ?', [(int)$startHour])
            ->whereRaw('HOUR(recorded_at) <= ?', [(int)$endHour])
            ->orderBy('recorded_at')
            ->limit(30)
            ->get();

        // Build x-axis labels from timestamps
        $labels = $readings->map(fn ($r) => $r->recorded_at->format('H:i'))->toArray();
        if (empty($labels)) {
            $labels = ['08:00', '08:30', '09:00', '09:30', '10:00'];
        }

        $datasets = [];

        if (in_array('suhu', $this->parameters)) {
            $datasets[] = [
                'label'           => 'Suhu (°C)',
                'data'            => $readings->isNotEmpty()
                    ? $readings->pluck('temperature')->map(fn ($v) => round($v, 1))->toArray()
                    : [17.5, 17.6, 17.5, 17.7, 17.6],
                'borderColor'     => '#f97316',
                'backgroundColor' => 'rgba(249,115,22,0.15)',
                'tension'         => 0.4,
                'fill'            => false,
                'pointRadius'     => 3,
            ];
        }

        if (in_array('kelembapan', $this->parameters)) {
            $datasets[] = [
                'label'           => 'Kelembapan (%)',
                'data'            => $readings->isNotEmpty()
                    ? $readings->pluck('humidity')->map(fn ($v) => round($v, 1))->toArray()
                    : [42.9, 43.1, 42.8, 43.0, 42.9],
                'borderColor'     => '#3b82f6',
                'backgroundColor' => 'rgba(59,130,246,0.15)',
                'tension'         => 0.4,
                'fill'            => false,
                'pointRadius'     => 3,
            ];
        }

        if (in_array('co2', $this->parameters)) {
            $datasets[] = [
                'label'           => 'CO₂ (ppm)',
                'data'            => $readings->isNotEmpty()
                    ? $readings->pluck('co2')->map(fn ($v) => round($v, 1))->toArray()
                    : [440, 455, 461, 448, 452],
                'borderColor'     => '#22c55e',
                'backgroundColor' => 'rgba(34,197,94,0.15)',
                'tension'         => 0.4,
                'fill'            => false,
                'pointRadius'     => 3,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels'   => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
