<?php

declare(strict_types=1);

/**
 * Runtime configuration.
 *
 * Return an array with runtime settings.
 * If this file does not exist, AvaX uses sensible defaults.
 */
return [
    'default_runtime' => 'built-in',
    'host' => '127.0.0.1',
    'port' => 8000,
    'memory_guard_soft_limit' => 128 * 1024 * 1024,   // 128MB
    'memory_guard_hard_limit' => 256 * 1024 * 1024,   // 256MB
    'max_requests' => 0,                               // 0 = unlimited
    'warm_safety_enabled' => true,
    'serve_mode' => 'dev',
];
