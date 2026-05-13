<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Logging\System\Capabilities\HealthCheck;

/**
 * LoggingHealthReport
 */
final readonly class LoggingHealthReport
{
    public function __construct(
        public bool $healthy,
        /** @var list<string> */
        public array $findings,
    ) {}
}
