<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Federation;

final readonly class FederatedIdentity
{
    /**
     * @param list<string> $groups
     */
    public function __construct(
        public string $subject,
        public string $email,
        public string $displayName,
        public array $groups = [],
        public bool $emailVerified = true
    ) {}
}
