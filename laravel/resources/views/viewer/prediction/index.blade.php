@extends('layouts.viewer')

@php
    $pageTitle       = 'Prediksi — AeroSenseV2';
    $metaDescription = 'Prediksi kualitas udara berbasis AI untuk seluruh fakultas Universitas Diponegoro.';
@endphp


@section('content')

{{-- ============================================================
     SECTION 1 — PREDICTION HERO & MODEL INFO
     ============================================================ --}}
<section class="prediction-hero" id="prediction-hero" aria-labelledby="prediction-title">
    <div class="container">
        <h1 class="section-title" id="prediction-title">
            Prediksi <span class="text-accent">Kualitas Udara</span>
        </h1>
        <p class="section-subtitle">
            Sistem AI kami memprediksi kondisi kualitas udara untuk
            membantu Anda merencanakan aktivitas dengan lebih baik
        </p>

        {{-- ML Model Info Panel --}}
        <div class="model-info-panel" id="model-info-panel" role="region" aria-label="Informasi model prediksi">
            <div class="model-info-panel__icon" aria-hidden="true">🧠</div>
            <div class="model-info-panel__content">
                <h2>Model Prediksi Machine Learning</h2>
                <p>
                    Menggunakan algoritma {{ $modelInfo['algorithm'] }} yang dilatih dengan data
                    historis dari {{ config('aerosense.total_faculties') }} fakultas, sistem ini memprediksi
                    kualitas udara hingga {{ config('aerosense.prediction_hours') }} jam ke depan dengan
                    akurasi tinggi.
                </p>
                <div class="model-meta">
                    <div class="model-meta-item">
                        <span>📊</span>
                        <span>Akurasi: <strong>{{ $modelInfo['accuracy'] }}%</strong></span>
                    </div>
                    <div class="model-meta-sep" aria-hidden="true"></div>
                    <div class="model-meta-item">
                        <span>🕐</span>
                        <span>Update: <strong>Setiap jam</strong></span>
                    </div>
                    <div class="model-meta-sep" aria-hidden="true"></div>
                    <div class="model-meta-item">
                        <span>ℹ️</span>
                        <span>Model: <strong>{{ $modelInfo['name'] }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     SECTIONS 2, 3, 4 — All on dark background
     ============================================================ --}}
<div class="prediction-main">
    <div class="container">

        {{-- ========================================================
             SECTION 2 — PREDICTION PER FACULTY / LOCATION
             ======================================================== --}}
        <div class="pred-section" id="faculty-prediction-section">
            <h2 class="pred-section-title">
                <span class="icon">📍</span>
                Prediksi per Lokasi/Fakultas
            </h2>

            @if($facultyPredictions->isNotEmpty())
            <div class="prediction-grid" id="faculty-prediction-grid">
                @foreach($facultyPredictions as $facultyId => $predictions)
                @php $pred = $predictions->first(); @endphp
                <div class="prediction-card" id="pred-faculty-{{ $facultyId }}">
                    <div class="prediction-card__header">
                        <span class="prediction-card__name">{{ $pred->faculty->name ?? 'Fakultas' }}</span>
                        <span class="status-badge {{ $pred->status_class }}">{{ $pred->predicted_status }}</span>
                    </div>
                    <div class="pred-metrics">
                        <div class="pred-metric">
                            <span class="pred-metric__label">💨 CO₂</span>
                            <span class="pred-metric__value">{{ $pred->predicted_co2 }} ppm</span>
                        </div>
                        <div class="pred-metric">
                            <span class="pred-metric__label">🌡 Suhu</span>
                            <span class="pred-metric__value">{{ $pred->predicted_temperature }}°C</span>
                        </div>
                        <div class="pred-metric">
                            <span class="pred-metric__label">💧 Kelembapan</span>
                            <span class="pred-metric__value">{{ $pred->predicted_humidity }}%</span>
                        </div>
                    </div>
                    <div class="pred-confidence">
                        <span class="pred-confidence__label">Confidence Score</span>
                        <span class="pred-confidence__value">{{ $pred->confidence_score }}%</span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="chart-body-placeholder" style="background:rgba(255,255,255,.04); border-color:rgba(255,255,255,.1); color:var(--text-secondary);">
                <span class="placeholder-icon">🔮</span>
                <span style="font-size:.82rem;">Data prediksi belum tersedia. Jalankan seeder untuk melihat data.</span>
            </div>
            @endif
        </div>

    </div>
</div>

@endsection

