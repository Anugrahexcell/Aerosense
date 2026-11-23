<x-filament-panels::page>

    {{-- ── Auto-refresh every 60 s (Livewire polling) ─────────── --}}
    <div wire:poll.60000ms></div>

    {{-- ── Filter Bar ─────────────────────────────────────────── --}}
    <x-filament::section>
        <div style="display:flex; flex-direction:row; align-items:flex-end; gap:1rem; flex-wrap:wrap;">

            <div style="flex:2; min-width:160px;">
                <label style="display:block; font-size:0.8rem; font-weight:600; margin-bottom:0.35rem; color:#6b7280;">
                    🏫 Fakultas
                </label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="faculty_id">
                        @foreach($this->getFacultyOptions() as $id => $name)
                            <option value="{{ $id }}" @selected($faculty_id == $id)>{{ $name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div style="flex:1; min-width:140px;">
                <label style="display:block; font-size:0.8rem; font-weight:600; margin-bottom:0.35rem; color:#6b7280;">
                    📅 Periode
                </label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="period">
                        @foreach($this->getPeriodOptions() as $val => $label)
                            <option value="{{ $val }}" @selected($period === $val)>{{ $label }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            {{-- Live indicator --}}
            <div style="display:flex; align-items:center; gap:0.5rem; padding-bottom:0.25rem; color:#6b7280; font-size:0.8rem;">
                <span style="width:8px; height:8px; border-radius:50%; background:#22c55e;
                             display:inline-block; animation:aeroPulse 2s ease-in-out infinite;"></span>
                Data langsung dari sensor IoT
            </div>
        </div>
    </x-filament::section>

    {{-- ── Latest Stats Cards ───────────────────────────────────── --}}
    @php $stats = $this->getLatestStats(); @endphp
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:1rem;">

        {{-- Suhu --}}
        <x-filament::section>
            <div style="border-left:4px solid #f97316; padding-left:0.75rem;">
                <div style="display:flex; align-items:center; gap:0.4rem; color:#6b7280; font-size:0.78rem; margin-bottom:0.4rem;">
                    <x-heroicon-m-fire style="width:13px;height:13px;color:#f97316;" /> Suhu Terkini
                </div>
                <div style="font-size:1.9rem; font-weight:700; line-height:1.1;">
                    {{ $stats['temperature'] }}<small style="font-size:1rem;font-weight:500;">°C</small>
                </div>
                <div style="font-size:0.72rem; color:#9ca3af; margin-top:0.3rem;">
                    {{ $stats['recorded_at'] }}
                </div>
            </div>
        </x-filament::section>

        {{-- Kelembapan --}}
        <x-filament::section>
            <div style="border-left:4px solid #3b82f6; padding-left:0.75rem;">
                <div style="display:flex; align-items:center; gap:0.4rem; color:#6b7280; font-size:0.78rem; margin-bottom:0.4rem;">
                    <x-heroicon-m-cloud style="width:13px;height:13px;color:#3b82f6;" /> Kelembapan Terkini
                </div>
                <div style="font-size:1.9rem; font-weight:700; line-height:1.1;">
                    {{ $stats['humidity'] }}<small style="font-size:1rem;font-weight:500;">%</small>
                </div>
                <div style="font-size:0.72rem; color:#9ca3af; margin-top:0.3rem;">
                    {{ $stats['recorded_at'] }}
                </div>
            </div>
        </x-filament::section>

        {{-- CO₂ --}}
        <x-filament::section>
            <div style="border-left:4px solid #22c55e; padding-left:0.75rem;">
                <div style="display:flex; align-items:center; gap:0.4rem; color:#6b7280; font-size:0.78rem; margin-bottom:0.4rem;">
                    <x-heroicon-m-sparkles style="width:13px;height:13px;color:#22c55e;" /> CO₂ Terkini
                </div>
                <div style="font-size:1.9rem; font-weight:700; line-height:1.1;">
                    {{ $stats['co2'] }}<small style="font-size:1rem;font-weight:500;"> ppm</small>
                </div>
                <div style="font-size:0.72rem; color:#9ca3af; margin-top:0.3rem;">
                    {{ $stats['recorded_at'] }}
                </div>
            </div>
        </x-filament::section>

        {{-- Status + Total readings --}}
        <x-filament::section>
            <div style="border-left:4px solid #a855f7; padding-left:0.75rem;">
                <div style="display:flex; align-items:center; gap:0.4rem; color:#6b7280; font-size:0.78rem; margin-bottom:0.4rem;">
                    <x-heroicon-m-shield-check style="width:13px;height:13px;color:#a855f7;" /> Status Kualitas
                </div>
                <div style="font-size:1.25rem; font-weight:700; line-height:1.2;">
                    {{ $stats['status'] }}
                </div>
                <div style="font-size:0.72rem; color:#9ca3af; margin-top:0.3rem;">
                    {{ $stats['total'] }} data dalam periode ini
                </div>
            </div>
        </x-filament::section>

    </div>

    {{-- ── Chart.js Line Chart ──────────────────────────────────── --}}
    <x-filament::section>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
            <div>
                <div style="font-weight:600; font-size:0.95rem;">Tren Kualitas Udara — Data per Jam</div>
                <div style="font-size:0.78rem; color:#9ca3af;">
                    Setiap titik = 1 pembacaan sensor (1 jam). Tidak ada rata-rata. CO₂ menggunakan sumbu kanan.
                </div>
            </div>
        </div>
        <div style="padding:0.25rem 0 0.5rem; position:relative; height:380px;">
            <canvas id="trendChart"></canvas>
        </div>
    </x-filament::section>

    {{-- ── Read-Only Data Table ─────────────────────────────────── --}}
    <x-filament::section
        heading="📋 Log Data Sensor (Read-Only)"
        description="Pembacaan langsung dari IoT — tidak dapat diedit. Urutkan atau cari untuk analisis lebih lanjut.">
        {{ $this->table }}
    </x-filament::section>

    {{-- ── Chart.js Script ─────────────────────────────────────── --}}
    @php $chartData = $this->getChartData(); @endphp
    {{-- Chart.js loaded from local npm bundle (app.js) — no CDN needed --}}
    <script>
        (function () {
            const labels      = @json($chartData['labels']);
            const temperature = @json($chartData['temperature']);
            const humidity    = @json($chartData['humidity']);
            const co2         = @json($chartData['co2']);

            function buildChart() {
                const canvas = document.getElementById('trendChart');
                if (!canvas) return;

                // Destroy existing instance on Livewire re-render
                if (canvas._chartInstance) {
                    canvas._chartInstance.destroy();
                    canvas._chartInstance = null;
                }

                // Empty state guard
                if (!labels || labels.length === 0) {
                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    ctx.fillStyle = '#9ca3af';
                    ctx.font = '14px sans-serif';
                    ctx.textAlign = 'center';
                    ctx.fillText('Belum ada data untuk periode dan fakultas yang dipilih.',
                                  canvas.width / 2, canvas.height / 2);
                    return;
                }

                canvas._chartInstance = new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: 'Suhu (°C)',
                                data: temperature,
                                borderColor: '#f97316',
                                backgroundColor: 'rgba(249,115,22,0.10)',
                                borderWidth: 2,
                                tension: 0.35,
                                fill: true,
                                pointRadius: 3,
                                pointHoverRadius: 6,
                                yAxisID: 'yLeft',   // left axis — °C / %
                            },
                            {
                                label: 'Kelembapan (%)',
                                data: humidity,
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59,130,246,0.10)',
                                borderWidth: 2,
                                tension: 0.35,
                                fill: true,
                                pointRadius: 3,
                                pointHoverRadius: 6,
                                yAxisID: 'yLeft',   // shared left axis
                            },
                            {
                                label: 'CO₂ (ppm)',
                                data: co2,
                                borderColor: '#22c55e',
                                backgroundColor: 'rgba(34,197,94,0.08)',
                                borderWidth: 2,
                                tension: 0.35,
                                fill: false,
                                pointRadius: 3,
                                pointHoverRadius: 6,
                                yAxisID: 'yCO2',    // separate right axis — ppm
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16 } },
                            tooltip: {
                                callbacks: {
                                    label: ctx => {
                                        const unit = ctx.dataset.yAxisID === 'yCO2' ? ' ppm' :
                                                     ctx.dataset.label.includes('Suhu') ? ' °C' : ' %';
                                        return ` ${ctx.dataset.label}: ${ctx.parsed.y}${unit}`;
                                    },
                                },
                            },
                        },
                        scales: {
                            x: {
                                ticks: { maxTicksLimit: 12, maxRotation: 40, font: { size: 11 } },
                                grid:  { color: 'rgba(0,0,0,0.04)' },
                            },
                            yLeft: {
                                type: 'linear',
                                position: 'left',
                                beginAtZero: false,
                                title: { display: true, text: 'Suhu (°C) / Kelembapan (%)', font: { size: 11 } },
                                grid: { color: 'rgba(0,0,0,0.05)' },
                                ticks: { font: { size: 11 } },
                            },
                            yCO2: {
                                type: 'linear',
                                position: 'right',
                                beginAtZero: false,
                                title: { display: true, text: 'CO₂ (ppm)', font: { size: 11 } },
                                grid: { drawOnChartArea: false },   // no grid lines for right axis
                                ticks: { font: { size: 11 } },
                            },
                        },
                    },
                });
            }

            // Initial render
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', buildChart);
            } else {
                buildChart();
            }

            // Re-render after Livewire updates (filter change / poll)
            document.addEventListener('livewire:navigated', buildChart);
            window.addEventListener('livewire:update', () => setTimeout(buildChart, 120));
        })();
    </script>

    <style>
        @keyframes aeroPulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.2; }
        }
    </style>

</x-filament-panels::page>
