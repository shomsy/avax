<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\DeviceTrust;

/**
 * Device token for device-bound authentication.
 */
final readonly class DeviceToken
{
    public function __construct(
        public string $deviceId,
        public string $userId,
        public string $token,
        public int $issuedAt,
        public int $expiresAt,
        public string $challenge
    ) {}

    public function isExpired(int $currentTime) : bool
    {
        return $this->expiresAt < $currentTime;
    }

    public function matchesChallenge(string $challenge) : bool
    {
        return hash_equals($this->challenge, $challenge);
    }
}