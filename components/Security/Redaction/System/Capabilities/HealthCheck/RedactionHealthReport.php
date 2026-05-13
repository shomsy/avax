<?php

declare(strict_types=1);

namespace Avax\Components\Security\Redaction\System\Capabilities\HealthCheck;

/**
 * RedactionHealthReport
 */
final readonly class RedactionHealthReport
{
    public function __construct(
        public bool $healthy,
        /** @var list<string> */
        public array $findings,
    ) {}
}
