<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Configuration;

final readonly class ExternalIdentityConfiguration
{
    /**
     * @param list<string> $allowedProviders
     */
    public function __construct(
        public array $allowedProviders = ['google', 'github', 'facebook'],
        public bool  $autoLink = false,
    ) {}
}
