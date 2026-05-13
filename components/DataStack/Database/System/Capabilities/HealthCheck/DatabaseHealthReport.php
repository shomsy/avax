<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\HealthCheck;

/**
 * DatabaseHealthReport
 */
final readonly class DatabaseHealthReport
{
    public function __construct(
        public bool $healthy,
        /** @var list<string> */
        public array $findings,
    ) {}
}
