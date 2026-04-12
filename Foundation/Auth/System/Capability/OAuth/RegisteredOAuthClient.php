<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\OAuth;

/**
 * One-time client registration result.
 */
final readonly class RegisteredOAuthClient
{
    public function __construct(
        public OAuthClient  $client,
        public string|null  $plainTextSecret = null
    ) {}
}
