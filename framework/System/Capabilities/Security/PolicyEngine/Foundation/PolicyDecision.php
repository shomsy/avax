<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Security\PolicyEngine\Foundation;

final readonly class PolicyDecision
{
    public function __construct(
        public PolicyEffect $effect,
        public string $reason = '',
    ) {
    }

    public static function allow(string $reason = ''): self
    {
        return new self(PolicyEffect::Allow, $reason);
    }

    public static function deny(string $reason = ''): self
    {
        return new self(PolicyEffect::Deny, $reason);
    }

    public function isAllowed(): bool
    {
        return $this->effect === PolicyEffect::Allow;
    }
}
