<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Oidc\PushAuthorizationRequest;

/**
 * Represents a pushed authorization request (PAR).
 */
final readonly class PushedAuthRequest
{
    /**
     * @param array<string, mixed> $requestParams
     */
    public function __construct(
        public string $requestUri,
        public int    $expiresAt,
        public string $clientId,
        public array $requestParams,
        public int    $createdAt
    ) {}

    public function isExpired(int $currentTime) : bool
    {
        return $this->expiresAt < $currentTime;
    }

    public function isValid() : bool
    {
        return $this->requestUri !== '' && $this->clientId !== '';
    }
}