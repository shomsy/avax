<?php

declare(strict_types=1);

namespace Avax\Components\Operations\RuntimeSupervision\System\Configuration;

final readonly class RuntimeSupervisionConfiguration
{
    public function __construct(
        public bool $enabled = true,
        public int  $defaultFailureThreshold = 3,
        public int  $defaultCooldownSeconds = 30,
        public int  $monitorIntervalSeconds = 10,
        public int  $maxRestartsPerHour = 10,
    ) {}
}
