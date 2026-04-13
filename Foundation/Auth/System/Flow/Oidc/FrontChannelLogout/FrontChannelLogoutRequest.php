<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\FrontChannelLogout;

/**
 * Represents a front-channel logout request from a relying party.
 */
final readonly class FrontChannelLogoutRequest
{
    public function __construct(
        public string|null $idTokenHint,
        public string|null $clientId,
        public string|null $logoutUri,
        public string|null $postLogoutRedirectUri,
        public string|null $state
    ) {}

    public function hasPostLogoutRedirect() : bool
    {
        return $this->postLogoutRedirectUri !== null;
    }

    /**
     * @return list<string>
     */
    public function getRegisteredLogoutUris() : array
    {
        if ($this->logoutUri === null) {
            return [];
        }

        return array_map('trim', explode(',', $this->logoutUri));
    }
}