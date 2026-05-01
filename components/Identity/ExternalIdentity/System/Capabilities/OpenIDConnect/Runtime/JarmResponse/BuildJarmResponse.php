<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Runtime\JarmResponse;

use Avax\Components\Identity\Auth\System\Foundation\Clock;
use Avax\Components\Identity\ExternalIdentity\System\Capabilities\OpenIDConnect\Protocol\OidcProviderInterface;
use DateMalformedStringException;
use Random\RandomException;

final readonly class BuildJarmResponse
{
    public function __construct(private OidcProviderInterface $oidcProvider, private Clock $clock) {}

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
    public function execute(BuildJarmResponseData $data): JarmResponse
    {
        $issuedAt = $this->clock->now();
        $expiresAt = $issuedAt->modify(modifier: '+5 minutes');
        $claims   = [
            'iss' => $this->oidcProvider->readProviderMetadata()->issuer,
            'aud' => trim(string: $data->clientId),
            'code' => $data->code,
            'iat' => $issuedAt->getTimestamp(),
            'exp' => $expiresAt->getTimestamp(),
            'jti' => bin2hex(string: random_bytes(length: 16)),
        ];

        if ($data->state !== null && trim(string: $data->state) !== '') {
            $claims['state'] = trim(string: $data->state);
        }

        return new JarmResponse(
            responseJwt: $this->oidcProvider->issueJwt(claims: $claims),
            expiresAt  : $expiresAt,
        );
    }
}
