<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Prediksi Kualitas Udara — {{ $faculty->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9pt; color: #1a202c; background: #fff; }

        /* ── Header ── */
        .header { background: #0f172a; color: #fff; padding: 16px 22px; margin-bottom: 16px; }
        .header-top { display: flex; justify-content: space-between; align-items: flex-start; }
        .header h1 { font-size: 15pt; font-weight: bold; letter-spacing: 0.3px; }
        .header .sub { font-size: 8.5pt; color: #94a3b8; margin-top: 3px; }
        .header .badge { background: #16a34a; color: #fff; border-radius: 4px; padding: 2px 8px; font-size: 8pt; font-weight: bold; }
        .header .meta { font-size: 8pt; color: #94a3b8; text-align: right; }

        /* ── Faculty bar ── */
        .faculty-bar { background: #f1f5f9; border-left: 4px solid #16a34a; padding: 10px 16px; margin-bottom: 14px; border-radius: 0 4px 4px 0; }
        .faculty-bar .name { font-size: 12pt; font-weight: bold; }
        .faculty-bar .detail { font-size: 8pt; color: #64748b; margin-top: 2px; }

        /* ── Stats row ── */
        .stats-grid { display: flex; gap: 8px; margin-bottom: 14px; }
        .stat-box { flex: 1; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; }
        .stat-label { font-size: 7.5pt; color: #64748b; margin-bottom: 3px; }
        .stat-value { font-size: 14pt; font-weight: bold; }
        .stat-unit { font-size: 8pt; font-weight: normal; }
        .stat-sub { font-size: 7pt; color: #94a3b8; margin-top: 2px; }

        /* ── Section ── */
        .section { margin-bottom: 14px; }
        .section-title { font-size: 10pt; font-weight: bold; margin-bottom: 6px; padding-bottom: 4px; border-bottom: 1px solid #e2e8f0; }

        /* ── Charts row ── */
        .charts-row { display: flex; gap: 10px; margin-bottom: 14px; }
        .chart-box { flex: 1; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px; }

        /* ── Data table ── */
        table { width: 100%; border-collapse: collapse; font-size: 8pt; }
        th { background: #f8fafc; color: #475569; font-weight: 600; padding: 5px 7px; text-align: left; border-bottom: 2px solid #e2e8f0; }
        th.r, td.r { text-align: right; }
        th.c, td.c { text-align: center; }
        td { padding: 4px 7px; border-bottom: 1px solid #f1f5f9; }
        tr:nth-child(even) td { background: #fafafa; }

        .badge { display: inline-block; border-radius: 20px; padding: 1px 7px; font-size: 7pt; font-weight: 600; }
        .badge-baik    { background: #f0fdf4; color: #15803d; }
        .badge-sedang  { background: #fffbeb; color: #b45309; }
        .badge-tidak   { background: #fef2f2; color: #b91c1c; }

        .conf-high { color: #15803d; font-weight: bold; }
        .conf-mid  { color: #b45309; font-weight: bold; }
        .conf-low  { color: #b91c1c; font-weight: bold; }

        /* ── Footer ── */
        .footer { margin-top: 16px; padding-top: 8px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; font-size: 7.5pt; color: #94a3b8; }

        /* ── Page break helper ── */
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

    {{-- ── Header ── --}}
    <div class="header">
        <div class="header-top">
            <div>
                <h1>AeroSense V2 — Laporan Prediksi Kualitas Udara</h1>
                <div class="sub">Universitas Diponegoro · Sistem Pemantauan Kualitas Udara Berbasis AI</div>
            </div>
            <div style="text-align:right;">
                <span class="badge">XGBoost v4</span>
                <div class="meta" style="margin-top:5px;">Dibuat: {{ $generatedAt }}</div>
            </div>
        </div>
    </div>

    {{-- ── Faculty Info ── --}}
    <div class="faculty-bar">
        <div class="name">{{ $faculty->name }}</div>
        <div class="detail">Proyeksi 90 Hari · Periode: {{ now()->format('d M Y') }} – {{ now()->addDays(89)->format('d M Y') }}</div>
    </div>

    {{-- ── Stats Summary ── --}}
    @php
        $first = $predictions->first();
        $last  = $predictions->last();
    @endphp
    <div class="stats-grid">
        <div class="stat-box">
            <div class="stat-label">🌿 CO₂ Prediksi (Hari ini)</div>
            <div class="stat-value" style="color:#16a34a;">
                {{ $first ? number_format($first->predicted_co2, 0) : '—' }}
                <span class="stat-unit">ppm</span>
            </div>
            <div class="stat-sub">Hari ke-90: {{ $last ? number_format($last->predicted_co2, 0) : '—' }} ppm</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">🌡️ Suhu Prediksi (Hari ini)</div>
            <div class="stat-value" style="color:#ea580c;">
                {{ $first ? number_format($first->predicted_temperature, 1) : '—' }}
                <span class="stat-unit">°C</span>
            </div>
            <div class="stat-sub">Hari ke-90: {{ $last ? number_format($last->predicted_temperature, 1) : '—' }} °C</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">💧 Kelembapan (Hari ini)</div>
            <div class="stat-value" style="color:#2563eb;">
                {{ $first ? number_format($first->predicted_humidity, 1) : '—' }}
                <span class="stat-unit">%</span>
            </div>
            <div class="stat-sub">Hari ke-90: {{ $last ? number_format($last->predicted_humidity, 1) : '—' }} %</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">🎯 Rata-rata Confidence Score</div>
            <div class="stat-value" style="color:#7c3aed;">
                {{ $avgConf }}<span class="stat-unit">%</span>
            </div>
            <div class="stat-sub">Hari ini: {{ $first ? $first->confidence_score : '—' }}% → Hari 90: {{ $last ? $last->confidence_score : '—' }}%</div>
        </div>
    </div>

    {{-- ── CO₂ Chart (full width) ── --}}
    <div class="section">
        <div class="section-title">Grafik Proyeksi CO₂ — dengan Interval Kepercayaan XGBoost v4</div>
        <div style="border:1px solid #e2e8f0; border-radius:6px; padding:8px;">
            {!! $co2Svg !!}
            <div style="font-size:7pt; color:#94a3b8; margin-top:4px; text-align:center;">
                ▬ Nilai Prediksi &nbsp;&nbsp; ╌ ╌ Batas Atas/Bawah Interval Kepercayaan (band melebar seiring bertambahnya hari)
            </div>
        </div>
    </div>

    {{-- ── Suhu & Humidity Charts (side by side) ── --}}
    <div class="charts-row">
        <div class="chart-box">
            <div style="font-size:8.5pt; font-weight:bold; margin-bottom:4px;">Proyeksi Suhu (°C)</div>
            {!! $tempSvg !!}
        </div>
        <div class="chart-box">
            <div style="font-size:8.5pt; font-weight:bold; margin-bottom:4px;">Proyeksi Kelembapan (%)</div>
            {!! $humSvg !!}
        </div>
    </div>

    {{-- ── Data Table (page break before if long) ── --}}
    <div class="page-break"></div>
    <div class="faculty-bar" style="margin-bottom:10px;">
        <div class="name">Tabel Data Prediksi Harian — {{ $faculty->name }}</div>
        <div class="detail">Model: XGBoost v4 · Total: {{ count($labels) }} hari</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th class="r">CO₂ (ppm)</th>
                <th class="r">Batas Atas</th>
                <th class="r">Batas Bawah</th>
                <th class="r">Suhu (°C)</th>
                <th class="r">Kelembapan (%)</th>
                <th class="c">Status</th>
                <th class="c">Confidence</th>
            </tr>
        </thead>
        <tbody>
            @foreach($labels as $i => $label)
            @php
                $conf = $confidence[$i] ?? 0;
                $confClass = $conf >= 80 ? 'conf-high' : ($conf >= 65 ? 'conf-mid' : 'conf-low');
                $statusClass = match($status[$i] ?? 'Baik') {
                    'Baik'        => 'badge-baik',
                    'Sedang'      => 'badge-sedang',
                    'Tidak Sehat' => 'badge-tidak',
                    default       => '',
                };
            @endphp
            <tr>
                <td style="color:#94a3b8;">{{ $i + 1 }}</td>
                <td>{{ $label }}</td>
                <td class="r" style="color:#16a34a; font-weight:600;">{{ number_format($co2[$i] ?? 0, 0) }}</td>
                <td class="r" style="color:#64748b;">{{ number_format($co2Upper[$i] ?? 0, 0) }}</td>
                <td class="r" style="color:#64748b;">{{ number_format($co2Lower[$i] ?? 0, 0) }}</td>
                <td class="r" style="color:#ea580c;">{{ number_format($temp[$i] ?? 0, 1) }}</td>
                <td class="r" style="color:#2563eb;">{{ number_format($hum[$i] ?? 0, 1) }}</td>
                <td class="c"><span class="badge {{ $statusClass }}">{{ $status[$i] ?? '—' }}</span></td>
                <td class="c {{ $confClass }}">{{ $conf }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── Footer ── --}}
    <div class="footer">
        <span>AeroSense V2 — Sistem Pemantauan Kualitas Udara · Universitas Diponegoro</span>
        <span>Model: XGBoost v4 · Laporan dibuat: {{ $generatedAt }}</span>
    </div>

</body>
</html>
