<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\DeviceTrust;

/**
 * Device binding state.
 */
enum DeviceBindingState: string
{
    case VERIFIED = 'verified';
    case PENDING = 'pending';
    case REVOKED = 'revoked';
    case EXPIRED = 'expired';
}

/**
 * Manages device trust and binding.
 */
final readonly class DeviceBinding
{
    /**
     * Creates a new device binding.
     */
    public function create(
        string $deviceId,
        string $userId,
        string $deviceFingerprint,
        int $expiresIn = 86400 * 365
    ) : DeviceRegistration {
        $now = time();

        return new DeviceRegistration(
            deviceId           : $deviceId,
            userId            : $userId,
            fingerprint      : $deviceFingerprint,
            state             : DeviceBindingState::VERIFIED,
            issuedAt          : $now,
            expiresAt        : $now + $expiresIn,
            lastVerifiedAt   : $now,
            challenger      : null
        );
    }

    /**
     * Issues a verification challenge.
     */
    public function challenge(DeviceRegistration $registration) : DeviceRegistration
    {
        $challenge = bin2hex(random_bytes(32));

        return new DeviceRegistration(
            deviceId        : $registration->deviceId,
            userId        : $registration->userId,
            fingerprint : $registration->fingerprint,
            state        : DeviceBindingState::PENDING,
            issuedAt     : $registration->issuedAt,
            expiresAt    : $registration->expiresAt,
            lastVerifiedAt: $registration->lastVerifiedAt,
            challenger   : $challenge
        );
    }

    /**
     * Verifies a device challenge response.
     */
    public function verify(
        DeviceRegistration $registration,
        string $challenge,
        string $signedResponse
    ) : DeviceRegistration {
        if ($registration->challenger !== $challenge) {
            return $registration;
        }

        $now = time();

        return new DeviceRegistration(
            deviceId        : $registration->deviceId,
            userId        : $registration->userId,
            fingerprint : $registration->fingerprint,
            state        : DeviceBindingState::VERIFIED,
            issuedAt     : $registration->issuedAt,
            expiresAt    : $now + (86400 * 365),
            lastVerifiedAt: $now,
            challenger   : null
        );
    }

    /**
     * Revokes a device registration.
     */
    public function revoke(DeviceRegistration $registration) : DeviceRegistration
    {
        return new DeviceRegistration(
            deviceId        : $registration->deviceId,
            userId        : $registration->userId,
            fingerprint : $registration->fingerprint,
            state        : DeviceBindingState::REVOKED,
            issuedAt     : $registration->issuedAt,
            expiresAt    : time(),
            lastVerifiedAt: $registration->lastVerifiedAt,
            challenger   : null
        );
    }
}