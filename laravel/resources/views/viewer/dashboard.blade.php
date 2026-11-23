@extends('layouts.viewer')

@php
    $pageTitle       = 'Dashboard — AeroSenseV2';
    $metaDescription = 'Pantau kualitas udara real-time di seluruh fakultas Universitas Diponegoro.';
@endphp

@section('content')

{{-- ============================================================
     SECTION 1 — HERO / INTRODUCTION
     ============================================================ --}}
<section class="hero" id="hero-section" aria-labelledby="hero-title">
    {{-- Campus background image overlay (sits behind content via CSS absolute positioning) --}}
    <div class="hero__bg" aria-hidden="true"></div>
    <div class="container">
        <h1 class="hero__title" id="hero-title">
            <span class="title-main">AeroSense</span>
            Air Quality Dashboard
        </h1>
        <p class="hero__desc">
            AeroSense menyajikan wawasan kualitas udara yang komprehensif
            di seluruh area kampus dengan prediksi berbasis AI, membantu
            sivitas akademika tetap terinformasi dan menjaga kesehatan.
        </p>

        {{-- Summary Stat Cards --}}
        <div class="hero__stats" id="hero-stats">
            <div class="stat-card" id="stat-faculties">
                <div class="stat-card__value">{{ $facultyCount ?: config('aerosense.total_faculties') }}</div>
                <div class="stat-card__label">Fakultas</div>
            </div>
            <div class="stat-card" id="stat-monitoring">
                <div class="stat-card__value">24/7</div>
                <div class="stat-card__label">Monitoring</div>
            </div>
            <div class="stat-card" id="stat-ai">
                <div class="stat-card__value">AI</div>
                <div class="stat-card__label">Powered Prediction</div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     SECTION 2 — REAL-TIME AIR QUALITY (paginated, JS-toggled)
     Shows 4 faculties at a time (2 left, 2 right) with prev/next.
     ============================================================ --}}
<section class="realtime-section" id="realtime-section" aria-labelledby="realtime-title">
    <div class="container">
        <h2 class="section-title" id="realtime-title">Data Kualitas Udara Real&#8209;Time</h2>
        <p class="section-subtitle">
            Pantau kondisi udara terkini di berbagai fakultas Universitas Diponegoro
        </p>

        @if($sensorReadings->isNotEmpty())

        {{-- Serialize all readings for JS --}}
        @php
            $readingsJson = $sensorReadings->map(fn($r) => [
                'faculty_id'  => $r->faculty_id,
                'name'        => $r->faculty->name ?? '—',
                'co2'         => round($r->co2, 1),
                'temperature' => round($r->temperature, 1),
                'humidity'    => round($r->humidity, 1),
                'status'      => $r->computed_status,
                'status_class'=> $r->status_class,
            ])->values();
        @endphp

        <div class="realtime-grid" id="realtime-grid">

            {{-- Left: 2 cards (filled by JS) --}}
            <div class="realtime-grid__cards-left" id="rt-cards-left"></div>

            {{-- Center: Animated Weather Illustration --}}
            <div class="realtime-grid__center" aria-hidden="true">
                <div class="weather-centerpiece">
                    {{-- Pulsing radar rings --}}
                    <span class="weather-ring weather-ring--1"></span>
                    <span class="weather-ring weather-ring--2"></span>
                    <span class="weather-ring weather-ring--3"></span>
                    {{-- Rotating shimmer halo --}}
                    <span class="weather-halo"></span>
                    {{-- Ambient glow backdrop --}}
                    <span class="weather-glow"></span>
                    {{-- The actual image, floating --}}
                    <img src="{{ asset('assets/images/weather.png') }}"
                         alt="Weather illustration"
                         class="weather-img">
                </div>
            </div>

            {{-- Right: 2 cards (filled by JS) --}}
            <div class="realtime-grid__cards-right" id="rt-cards-right"></div>

        </div>

        {{-- Prev / Page indicator / Next --}}
        <div class="realtime-controls" id="realtime-controls" aria-label="Navigasi halaman fakultas">
            <button class="realtime-btn prev" id="rt-btn-prev"
                    onclick="rtChangePage(-1)" aria-label="Sebelumnya">
                <i class="btn-arrow">←</i> Sebelumnya
            </button>
            <span class="realtime-page" id="rt-page-indicator" aria-live="polite">1 / 1</span>
            <button class="realtime-btn next" id="rt-btn-next"
                    onclick="rtChangePage(1)" aria-label="Berikutnya">
                Berikutnya <i class="btn-arrow">→</i>
            </button>
        </div>

        <script>
        (function () {
            const readings  = @json($readingsJson);
            const PER_PAGE  = 4;
            let   page      = 0;
            const totalPages = Math.ceil(readings.length / PER_PAGE);

            function makeCard(r) {
                return `<div class="faculty-card" id="faculty-card-${r.faculty_id}">
                    <div class="faculty-card__name">${r.name}</div>
                    <div class="faculty-card__status ${r.status_class}">${r.status}</div>
                    <div class="faculty-card__metrics">
                        <div class="metric-item"><span>💨 CO₂</span><strong>${r.co2} ppm</strong></div>
                        <div class="metric-item"><span>🌡 Suhu</span><strong>${r.temperature}°C</strong></div>
                        <div class="metric-item"><span>💧 Kelembapan</span><strong>${r.humidity}%</strong></div>
                    </div>
                </div>`;
            }

            function render() {
                const start  = page * PER_PAGE;
                const slice  = readings.slice(start, start + PER_PAGE);
                const left   = slice.slice(0, 2);
                const right  = slice.slice(2, 4);

                document.getElementById('rt-cards-left').innerHTML  = left.map(makeCard).join('');
                document.getElementById('rt-cards-right').innerHTML = right.map(makeCard).join('');
                document.getElementById('rt-page-indicator').textContent =
                    `${page + 1} / ${totalPages}`;
                document.getElementById('rt-btn-prev').disabled = (page === 0);
                document.getElementById('rt-btn-next').disabled = (page >= totalPages - 1);
            }

            window.rtChangePage = function (dir) {
                page = Math.max(0, Math.min(totalPages - 1, page + dir));
                render();
            };

            render(); // initial render
        })();
        </script>

        @else
        <div class="chart-body-placeholder" style="margin-top: 1rem;">
            <span class="placeholder-icon">📡</span>
            <span>Data sensor belum tersedia. Jalankan seeder untuk melihat data.</span>
        </div>
        @endif
    </div>
</section>

{{-- ============================================================
     SECTION 3 — HISTORICAL AIR QUALITY TREND
     Note: This section is NOT real-time.
     Data is manually managed by admin via air_quality_trends table.
     Chart.js integration will be added in Phase 3.
     ============================================================ --}}
<section class="trend-section" id="trend-section" aria-labelledby="trend-title">
    <div class="container">
        <h2 class="section-title" id="trend-title">Tren Kualitas Udara Historis</h2>
        <p class="section-subtitle">
            Analisis data historis untuk memahami pola<br>
            dan perubahan kualitas udara dari waktu ke waktu
        </p>

        @php
            // All faculties for the dropdown
            $allFaculties = \App\Models\Faculty::orderBy('name')->get();

            // Resolve selected faculty from GET param or default to "Fakultas Teknik"
            $selectedFacultyId = request()->query('faculty_id');
            if ($selectedFacultyId) {
                $selectedFaculty = $allFaculties->firstWhere('id', $selectedFacultyId);
            } else {
                $selectedFaculty = $allFaculties->first(fn($f) => str_contains(strtolower($f->name), 'teknik'));
                $selectedFacultyId = $selectedFaculty?->id;
            }
            if (!$selectedFaculty) {
                $selectedFaculty   = $allFaculties->first();
                $selectedFacultyId = $selectedFaculty?->id;
            }
        @endphp

        <div class="chart-container" id="chart-container">
            <div class="chart-header" style="display:flex; align-items:center; gap:0.75rem;">
                <div class="chart-title-group">
                    <span class="chart-icon">📊</span>
                    <div class="chart-title-text">
                        <strong>Grafik tren </strong>
                        <span>Data sensor {{ $selectedFaculty?->name ?? 'Semua Fakultas' }}</span>
                    </div>
                </div>

                {{-- ── Faculty Selector (right side) ──────────────── --}}
                <form method="GET" action="{{ url()->current() }}#trend-section" id="faculty-filter-form"
                      style="display:flex; align-items:center; gap:0.5rem; margin-left:auto;">
                    <label for="faculty_id_chart"
                           style="font-size:0.78rem; color:#9ca3af; white-space:nowrap;">
                        Pilih Fakultas:
                    </label>
                    <select id="faculty_id_chart" name="faculty_id"
                            onchange="this.form.submit()"
                            style="background:#1e293b; color:#e2e8f0; border:1px solid #334155;
                                   border-radius:0.4rem; padding:0.35rem 0.65rem; font-size:0.82rem;
                                   cursor:pointer; outline:none;">
                        @foreach($allFaculties as $faculty)
                            <option value="{{ $faculty->id }}"
                                    @selected($faculty->id == $selectedFacultyId)>
                                {{ $faculty->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

        @php
            $chartReadings = \App\Models\SensorReading::query()
                ->when($selectedFacultyId, fn ($q) => $q->where('faculty_id', $selectedFacultyId))
                ->orderBy('recorded_at', 'desc')
                ->limit(30)
                ->get()
                ->reverse()
                ->values();

            $chartLabels = $chartReadings->map(fn ($r) => $r->recorded_at->format('d/m H:i'))->toArray();
            $chartTemp   = $chartReadings->pluck('temperature')->map(fn ($v) => round($v, 1))->toArray();
            $chartHum    = $chartReadings->pluck('humidity')->map(fn ($v) => round($v, 1))->toArray();
            $chartCo2    = $chartReadings->pluck('co2')->map(fn ($v) => round($v, 1))->toArray();
        @endphp

        @if($chartReadings->isNotEmpty())
            {{-- Live Chart.js chart --}}
            <canvas id="dashboardTrendChart" style="width:100%; max-height:280px; padding:0 1rem 0.5rem;"></canvas>

            {{-- Chart.js from local npm bundle via app.js — no CDN --}}
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    new Chart(document.getElementById('dashboardTrendChart'), {
                        type: 'line',
                        data: {
                            labels: @json($chartLabels),
                            datasets: [
                                {
                                    label: 'Suhu (°C)',
                                    data: @json($chartTemp),
                                    borderColor: '#f97316',
                                    backgroundColor: 'rgba(249,115,22,0.1)',
                                    tension: 0.4,
                                    fill: true,
                                    pointRadius: 2,
                                },
                                {
                                    label: 'Kelembapan (%)',
                                    data: @json($chartHum),
                                    borderColor: '#00d4a0',
                                    backgroundColor: 'rgba(0,212,160,0.1)',
                                    tension: 0.4,
                                    fill: true,
                                    pointRadius: 2,
                                },
                                {
                                    label: 'CO₂ (ppm)',
                                    data: @json($chartCo2),
                                    borderColor: '#ef4444',
                                    backgroundColor: 'rgba(239,68,68,0.1)',
                                    tension: 0.4,
                                    fill: true,
                                    pointRadius: 2,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { color: '#9ca3af', font: { size: 12 } }
                                },
                            },
                            scales: {
                                x: {
                                    ticks: { color: '#9ca3af', maxTicksLimit: 8, maxRotation: 45 },
                                    grid:  { color: 'rgba(255,255,255,0.05)' },
                                },
                                y: {
                                    ticks:     { color: '#9ca3af' },
                                    grid:      { color: 'rgba(255,255,255,0.05)' },
                                    beginAtZero: false,
                                },
                            },
                        },
                    });
                });
            </script>
        @else
            <div class="chart-body-placeholder" id="chart-placeholder">
                <span class="placeholder-icon">📈</span>
                <span>Belum ada data sensor. Hubungkan perangkat IoT untuk melihat grafik.</span>
            </div>
        @endif

            <div class="chart-legend" aria-label="Chart legend">
                <div class="legend-item">
                    <div class="legend-dot" style="background:#ef4444;"></div>
                    <span>CO Level</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot" style="background:#f97316;"></div>
                    <span>Temperatur</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot" style="background:#00d4a0;"></div>
                    <span>Co2</span>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
