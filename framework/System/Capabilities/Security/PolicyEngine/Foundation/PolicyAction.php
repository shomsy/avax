<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation;

final readonly class PolicyAction
{
    public function __construct(
        public string $name,
        /** @var array<string, string> $attributes */
        public array $attributes = [],
    ) {
    }
}
