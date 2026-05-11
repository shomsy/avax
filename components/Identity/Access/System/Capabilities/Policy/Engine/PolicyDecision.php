<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\Policy\Engine;

final readonly class PolicyDecision
{
    public function __construct(
        public bool    $allowed,
        public string|null $reason,
    ) {}

    public static function deny(string|null $reason) : self
    {
        return new self(false, $reason);
    }

    public static function allow(string|null $reason = null) : self
    {
        return new self(true, $reason);
    }
}
