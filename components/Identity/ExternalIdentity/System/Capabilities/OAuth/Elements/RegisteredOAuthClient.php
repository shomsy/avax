<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OAuth\Elements;

use SensitiveParameter;

/**
 * One-time client registration result.
 */
final readonly class RegisteredOAuthClient
{
    public function __construct(
        public OAuthClient $client,
        #[SensitiveParameter]
        public ?string     $plainTextSecret = null,
    ) {}
}
