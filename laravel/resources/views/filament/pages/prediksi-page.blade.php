<x-filament-panels::page>

    {{-- ── Filter Bar ─────────────────────────────────────────── --}}
    <x-filament::section>
        <div style="display:flex; flex-direction:row; align-items:flex-end; gap:1.5rem; flex-wrap:wrap;">

            <div style="flex:2; min-width:180px;">
                <label style="display:block; font-size:0.8rem; font-weight:600; margin-bottom:0.35rem; color:#6b7280;">
                    🏫 Pilih Fakultas
                </label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="faculty_id">
                        @foreach($this->getFacultyOptions() as $id => $name)
                            <option value="{{ $id }}" @selected($faculty_id == $id)>{{ $name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div style="display:flex; align-items:center; gap:0.6rem; padding-bottom:0.2rem;">
                <span style="display:inline-flex; align-items:center; gap:0.4rem;
                             background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d;
                             border-radius:9999px; padding:0.3rem 0.85rem; font-size:0.78rem; font-weight:600;">
                    🤖 Model: XGBoost v4
                </span>
                <span style="display:inline-flex; align-items:center; gap:0.4rem;
                             background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8;
                             border-radius:9999px; padding:0.3rem 0.85rem; font-size:0.78rem; font-weight:600;">
                    📅 Proyeksi: 90 Hari
                </span>
            </div>

            <div style="margin-left:auto; padding-bottom:0.2rem;">
                <a href="{{ route('admin.prediksi.pdf', ['faculty_id' => $faculty_id]) }}"
                   target="_blank"
                   style="display:inline-flex; align-items:center; gap:0.5rem;
                          background:#dc2626; color:#fff; border-radius:0.5rem;
                          padding:0.55rem 1.1rem; font-size:0.82rem; font-weight:600;
                          text-decoration:none; transition:background .2s;"
                   onmouseover="this.style.background='#b91c1c'"
                   onmouseout="this.style.background='#dc2626'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM8 13h8v1.5H8V13zm0 3h5v1.5H8V16zm0-6h3v1.5H8V10z"/>
                    </svg>
                    Download PDF
                </a>
            </div>
        </div>
    </x-filament::section>

    {{-- ── Summary Stats ───────────────────────────────────────── --}}
    @php
        $first = $this->getFirstPrediction();
        $data  = $this->getPredictionData();
        $avgConf = count($data['confidence']) ? round(array_sum($data['confidence']) / count($data['confidence']), 1) : 0;
    @endphp
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem;">
        <x-filament::section>
            <div style="border-left:4px solid #22c55e; padding-left:0.75rem;">
                <div style="font-size:0.75rem; color:#6b7280; margin-bottom:0.3rem;">🌿 CO₂ Prediksi (Hari ini)</div>
                <div style="font-size:1.8rem; font-weight:700; line-height:1.1;">
                    {{ $first ? number_format($first->predicted_co2, 0) : '—' }}
                    <small style="font-size:0.9rem; font-weight:400;">ppm</small>
                </div>
                <div style="font-size:0.72rem; color:#9ca3af; margin-top:0.25rem;">
                    Status: <strong>{{ $first?->predicted_status ?? '—' }}</strong>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div style="border-left:4px solid #f97316; padding-left:0.75rem;">
                <div style="font-size:0.75rem; color:#6b7280; margin-bottom:0.3rem;">🌡️ Suhu Prediksi (Hari ini)</div>
                <div style="font-size:1.8rem; font-weight:700; line-height:1.1;">
                    {{ $first ? number_format($first->predicted_temperature, 1) : '—' }}
                    <small style="font-size:0.9rem; font-weight:400;">°C</small>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div style="border-left:4px solid #3b82f6; padding-left:0.75rem;">
                <div style="font-size:0.75rem; color:#6b7280; margin-bottom:0.3rem;">💧 Kelembapan Prediksi (Hari ini)</div>
                <div style="font-size:1.8rem; font-weight:700; line-height:1.1;">
                    {{ $first ? number_format($first->predicted_humidity, 1) : '—' }}
                    <small style="font-size:0.9rem; font-weight:400;">%</small>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div style="border-left:4px solid #a855f7; padding-left:0.75rem;">
                <div style="font-size:0.75rem; color:#6b7280; margin-bottom:0.3rem;">🎯 Rata-rata Confidence Score</div>
                <div style="font-size:1.8rem; font-weight:700; line-height:1.1;">
                    {{ $avgConf }}<small style="font-size:0.9rem; font-weight:400;">%</small>
                </div>
                <div style="font-size:0.72rem; color:#9ca3af; margin-top:0.25rem;">
                    Semakin jauh = semakin rendah
                </div>
            </div>
        </x-filament::section>
    </div>

    {{-- ── CO₂ Chart with Confidence Band ─────────────────────── --}}
    <x-filament::section>
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem;">
            <div>
                <div style="font-weight:700; font-size:0.95rem;">Proyeksi CO₂ — 90 Hari ke Depan</div>
                <div style="font-size:0.78rem; color:#9ca3af; margin-top:0.2rem;">
                    Area abu-abu = interval kepercayaan XGBoost v4. Semakin jauh prediksi, semakin lebar band.
                </div>
            </div>
        </div>
        <div wire:ignore style="position:relative; height:300px;">
            <canvas id="prediksiCo2Chart"></canvas>
        </div>
    </x-filament::section>

    {{-- ── Suhu & Kelembapan Charts ─────────────────────────────── --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
        <x-filament::section>
            <div style="font-weight:600; font-size:0.88rem; margin-bottom:0.5rem;">🌡️ Proyeksi Suhu (°C)</div>
            <div wire:ignore style="position:relative; height:200px;">
                <canvas id="prediksiTempChart"></canvas>
            </div>
        </x-filament::section>
        <x-filament::section>
            <div style="font-weight:600; font-size:0.88rem; margin-bottom:0.5rem;">💧 Proyeksi Kelembapan (%)</div>
            <div wire:ignore style="position:relative; height:200px;">
                <canvas id="prediksiHumChart"></canvas>
            </div>
        </x-filament::section>
    </div>

    {{-- ── Data Table ───────────────────────────────────────────── --}}
    <x-filament::section heading="📋 Tabel Data Prediksi Harian" description="Hasil proyeksi XGBoost v4 — 90 hari ke depan. Confidence score menunjukkan tingkat keyakinan model.">
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse; font-size:0.82rem;">
                <thead>
                    <tr style="border-bottom:2px solid #e5e7eb; background:#f9fafb;">
                        <th style="padding:0.6rem 0.8rem; text-align:left; color:#6b7280; font-weight:600;">Hari ke-</th>
                        <th style="padding:0.6rem 0.8rem; text-align:left; color:#6b7280; font-weight:600;">Tanggal</th>
                        <th style="padding:0.6rem 0.8rem; text-align:right; color:#6b7280; font-weight:600;">CO₂ (ppm)</th>
                        <th style="padding:0.6rem 0.8rem; text-align:right; color:#6b7280; font-weight:600;">Suhu (°C)</th>
                        <th style="padding:0.6rem 0.8rem; text-align:right; color:#6b7280; font-weight:600;">Kelembapan (%)</th>
                        <th style="padding:0.6rem 0.8rem; text-align:center; color:#6b7280; font-weight:600;">Status</th>
                        <th style="padding:0.6rem 0.8rem; text-align:center; color:#6b7280; font-weight:600;">Confidence</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['labels'] as $i => $label)
                    @php
                        $statusColor = match($data['status'][$i] ?? 'Baik') {
                            'Baik'        => '#15803d',
                            'Sedang'      => '#b45309',
                            'Tidak Sehat' => '#b91c1c',
                            default       => '#6b7280',
                        };
                        $statusBg = match($data['status'][$i] ?? 'Baik') {
                            'Baik'        => '#f0fdf4',
                            'Sedang'      => '#fffbeb',
                            'Tidak Sehat' => '#fef2f2',
                            default       => '#f9fafb',
                        };
                        $confVal = $data['confidence'][$i] ?? 0;
                        $confColor = $confVal >= 80 ? '#15803d' : ($confVal >= 65 ? '#b45309' : '#b91c1c');
                    @endphp
                    <tr style="border-bottom:1px solid #f3f4f6; {{ $i % 2 === 0 ? '' : 'background:#fafafa;' }}">
                        <td style="padding:0.5rem 0.8rem; color:#9ca3af;">{{ $i + 1 }}</td>
                        <td style="padding:0.5rem 0.8rem; font-weight:500;">{{ $label }}</td>
                        <td style="padding:0.5rem 0.8rem; text-align:right; color:#16a34a; font-weight:600;">
                            {{ number_format($data['co2'][$i] ?? 0, 0) }}
                        </td>
                        <td style="padding:0.5rem 0.8rem; text-align:right; color:#ea580c;">
                            {{ number_format($data['temp'][$i] ?? 0, 1) }}
                        </td>
                        <td style="padding:0.5rem 0.8rem; text-align:right; color:#2563eb;">
                            {{ number_format($data['hum'][$i] ?? 0, 1) }}
                        </td>
                        <td style="padding:0.5rem 0.8rem; text-align:center;">
                            <span style="background:{{ $statusBg }}; color:{{ $statusColor }};
                                         border-radius:9999px; padding:0.2rem 0.65rem;
                                         font-size:0.75rem; font-weight:600;">
                                {{ $data['status'][$i] ?? '—' }}
                            </span>
                        </td>
                        <td style="padding:0.5rem 0.8rem; text-align:center; color:{{ $confColor }}; font-weight:600;">
                            {{ $confVal }}%
                        </td>
                    </tr>
                    @endforeach
                    @if(empty($data['labels']))
                    <tr>
                        <td colspan="7" style="padding:2rem; text-align:center; color:#9ca3af;">
                            Belum ada data prediksi. Jalankan seeder terlebih dahulu.
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </x-filament::section>

    {{-- ── Chart.js Scripts ─────────────────────────────────────── --}}
    @php $chartData = $this->getPredictionData(); @endphp
    {{-- Chart.js loaded from local npm bundle (app.js) — no CDN needed --}}
    <script>
    (function () {
        let co2Chart = null, tempChart = null, humChart = null;

        function buildAllCharts(d) {
            buildCo2Chart(d.labels, d.co2, d.co2Upper, d.co2Lower);
            buildSimpleChart('prediksiTempChart', tempChart, d.labels, d.temp, '#f97316', 'Suhu (°C)', chart => tempChart = chart);
            buildSimpleChart('prediksiHumChart',  humChart,  d.labels, d.hum,  '#3b82f6', 'Kelembapan (%)', chart => humChart = chart);
        }

        function buildCo2Chart(labels, co2, upper, lower) {
            const canvas = document.getElementById('prediksiCo2Chart');
            if (!canvas) return;
            if (co2Chart) { co2Chart.destroy(); co2Chart = null; }

            co2Chart = new Chart(canvas, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        // Upper band (fills toward lower band)
                        {
                            data: upper,
                            borderWidth: 0,
                            borderColor: 'transparent',
                            backgroundColor: 'rgba(34,197,94,0.13)',
                            fill: '+1',
                            tension: 0.4,
                            pointRadius: 0,
                            label: 'Batas Atas',
                        },
                        // Lower band
                        {
                            data: lower,
                            borderWidth: 0,
                            borderColor: 'transparent',
                            backgroundColor: 'rgba(34,197,94,0.13)',
                            fill: false,
                            tension: 0.4,
                            pointRadius: 0,
                            label: 'Batas Bawah',
                        },
                        // Main prediction line
                        {
                            label: 'CO₂ (ppm) — XGBoost v4',
                            data: co2,
                            borderColor: '#16a34a',
                            backgroundColor: 'transparent',
                            borderWidth: 2.5,
                            fill: false,
                            tension: 0.4,
                            pointRadius: 2,
                            pointHoverRadius: 5,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                filter: item => item.text !== 'Batas Atas' && item.text !== 'Batas Bawah',
                                boxWidth: 12,
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => {
                                    if (ctx.dataset.label === 'Batas Atas') return `  Batas Atas: ${ctx.parsed.y} ppm`;
                                    if (ctx.dataset.label === 'Batas Bawah') return `  Batas Bawah: ${ctx.parsed.y} ppm`;
                                    return `  ${ctx.dataset.label}: ${ctx.parsed.y} ppm`;
                                }
                            }
                        },
                    },
                    scales: {
                        x: { ticks: { maxTicksLimit: 15, font: { size: 11 } }, grid: { color: 'rgba(0,0,0,0.04)' } },
                        y: {
                            beginAtZero: false,
                            title: { display: true, text: 'CO₂ (ppm)', font: { size: 11 } },
                            ticks: { font: { size: 11 } },
                        },
                    },
                },
            });
        }

        function buildSimpleChart(id, existingChart, labels, values, color, label, setRef) {
            const canvas = document.getElementById(id);
            if (!canvas) return;
            if (existingChart) existingChart.destroy();

            const chart = new Chart(canvas, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label,
                        data: values,
                        borderColor: color,
                        backgroundColor: color.replace(')', ',0.08)').replace('rgb', 'rgba'),
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 1.5,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { maxTicksLimit: 10, font: { size: 10 } } },
                        y: { beginAtZero: false, ticks: { font: { size: 10 } } },
                    },
                },
            });
            setRef(chart);
        }

        // Initial render
        const initData = @json($chartData);
        function init() { buildAllCharts(initData); }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }

        // Re-render when faculty filter changes (via PHP dispatch)
        window.addEventListener('prediksi-data-ready', e => {
            buildAllCharts(e.detail.data);
        });

        document.addEventListener('livewire:navigated', init);
    })();
    </script>

</x-filament-panels::page>
