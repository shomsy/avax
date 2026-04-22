<?php

declare(strict_types=1);

return [
    'cold_boot'               => ['max_time_ms' => 40.0, 'max_peak_mb' => 8.0],
    'warm_boot'               => ['max_time_ms' => 10.0, 'max_peak_mb' => 8.0],
    'cached_get'              => ['max_time_ms' => 6500.0, 'max_peak_mb' => 8.0],
    'worker_cached_get'       => ['max_time_ms' => 1500.0, 'max_peak_mb' => 8.0],
    'uncached_resolve'        => ['max_time_ms' => 6500.0, 'max_peak_mb' => 8.0],
    'request_lifecycle'       => ['max_time_ms' => 3000.0, 'max_peak_mb' => 8.0],
    'deep_graph'              => ['max_time_ms' => 5000.0, 'max_peak_mb' => 8.0],
    'wide_graph'              => ['max_time_ms' => 2000.0, 'max_peak_mb' => 8.0],
    'scoped_service'          => ['max_time_ms' => 3500.0, 'max_peak_mb' => 8.0],
    'pooled_service'          => ['max_time_ms' => 3500.0, 'max_peak_mb' => 8.0],
    'lazy_service'            => ['max_time_ms' => 9000.0, 'max_peak_mb' => 8.0],
    'deferred_service'        => ['max_time_ms' => 4500.0, 'max_peak_mb' => 8.0],
    'deferred_provider'       => ['max_time_ms' => 4500.0, 'max_peak_mb' => 8.0],
    'function_call_injection' => ['max_time_ms' => 4000.0, 'max_peak_mb' => 8.0],
    'property_injection'      => ['max_time_ms' => 7500.0, 'max_peak_mb' => 8.0],
    'method_injection'        => ['max_time_ms' => 3000.0, 'max_peak_mb' => 8.0],
    'compile_time'            => ['max_time_ms' => 50.0, 'max_peak_mb' => 8.0],
    'prod_compiled_get'       => ['max_time_ms' => 1500.0, 'max_peak_mb' => 8.0],
    'generated_get'           => ['max_time_ms' => 1500.0, 'max_peak_mb' => 8.0],
    'dev_compiled_get'        => ['max_time_ms' => 2500.0, 'max_peak_mb' => 8.0],
];
