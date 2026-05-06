<?php

declare(strict_types=1);

namespace Avax\Components\DeveloperTools\Testing\System\Capabilities\ContractTesting;

final readonly class BreakingChangesReport
{
    public function __construct(
        public array $changes = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'has_breaking' => $this->hasBreaking(),
            'changes' => array_map(
                static fn ($c): array => is_array($c) ? $c : (array) $c,
                $this->changes,
            ),
        ];
    }

    public function hasBreaking(): bool
    {
        return $this->changes !== [];
    }
}
