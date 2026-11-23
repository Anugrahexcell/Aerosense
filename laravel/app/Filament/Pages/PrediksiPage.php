<?php

namespace App\Filament\Pages;

use App\Models\AirQualityPrediction;
use App\Models\Faculty;
use Filament\Pages\Page;

class PrediksiPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-chart-pie';
    protected static ?string $title           = 'Prediksi Kualitas Udara';
    protected static ?string $navigationLabel = 'Prediksi';
    protected static ?int    $navigationSort  = 4;
    protected string $view = 'filament.pages.prediksi-page';

    public ?int $faculty_id = null;

    public function mount(): void
    {
        $this->faculty_id = Faculty::orderBy('name')->value('id');
    }

    public function getFacultyOptions(): array
    {
        return Faculty::orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function getFacultyName(): string
    {
        return Faculty::find($this->faculty_id)?->name ?? '—';
    }

    // ── 90-day prediction data with confidence band ───────────────
    public function getPredictionData(): array
    {
        $predictions = AirQualityPrediction::where('faculty_id', $this->faculty_id)
            ->where('prediction_type', 'daily')
            ->where('predicted_for', '>=', now()->startOfDay())
            ->orderBy('predicted_for')
            ->limit(90)
            ->get();

        if ($predictions->isEmpty()) {
            return [
                'labels' => [], 'co2' => [], 'temperature' => [], 'humidity' => [],
                'co2_upper' => [], 'co2_lower' => [], 'confidence' => [], 'status' => [],
            ];
        }

        $labels = $co2 = $temp = $hum = $co2Upper = $co2Lower = $confidence = $status = [];

        foreach ($predictions as $p) {
            $labels[]     = $p->predicted_for->format('d/m');
            $co2[]        = (float) $p->predicted_co2;
            $temp[]       = (float) $p->predicted_temperature;
            $hum[]        = (float) $p->predicted_humidity;
            $status[]     = $p->predicted_status;
            $conf         = (float) $p->confidence_score;
            $confidence[] = $conf;

            // Confidence band: widens as confidence decreases
            $band       = (1 - $conf / 100) * 0.28;
            $co2Upper[] = round((float) $p->predicted_co2 * (1 + $band), 1);
            $co2Lower[] = round(max(350, (float) $p->predicted_co2 * (1 - $band)), 1);
        }

        return compact('labels', 'co2', 'temp', 'hum', 'co2Upper', 'co2Lower', 'confidence', 'status');
    }

    // ── Summary of latest prediction entry ───────────────────────
    public function getFirstPrediction(): ?AirQualityPrediction
    {
        return AirQualityPrediction::where('faculty_id', $this->faculty_id)
            ->where('prediction_type', 'daily')
            ->where('predicted_for', '>=', now()->startOfDay())
            ->orderBy('predicted_for')
            ->first();
    }

    // ── Dispatch chart data to JS when faculty changes ────────────
    public function updatedFacultyId(): void
    {
        $this->dispatch('prediksi-data-ready', data: $this->getPredictionData());
    }
}
