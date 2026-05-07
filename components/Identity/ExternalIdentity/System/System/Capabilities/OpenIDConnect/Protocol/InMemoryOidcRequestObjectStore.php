<?php

declare(strict_types=1);

namespace Avax\Components\Identity\ExternalIdentity\System\System\Capabilities\OpenIDConnect\Protocol;

use DateTimeImmutable;

final class InMemoryOidcRequestObjectStore implements OidcRequestObjectStoreInterface
{
    /** @var array<string, OidcRequestObject> */
    private array $objects = [];

    public function store(
        string            $requestUri,
        array             $claims,
        DateTimeImmutable $expiresAt,
        bool              $signatureVerified = false,
        ?string           $signingAlgorithm = null,
        ?string           $signingClientId = null,
    ) : OidcRequestObject
    {
        $oidcRequestObject = new OidcRequestObject(
            requestUri       : $requestUri,
            claims           : $claims,
            createdAt        : new DateTimeImmutable(),
            expiresAt        : $expiresAt,
            signatureVerified: $signatureVerified,
            signingAlgorithm : $signingAlgorithm,
            signingClientId  : $signingClientId,
        );

        $this->objects[$requestUri] = $oidcRequestObject;

        return $oidcRequestObject;
    }

    public function consume(string $requestUri) : ?OidcRequestObject
    {
        $object = $this->find(requestUri: $requestUri);

        if (! $object instanceof OidcRequestObject) {
            return null;
        }

        unset($this->objects[$requestUri]);

        return $object;
    }

    public function find(string $requestUri) : ?OidcRequestObject
    {
        $object = $this->objects[$requestUri] ?? null;

        if ($object === null) {
            return null;
        }

        if ($object->expiresAt->getTimestamp() <= time()) {
            unset($this->objects[$requestUri]);

            return null;
        }

        return $object;
    }
}
