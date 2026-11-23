<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Identity
    |--------------------------------------------------------------------------
    */
    'name'    => 'AeroSenseV2',
    'version' => '2.0',
    'university' => 'Universitas Diponegoro',

    /*
    |--------------------------------------------------------------------------
    | Monitoring Stats (shown on Dashboard hero)
    |--------------------------------------------------------------------------
    */
    'total_faculties'  => 13,
    'monitoring_hours' => '24/7',
    'model_accuracy'   => 91,                          // XGBoost v4 temp/humidity R²≈0.91
    'model_name'       => 'AeroSense v2 (XGBoost v4)',
    'model_algorithm'  => 'XGBoost',
    'prediction_hours' => 1,                           // 60-minute ahead forecast

    /*
    |--------------------------------------------------------------------------
    | Air Quality Status Thresholds (CO₂ in ppm)
    |--------------------------------------------------------------------------
    */
    'thresholds' => [
        // Aligned with XGBoost v4 zone classification
        'baik'        => [0,    1000],   // CO2 <= 1000 ppm
        'sedang'      => [1001, 2000],   // CO2 1001-2000 ppm
        'tidak_sehat' => [2001, PHP_INT_MAX],  // CO2 > 2000 ppm
    ],

    /*
    |--------------------------------------------------------------------------
    | Real-Time Polling Interval (seconds) — used by JavaScript
    |--------------------------------------------------------------------------
    */
    'polling_interval' => 30,

    /*
    |--------------------------------------------------------------------------
    | Historical Trend Default Range (days)
    |--------------------------------------------------------------------------
    */
    'trend_days' => 30,

];
