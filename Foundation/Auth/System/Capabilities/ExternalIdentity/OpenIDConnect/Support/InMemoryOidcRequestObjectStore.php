<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Oidc;

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
        string|null       $signingAlgorithm = null,
        string|null       $signingClientId = null
    ) : OidcRequestObject
    {
        $object = new OidcRequestObject(
            requestUri       : $requestUri,
            claims           : $claims,
            createdAt        : new DateTimeImmutable(),
            expiresAt        : $expiresAt,
            signatureVerified: $signatureVerified,
            signingAlgorithm : $signingAlgorithm,
            signingClientId  : $signingClientId
        );

        $this->objects[$requestUri] = $object;

        return $object;
    }

    public function consume(string $requestUri) : OidcRequestObject|null
    {
        $object = $this->find(requestUri: $requestUri);

        if ($object === null) {
            return null;
        }

        unset($this->objects[$requestUri]);

        return $object;
    }

    public function find(string $requestUri) : OidcRequestObject|null
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
