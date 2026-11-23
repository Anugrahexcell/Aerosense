<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AirQualityPrediction;
use App\Models\Faculty;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PrediksiPdfController extends Controller
{
    public function download(Request $request)
    {
        $facultyId = (int) $request->query('faculty_id');
        $faculty   = Faculty::findOrFail($facultyId);

        $predictions = AirQualityPrediction::where('faculty_id', $facultyId)
            ->where('prediction_type', 'daily')
            ->where('predicted_for', '>=', now()->startOfDay())
            ->orderBy('predicted_for')
            ->limit(90)
            ->get();

        // ── Build chart-ready arrays ──────────────────────────────
        $labels = $co2 = $temp = $hum = $co2Upper = $co2Lower = $confidence = $status = [];

        foreach ($predictions as $p) {
            $labels[]     = $p->predicted_for->format('d/m/Y');
            $co2[]        = (float) $p->predicted_co2;
            $temp[]       = (float) $p->predicted_temperature;
            $hum[]        = (float) $p->predicted_humidity;
            $status[]     = $p->predicted_status;
            $conf         = (float) $p->confidence_score;
            $confidence[] = $conf;
            $band         = (1 - $conf / 100) * 0.28;
            $co2Upper[]   = round((float) $p->predicted_co2 * (1 + $band), 1);
            $co2Lower[]   = round(max(350, (float) $p->predicted_co2 * (1 - $band)), 1);
        }

        $avgConf = count($confidence) ? round(array_sum($confidence) / count($confidence), 1) : 0;

        // ── Generate SVG charts ───────────────────────────────────
        $co2Svg  = $this->buildSvgChart($labels, $co2, $co2Upper, $co2Lower, 'CO₂ (ppm)', '#16a34a', 580, 180);
        $tempSvg = $this->buildSvgChart($labels, $temp, null, null, 'Suhu (°C)', '#ea580c', 280, 140);
        $humSvg  = $this->buildSvgChart($labels, $hum, null, null, 'Kelembapan (%)', '#2563eb', 280, 140);

        $pdf = Pdf::loadView('pdf.prediksi-report', [
            'faculty'     => $faculty,
            'predictions' => $predictions,
            'labels'      => $labels,
            'co2'         => $co2,
            'temp'        => $temp,
            'hum'         => $hum,
            'co2Upper'    => $co2Upper,
            'co2Lower'    => $co2Lower,
            'confidence'  => $confidence,
            'status'      => $status,
            'avgConf'     => $avgConf,
            'co2Svg'      => $co2Svg,
            'tempSvg'     => $tempSvg,
            'humSvg'      => $humSvg,
            'generatedAt' => now()->format('d F Y, H:i'),
        ])->setPaper('a4', 'portrait');

        $filename = 'Prediksi_' . str_replace(' ', '_', $faculty->name) . '_' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    }

    // ── Pure-PHP SVG line chart (DomPDF-compatible) ───────────────
    private function buildSvgChart(
        array $labels,
        array $values,
        ?array $upper,
        ?array $lower,
        string $title,
        string $color,
        int $width = 540,
        int $height = 160
    ): string {
        $n = count($values);
        if ($n === 0) return '';

        $pl = 48; $pr = 12; $pt = 18; $pb = 28;
        $cw = $width - $pl - $pr;
        $ch = $height - $pt - $pb;

        $allVals = array_merge($values, $upper ?? [], $lower ?? []);
        $minV = min($allVals) * 0.96;
        $maxV = max($allVals) * 1.04;
        $range = max($maxV - $minV, 1);

        $xOf = fn($i) => $pl + ($n > 1 ? ($i / ($n - 1)) * $cw : $cw / 2);
        $yOf = fn($v) => $pt + $ch - (($v - $minV) / $range) * $ch;

        $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='{$width}' height='{$height}'>";
        $svg .= "<rect width='{$width}' height='{$height}' fill='#f8fafc' rx='3'/>";

        // Grid lines
        for ($g = 0; $g <= 4; $g++) {
            $gy  = $pt + ($g / 4) * $ch;
            $gv  = round($maxV - ($g / 4) * $range);
            $svg .= "<line x1='{$pl}' y1='{$gy}' x2='" . ($width - $pr) . "' y2='{$gy}' stroke='#e2e8f0' stroke-width='0.8'/>";
            $svg .= "<text x='" . ($pl - 4) . "' y='" . ($gy + 3.5) . "' text-anchor='end' font-size='7.5' fill='#94a3b8'>{$gv}</text>";
        }

        // Confidence band polygon
        if ($upper && $lower) {
            $pts = [];
            for ($i = 0; $i < $n; $i++) $pts[] = $xOf($i) . ',' . $yOf($upper[$i]);
            for ($i = $n - 1; $i >= 0; $i--) $pts[] = $xOf($i) . ',' . $yOf($lower[$i]);
            $svg .= "<polygon points='" . implode(' ', $pts) . "' fill='rgba(34,197,94,0.15)'/>";

            // Dashed upper/lower edges
            $uPath = ''; $lPath = '';
            for ($i = 0; $i < $n; $i++) {
                $uPath .= ($i === 0 ? 'M' : 'L') . ' ' . $xOf($i) . ' ' . $yOf($upper[$i]) . ' ';
                $lPath .= ($i === 0 ? 'M' : 'L') . ' ' . $xOf($i) . ' ' . $yOf($lower[$i]) . ' ';
            }
            $svg .= "<path d='{$uPath}' fill='none' stroke='rgba(22,163,74,0.35)' stroke-width='0.8' stroke-dasharray='3,3'/>";
            $svg .= "<path d='{$lPath}' fill='none' stroke='rgba(22,163,74,0.35)' stroke-width='0.8' stroke-dasharray='3,3'/>";
        }

        // Main line
        $path = '';
        for ($i = 0; $i < $n; $i++) {
            $path .= ($i === 0 ? 'M' : 'L') . ' ' . $xOf($i) . ' ' . $yOf($values[$i]) . ' ';
        }
        $svg .= "<path d='{$path}' fill='none' stroke='{$color}' stroke-width='1.8'/>";

        // X-axis labels (every ~15 days)
        $step = max(1, (int) floor($n / 6));
        for ($i = 0; $i < $n; $i += $step) {
            $x   = $xOf($i);
            $y   = $height - 6;
            $lbl = isset($labels[$i]) ? substr($labels[$i], 0, 5) : '';
            $svg .= "<text x='{$x}' y='{$y}' text-anchor='middle' font-size='7' fill='#94a3b8'>{$lbl}</text>";
        }

        // Title
        $svg .= "<text x='" . ($pl + 2) . "' y='11' font-size='9' font-weight='bold' fill='#374151'>{$title}</text>";

        $svg .= '</svg>';
        return $svg;
    }
}
