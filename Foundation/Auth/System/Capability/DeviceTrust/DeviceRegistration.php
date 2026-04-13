<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\DeviceTrust;

/**
 * Device registration record.
 */
final readonly class DeviceRegistration
{
    public function __construct(
        public string $deviceId,
        public string $userId,
        public string $fingerprint,
        public DeviceBindingState $state,
        public int $issuedAt,
        public int $expiresAt,
        public int $lastVerifiedAt,
        public string|null $challenger
    ) {}

    public function isRevoked() : bool
    {
        return $this->state === DeviceBindingState::REVOKED;
    }

    public function isExpired(int $currentTime) : bool
    {
        return $this->expiresAt < $currentTime;
    }

    public function isVerified() : bool
    {
        return $this->state === DeviceBindingState::VERIFIED;
    }
}