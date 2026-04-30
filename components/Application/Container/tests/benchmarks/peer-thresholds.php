<?php

declare(strict_types=1);

return [
    '*'                => [
        'max_time_ratio_vs_peer' => 1.75,
        'max_peak_ratio_vs_peer' => 2.00,
    ],
    'cold_boot'        => [
        'max_time_ratio_vs_peer' => 1.50,
        'max_peak_ratio_vs_peer' => 2.00,
    ],
    'warm_boot'        => [
        'max_time_ratio_vs_peer' => 1.50,
        'max_peak_ratio_vs_peer' => 2.00,
    ],
    'compile_time'     => [
        'max_time_ratio_vs_peer' => 1.50,
        'max_peak_ratio_vs_peer' => 2.00,
    ],
    'deep_graph'       => [
        'max_time_ratio_vs_peer' => 2.25,
        'max_peak_ratio_vs_peer' => 2.00,
    ],
    'wide_graph'       => [
        'max_time_ratio_vs_peer' => 2.25,
        'max_peak_ratio_vs_peer' => 2.00,
    ],
    'deferred_service' => [
        'max_time_ratio_vs_peer' => 2.25,
        'max_peak_ratio_vs_peer' => 2.00,
    ],
    'deferred_provider' => [
        'max_time_ratio_vs_peer' => 2.25,
        'max_peak_ratio_vs_peer' => 2.00,
    ],
];
