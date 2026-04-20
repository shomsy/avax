<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\OAuth;

use SensitiveParameter;

/**
 * One-time client registration result.
 */
final readonly class RegisteredOAuthClient
{
    public string|null $plainTextSecret;
    public OAuthClient $client;

    public function __construct(
        OAuthClient                       $client,
        #[SensitiveParameter] string|null $plainTextSecret = null
    )
    {
        $this->client          = $client;
        $this->plainTextSecret = $plainTextSecret;
    }
}
