<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OpenIDConnect\Runtime\JarmResponse;

use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OpenIDConnect\Protocol\OidcProviderInterface;
use DateMalformedStringException;
use Random\RandomException;

final readonly class BuildJarmResponse
{
    public function __construct(private OidcProviderInterface $oidcProvider, private Clock $clock) {}

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function execute(BuildJarmResponseData $buildJarmResponseData) : JarmResponse
    {
        $issuedAt  = $this->clock->now();
        $expiresAt = $issuedAt->modify(modifier: '+5 minutes');
        $claims    = [
            'iss'  => $this->oidcProvider->readProviderMetadata()->issuer,
            'aud'  => trim(string: $buildJarmResponseData->clientId),
            'code' => $buildJarmResponseData->code,
            'iat'  => $issuedAt->getTimestamp(),
            'exp'  => $expiresAt->getTimestamp(),
            'jti'  => bin2hex(string: random_bytes(length: 16)),
        ];

        if ($buildJarmResponseData->state !== null && trim(string: $buildJarmResponseData->state) !== '') {
            $claims['state'] = trim(string: $buildJarmResponseData->state);
        }

        return new JarmResponse(
            responseJwt: $this->oidcProvider->issueJwt(claims: $claims),
            expiresAt  : $expiresAt,
        );
    }
}
