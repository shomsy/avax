<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\ReadUserInfo;

use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capability\Oidc\OidcProviderInterface;
use RuntimeException;
use SensitiveParameter;

final readonly class ReadOidcUserInfo
{
    private OidcProviderInterface|null $oidcProvider;
    private JwtIdentityInterface       $jwtIdentity;

    public function __construct(
        #[\SensitiveParameter] JwtIdentityInterface $jwtIdentity,
        OidcProviderInterface|null                  $oidcProvider = null
    )
    {
        $this->jwtIdentity  = $jwtIdentity;
        $this->oidcProvider = $oidcProvider;
    }

    public function execute(#[SensitiveParameter] string $accessToken) : OidcUserInfo
    {
        $resolved = $this->jwtIdentity->resolve(token: $accessToken);

        if ($resolved === null) {
            throw new RuntimeException(message: 'Active access token is required for OIDC userinfo.');
        }

        $claims = [
            'sub'                => $this->subjectIdentifier(user: $resolved->user, clientId: $resolved->clientId),
            'preferred_username' => $resolved->user->getUsername(),
        ];

        if (in_array('email', $resolved->scopes, true)) {
            $claims['email'] = $resolved->user->getEmail()->value;
        }

        if (in_array('profile', $resolved->scopes, true)) {
            $claims['name'] = $resolved->user->getUsername();
        }

        if ($resolved->phishingResistant) {
            $claims['acr'] = 'phr';
        }

        return new OidcUserInfo(claims: $claims);
    }

    private function subjectIdentifier(\Avax\Auth\System\Capability\User\User $user, string|null $clientId) : string
    {
        if ($this->oidcProvider !== null && $clientId !== null && trim($clientId) !== '') {
            return $this->oidcProvider->subjectIdentifier(user: $user, clientId: $clientId);
        }

        return (string) $user->getId()->value;
    }
}
