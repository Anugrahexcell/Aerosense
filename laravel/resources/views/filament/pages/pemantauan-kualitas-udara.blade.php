<x-filament-panels::page>

    {{-- ⟳ Auto-refresh every 10 seconds to pick up new IoT sensor data --}}
    <div wire:poll.60s></div>

    @php
        $latestReading = $faculty_id
            ? \App\Models\SensorReading::where('faculty_id', $faculty_id)->latest('recorded_at')->first()
            : null;
        $suhuVal       = $latestReading ? number_format($latestReading->temperature, 1) : '—';
        $kelembapanVal = $latestReading ? number_format($latestReading->humidity, 1)    : '—';
        $co2Val        = $latestReading ? number_format($latestReading->co2, 1)         : '—';
        $lastUpdated   = $latestReading ? $latestReading->recorded_at->diffForHumans()  : 'Belum ada data';
    @endphp

    {{-- Filter Section + Live Badge --}}
    <x-filament::section>
        {{-- Live badge top-right --}}
        <div style="display:flex; justify-content:flex-end; margin-bottom:0.6rem;">
            <div style="display:flex; align-items:center; gap:0.4rem; font-size:0.76rem; color:#6b7280;">
                <span style="width:8px; height:8px; border-radius:50%; background:#22c55e; display:inline-block; animation:aeroPulse 1.5s infinite;"></span>
                Pembaruan otomatis &bull; Terakhir: <strong>{{ $lastUpdated }}</strong>
            </div>
        </div>

        {{-- Filters in ONE ROW --}}
        <div style="display:flex; flex-direction:row; align-items:flex-end; gap:1rem; flex-wrap:nowrap;">

            <div style="flex:1.5; min-width:0;">
                <label style="display:block; font-size:0.8rem; font-weight:500; margin-bottom:0.3rem; color:#6b7280;">Fakultas</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="faculty_id">
                        @foreach($this->getFacultyOptions() as $id => $name)
                            <option value="{{ $id }}" @selected($faculty_id == $id)>{{ $name }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div style="flex:1.5; min-width:0;">
                <label style="display:block; font-size:0.8rem; font-weight:500; margin-bottom:0.3rem; color:#6b7280;">Rentang Waktu (5 jam)</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="time_range">
                        @foreach($this->getTimeRangeOptions() as $value => $label)
                            <option value="{{ $value }}" @selected($time_range === $value)>{{ $label }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div style="flex-shrink:0;">
                <label style="display:block; font-size:0.8rem; font-weight:500; margin-bottom:0.3rem; color:#6b7280;">Parameter</label>
                <div style="display:flex; gap:0.5rem; align-items:center;">
                    <button type="button" wire:click="toggleParameter('suhu')" wire:loading.attr="disabled"
                        style="display:inline-flex; align-items:center; gap:0.35rem; border-radius:9999px; padding:0.4rem 0.9rem; font-size:0.8rem; font-weight:600; cursor:pointer; transition:all 0.15s; white-space:nowrap; border:none; outline:none;
                        {{ in_array('suhu', $parameters) ? 'background:#f97316; color:#fff;' : 'background:transparent; color:#f97316; outline:1.5px solid #f97316;' }}">
                        <x-heroicon-m-fire style="width:13px;height:13px;flex-shrink:0;" /> Suhu
                    </button>
                    <button type="button" wire:click="toggleParameter('kelembapan')" wire:loading.attr="disabled"
                        style="display:inline-flex; align-items:center; gap:0.35rem; border-radius:9999px; padding:0.4rem 0.9rem; font-size:0.8rem; font-weight:600; cursor:pointer; transition:all 0.15s; white-space:nowrap; border:none; outline:none;
                        {{ in_array('kelembapan', $parameters) ? 'background:#3b82f6; color:#fff;' : 'background:transparent; color:#3b82f6; outline:1.5px solid #3b82f6;' }}">
                        <x-heroicon-m-cloud style="width:13px;height:13px;flex-shrink:0;" /> Kelembapan
                    </button>
                    <button type="button" wire:click="toggleParameter('co2')" wire:loading.attr="disabled"
                        style="display:inline-flex; align-items:center; gap:0.35rem; border-radius:9999px; padding:0.4rem 0.9rem; font-size:0.8rem; font-weight:600; cursor:pointer; transition:all 0.15s; white-space:nowrap; border:none; outline:none;
                        {{ in_array('co2', $parameters) ? 'background:#22c55e; color:#fff;' : 'background:transparent; color:#22c55e; outline:1.5px solid #22c55e;' }}">
                        <x-heroicon-m-sparkles style="width:13px;height:13px;flex-shrink:0;" /> CO₂
                    </button>
                </div>
            </div>
        </div>
    </x-filament::section>

    {{-- Stats Cards (reactive — pulled fresh on every poll) --}}
    @if(count($parameters) > 0)
    <div style="display:grid; grid-template-columns:repeat({{ count($parameters) }},1fr); gap:1rem;">
        @if(in_array('suhu', $parameters))
        <x-filament::section>
            <div style="border-left:4px solid #f97316; padding-left:0.75rem;">
                <div style="display:flex; align-items:center; gap:0.4rem; color:#6b7280; font-size:0.82rem; margin-bottom:0.4rem;">
                    <x-heroicon-m-fire style="width:15px;height:15px;color:#f97316;flex-shrink:0;" /> Suhu (°C)
                </div>
                <div style="font-size:2.2rem; font-weight:700; line-height:1.1;">{{ $suhuVal }}</div>
            </div>
        </x-filament::section>
        @endif
        @if(in_array('kelembapan', $parameters))
        <x-filament::section>
            <div style="border-left:4px solid #3b82f6; padding-left:0.75rem;">
                <div style="display:flex; align-items:center; gap:0.4rem; color:#6b7280; font-size:0.82rem; margin-bottom:0.4rem;">
                    <x-heroicon-m-cloud style="width:15px;height:15px;color:#3b82f6;flex-shrink:0;" /> Kelembapan (%)
                </div>
                <div style="font-size:2.2rem; font-weight:700; line-height:1.1;">{{ $kelembapanVal }}</div>
            </div>
        </x-filament::section>
        @endif
        @if(in_array('co2', $parameters))
        <x-filament::section>
            <div style="border-left:4px solid #22c55e; padding-left:0.75rem;">
                <div style="display:flex; align-items:center; gap:0.4rem; color:#6b7280; font-size:0.82rem; margin-bottom:0.4rem;">
                    <x-heroicon-m-sparkles style="width:15px;height:15px;color:#22c55e;flex-shrink:0;" /> CO₂ (ppm)
                </div>
                <div style="font-size:2.2rem; font-weight:700; line-height:1.1;">{{ $co2Val }}</div>
            </div>
        </x-filament::section>
        @endif
    </div>
    @endif


    {{-- Chart has moved to the dedicated "Grafik Tren Data" page in the sidebar --}}
    <x-filament::section>
        <div style="text-align:center; padding:1.5rem; color:#6b7280; font-size:0.85rem;">
            <x-heroicon-o-chart-bar style="width:32px;height:32px;margin:0 auto 0.5rem;color:#d1d5db;" />
            <div>Grafik tren tersedia di halaman <strong>Grafik Tren Data</strong> pada sidebar.</div>
        </div>
    </x-filament::section>

</x-filament-panels::page>

