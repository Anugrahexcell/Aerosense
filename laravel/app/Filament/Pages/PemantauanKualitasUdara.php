<?php

namespace App\Filament\Pages;

use App\Models\Faculty;
use Filament\Pages\Page;

class PemantauanKualitasUdara extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $title = 'Pemantauan Kualitas Udara';
    protected static ?string $navigationLabel = 'Pemantauan';
    protected string $view = 'filament.pages.pemantauan-kualitas-udara';

    // Livewire state
    public ?int $faculty_id = null;
    public string $time_range = '08:00 - 13:00';
    public array $parameters = ['suhu', 'kelembapan', 'co2'];

    public function mount(): void
    {
        // Always default to Fakultas Teknik for focused real-time monitoring
        $this->faculty_id = Faculty::where('name', 'like', '%Teknik%')
            ->value('id')
            ?? Faculty::orderBy('name')->value('id');
    }

    public function getFacultyOptions(): array
    {
        return Faculty::orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function getTimeRangeOptions(): array
    {
        return [
            '00:00 - 05:00' => '00:00 - 05:00',
            '03:00 - 08:00' => '03:00 - 08:00',
            '06:00 - 11:00' => '06:00 - 11:00',
            '08:00 - 13:00' => '08:00 - 13:00',
            '10:00 - 15:00' => '10:00 - 15:00',
            '12:00 - 17:00' => '12:00 - 17:00',
            '15:00 - 20:00' => '15:00 - 20:00',
        ];
    }

    public function toggleParameter(string $param): void
    {
        if (in_array($param, $this->parameters)) {
            $this->parameters = array_values(array_filter($this->parameters, fn ($p) => $p !== $param));
        } else {
            $this->parameters[] = $param;
        }
    }

    // Compute the [hour_start, hour_end] from the selected time range string
    public function getParsedTimeRange(): array
    {
        [$start, $end] = explode(' - ', $this->time_range);
        return [$start, $end];
    }
}
