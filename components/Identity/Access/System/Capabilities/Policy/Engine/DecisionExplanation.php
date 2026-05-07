<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\Policy\Engine;

final readonly class DecisionExplanation
{
    /** @param list<string> */
    public function __construct(
        public bool  $allowed,
        public array $reasons = [],
    ) {}

    public function toString() : string
    {
        return $this->allowed
            ? 'ALLOWED: ' . implode(' AND ', $this->reasons)
            : 'DENIED: ' . implode(' AND ', $this->reasons);
    }
}
