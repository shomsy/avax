<?php

declare(strict_types=1);

namespace Avax\Components\Security\Cryptography\System\Capabilities\HealthCheck;

/**
 * CryptographyHealthReport
 */
final readonly class CryptographyHealthReport
{
    public function __construct(
        public bool $healthy,
        /** @var list<string> */
        public array $findings,
    ) {}
}
