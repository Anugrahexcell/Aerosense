@extends('layouts.viewer')

@php
    $pageTitle       = 'Education — AeroSenseV2';
    $metaDescription = 'Pelajari dampak kualitas udara terhadap kesehatan dan lingkungan bersama AeroSense.';
@endphp

@section('content')

{{-- ============================================================
     SECTION 1 — AIR QUALITY IMPACT
     Static section — hardcoded content (stable, rarely changes)
     ============================================================ --}}
<section class="impact-section" id="impact-section" aria-labelledby="impact-title">
    {{-- Campus background image overlay --}}
    <div class="impact-section__bg" aria-hidden="true"></div>
    <div class="container">
        <h1 class="section-title" id="impact-title">
            Dampak <span class="text-accent">Kualitas Udara</span>
        </h1>
        <p class="section-subtitle">
            Memahami pentingnya kualitas udara<br>bagi kesehatan dan lingkungan
        </p>

        <div class="impact-grid" id="impact-grid">

            <div class="impact-card" id="impact-respiratory">
                <div class="impact-card__icon red" aria-hidden="true">❤️</div>
                <h3 class="impact-card__title">Kesehatan Pernapasan</h3>
                <p class="impact-card__desc">
                    Kualitas udara buruk dapat memperburuk asma, bronkitis,
                    dan penyakit pernapasan lainnya.
                </p>
            </div>

            <div class="impact-card" id="impact-cardiovascular">
                <div class="impact-card__icon orange" aria-hidden="true">⚠️</div>
                <h3 class="impact-card__title">Sistem Kardiovaskular</h3>
                <p class="impact-card__desc">
                    Paparan polutan udara meningkatkan risiko penyakit
                    jantung dan stroke.
                </p>
            </div>

            <div class="impact-card" id="impact-mental">
                <div class="impact-card__icon purple" aria-hidden="true">👥</div>
                <h3 class="impact-card__title">Kesehatan Mental</h3>
                <p class="impact-card__desc">
                    Polusi udara dapat mempengaruhi fungsi kognitif
                    dan kesejahteraan mental.
                </p>
            </div>

            <div class="impact-card" id="impact-environment">
                <div class="impact-card__icon teal" aria-hidden="true">🌿</div>
                <h3 class="impact-card__title">Dampak Lingkungan</h3>
                <p class="impact-card__desc">
                    Polusi udara merusak ekosistem, tanaman,
                    dan keanekaragaman hayati.
                </p>
            </div>

        </div>
    </div>
</section>

{{-- ============================================================
     SECTION 2 — FACTS & STATISTICS
     Static section — globally-sourced statistics
     ============================================================ --}}
<section class="facts-section" id="facts-section" aria-labelledby="facts-title">
    <div class="container">
        <h2 class="section-title" id="facts-title">Fakta &amp; Statistik Kualitas Udara</h2>
        <p class="section-subtitle">Data global tentang dampak polusi udara terhadap kesehatan</p>

        <div class="facts-grid" id="facts-grid">

            <div class="fact-card pink" id="fact-population">
                <div class="fact-card__icon">📈</div>
                <div class="fact-card__value">91%</div>
                <div class="fact-card__label">Populasi Dunia</div>
                <p class="fact-card__desc">
                    Terpapar polusi udara melebihi standar WHO
                </p>
            </div>

            <div class="fact-card orange" id="fact-deaths">
                <div class="fact-card__icon">⚠️</div>
                <div class="fact-card__value">8 Juta</div>
                <div class="fact-card__label">Kematian per Tahun</div>
                <p class="fact-card__desc">
                    Disebabkan oleh polusi udara di seluruh dunia
                </p>
            </div>

            <div class="fact-card purple" id="fact-heart">
                <div class="fact-card__icon">❤️</div>
                <div class="fact-card__value">25%</div>
                <div class="fact-card__label">Penyakit Jantung</div>
                <p class="fact-card__desc">
                    Kematian akibat stroke terkait polusi udara
                </p>
            </div>

            <div class="fact-card teal" id="fact-lung">
                <div class="fact-card__icon">🫁</div>
                <div class="fact-card__value">8%</div>
                <div class="fact-card__label">Penyakit Paru</div>
                <p class="fact-card__desc">
                    PPOK (Penyakit Paru Obstruktif Kronik) terkait polusi
                </p>
            </div>

        </div>
    </div>
</section>

{{-- ============================================================
     SECTION 3 — EDUCATIONAL ARTICLES
     Dynamic section — loaded from education_articles table
     Admin manages articles via admin panel (Phase 4+)
     ============================================================ --}}
<section class="articles-section" id="articles-section" aria-labelledby="articles-title">
    {{-- Campus background image overlay --}}
    <div class="articles-section__bg" aria-hidden="true"></div>
    <div class="container">
        <h2 class="section-title" id="articles-title">Artikel &amp; Panduan Edukasi</h2>
        <p class="section-subtitle">
            Pelajari lebih dalam tentang kualitas udara dan cara menjaganya
        </p>

        @if($articles->isNotEmpty())
        <div class="articles-grid" id="articles-grid">
            @foreach($articles as $article)
            <article class="article-card" id="article-{{ $article->id }}">
                <div class="article-card__meta">
                    <span>📋</span>
                    <span>{{ $article->reading_time_minutes }} menit baca</span>
                </div>
                <h3 class="article-card__title">{{ $article->title }}</h3>
                <p class="article-card__excerpt">{{ $article->excerpt }}</p>
                <a
                    href="{{ $article->external_url ?: '#' }}"
                    @if($article->external_url) target="_blank" rel="noopener noreferrer" @endif
                    class="article-card__link"
                    aria-label="Baca artikel: {{ $article->title }}"
                    @if(!$article->external_url) style="opacity:0.4; cursor:not-allowed;" @endif
                >
                    Baca selengkapnya &rsaquo;
                </a>
            </article>
            @endforeach
        </div>
        @else
        <div class="chart-body-placeholder" style="height:160px; background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.1);">
            <span class="placeholder-icon" style="color: var(--text-secondary);">📰</span>
            <span style="color: var(--text-secondary); font-size:.82rem;">
                Belum ada artikel. Jalankan seeder atau tambahkan via admin panel.
            </span>
        </div>
        @endif
    </div>
</section>

@endsection
