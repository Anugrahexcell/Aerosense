<?php

namespace App\Jobs;

use App\Models\SensorReading;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncToGoogleSheets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Max retry attempts if Google Sheets is slow */
    public int $tries = 3;

    /** Timeout per attempt (seconds) */
    public int $timeout = 20;

    public function __construct(
        private readonly SensorReading $reading
    ) {}

    /**
     * Push the sensor reading to Google Sheets via Apps Script webhook.
     * Runs in a background queue worker — never blocks the ESP32 response.
     */
    public function handle(): void
    {
        $webhookUrl = config('services.google_sheets.webhook_url');

        if (empty($webhookUrl)) {
            Log::info('SyncToGoogleSheets: no webhook URL configured, skipping.');
            return;
        }

        $this->reading->loadMissing('faculty');

        $response = Http::timeout(15)->post($webhookUrl, [
            'timestamp'   => $this->reading->recorded_at->toIso8601String(),
            'faculty'     => $this->reading->faculty->name ?? 'Unknown',
            'temperature' => (float) $this->reading->temperature,
            'humidity'    => (float) $this->reading->humidity,
            'co2'         => (float) $this->reading->co2,
            'status'      => $this->reading->computed_status,
        ]);

        Log::info("Google Sheets sync OK — reading #{$this->reading->id} | HTTP {$response->status()}");
    }

    public function failed(\Throwable $exception): void
    {
        Log::warning("Google Sheets sync FAILED — reading #{$this->reading->id}: " . $exception->getMessage());
    }
}
