<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\ReadUserInfo;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OidcProviderInterface;
use RuntimeException;
use SensitiveParameter;

final readonly class ReadOidcUserInfo
{
    public function __construct(
        #[SensitiveParameter]
        private JwtIdentityInterface $jwtIdentity,
        private ?OidcProviderInterface $oidcProvider = null,
    ) {}

    public function execute(#[SensitiveParameter] string $accessToken): OidcUserInfo
    {
        $resolved = $this->jwtIdentity->resolve(token: $accessToken);

        if ($resolved === null) {
            throw new RuntimeException(message: 'Active access token is required for OIDC userinfo.');
        }

        $claims = [
            'sub' => $this->subjectIdentifier(user: $resolved->user, clientId: $resolved->clientId),
            'preferred_username' => $resolved->user->getUsername(),
        ];

        if (in_array(needle: 'email', haystack: $resolved->scopes, strict: true)) {
            $claims['email'] = $resolved->user->getEmail()->value;
        }

        if (in_array(needle: 'profile', haystack: $resolved->scopes, strict: true)) {
            $claims['name'] = $resolved->user->getUsername();
        }

        if ($resolved->phishingResistant) {
            $claims['acr'] = 'phr';
        }

        return new OidcUserInfo(claims: $claims);
    }

    private function subjectIdentifier(User $user, ?string $clientId): string
    {
        if ($this->oidcProvider !== null && $clientId !== null && trim(string: $clientId) !== '') {
            return $this->oidcProvider->subjectIdentifier(user: $user, clientId: $clientId);
        }

        return (string) $user->getId()->value;
    }
}
