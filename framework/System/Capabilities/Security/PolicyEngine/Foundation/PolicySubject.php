<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation;

final readonly class PolicySubject
{
    public function __construct(
        public string $id,
        public string $type = 'user',
        /** @var array<string, string> $attributes */
        public array $attributes = [],
    ) {
    }
}
