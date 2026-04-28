<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\ExternalIdentity\OAuth\Support;

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
