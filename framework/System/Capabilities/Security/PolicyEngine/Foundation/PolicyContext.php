<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation;

final readonly class PolicyContext
{
    public function __construct(
        /** @var array<string, mixed> $attributes */
        public array $attributes = [],
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }
}
