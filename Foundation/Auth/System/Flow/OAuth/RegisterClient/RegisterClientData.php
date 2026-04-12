<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\OAuth\RegisterClient;

use Avax\Auth\System\Capability\OAuth\OAuthClientType;

final readonly class RegisterClientData
{
    /**
     * @param list<string> $redirectUris
     * @param list<string> $allowedScopes
     */
    public function __construct(
        public string          $name,
        public OAuthClientType $type,
        public array           $redirectUris,
        public array           $allowedScopes = []
    ) {}
}
