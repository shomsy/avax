<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation;

final readonly class PolicyResource
{
    public function __construct(
        public string $type,
        public string $identifier,
        /** @var array<string, string> $attributes */
        public array $attributes = [],
    ) {
    }
}
