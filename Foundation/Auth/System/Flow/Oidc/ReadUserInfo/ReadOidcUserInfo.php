<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\ReadUserInfo;

use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentityInterface;
use RuntimeException;
use SensitiveParameter;

final readonly class ReadOidcUserInfo
{
    public function __construct(
        #[\SensitiveParameter] private JwtIdentityInterface $jwtIdentity
    ) {}

    public function execute(#[SensitiveParameter] string $accessToken) : OidcUserInfo
    {
        $resolved = $this->jwtIdentity->resolve(token: $accessToken);

        if ($resolved === null) {
            throw new RuntimeException(message: 'Active access token is required for OIDC userinfo.');
        }

        $claims = [
            'sub' => (string) $resolved->user->getId()->value,
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
}
