<?php

declare(strict_types=1);

namespace Avax\Components\Operations\BackgroundProcesses\System\Configuration;

final readonly class BackgroundProcessesConfiguration
{
    public function __construct(
        public int    $maxRestarts = 5,
        public int    $restartWindowSeconds = 60,
        public int    $healthCheckIntervalSeconds = 30,
        public string $defaultRestartPolicy = 'always',
    ) {}

    public static function make() : self
    {
        return new self();
    }
}
