<?php
declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Configuration;

/**
 * AuthConfiguration - Value object for auth settings.
 * 1:1 alignment with refactor.md.
 */
final readonly class AuthConfiguration
{
    public function __construct(
        public string $guard = 'web',
        public array $providers = [],
    ) {}
}
