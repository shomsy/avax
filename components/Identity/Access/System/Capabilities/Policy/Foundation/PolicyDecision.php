<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\Policy\Foundation;

final readonly class PolicyDecision
{
    public function __construct(
        public bool $allowed,
        public string|null $reason = null,
    ) {}

    public static function allow(string|null $reason = null) : self
    {
        return new self(true, $reason);
    }

    public static function deny(string|null $reason = null) : self
    {
        return new self(false, $reason);
    }
}
