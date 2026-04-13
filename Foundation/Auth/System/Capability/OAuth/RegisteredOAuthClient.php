<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth;

use SensitiveParameter;

/**
 * One-time client registration result.
 */
final readonly class RegisteredOAuthClient
{
    public function __construct(
        public OAuthClient                       $client,
        #[SensitiveParameter] public string|null $plainTextSecret = null
    ) {}
}
