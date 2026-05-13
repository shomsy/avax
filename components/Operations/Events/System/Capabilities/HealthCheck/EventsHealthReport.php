<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Events\System\Capabilities\HealthCheck;

/**
 * EventsHealthReport
 */
final readonly class EventsHealthReport
{
    public function __construct(
        public bool $healthy,
        /** @var list<string> */
        public array $findings,
    ) {}
}
