<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\HealthCheck;

/**
 * RouterHealthReport
 */
final readonly class RouterHealthReport
{
    public function __construct(
        public bool $healthy,
        /** @var list<string> */
        public array $findings,
    ) {}
}
