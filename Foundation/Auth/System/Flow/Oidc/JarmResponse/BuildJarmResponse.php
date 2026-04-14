<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\JarmResponse;

use Avax\Auth\System\Capability\Oidc\OidcProviderInterface;
use Avax\Auth\System\Foundation\Clock;
use DateTimeImmutable;

final readonly class BuildJarmResponse
{
    public function __construct(
        private OidcProviderInterface $oidcProvider,
        private Clock $clock
    ) {}

    public function execute(BuildJarmResponseData $data) : JarmResponse
    {
        $issuedAt = $this->clock->now();
        $expiresAt = $issuedAt->modify(modifier: '+5 minutes');
        $claims = [
            'iss' => $this->oidcProvider->readProviderMetadata()->issuer,
            'aud' => trim($data->clientId),
            'code' => $data->code,
            'iat' => $issuedAt->getTimestamp(),
            'exp' => $expiresAt->getTimestamp(),
            'jti' => bin2hex(random_bytes(16)),
        ];

        if ($data->state !== null && trim($data->state) !== '') {
            $claims['state'] = trim($data->state);
        }

        return new JarmResponse(
            responseJwt: $this->oidcProvider->issueJwt(claims: $claims),
            expiresAt   : $expiresAt
        );
    }
}
