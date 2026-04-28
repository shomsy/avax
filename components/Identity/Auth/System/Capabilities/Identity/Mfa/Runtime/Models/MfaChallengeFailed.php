<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Models;

use RuntimeException;

/**
 * Safe public failure contract for MFA challenge verification.
 */
final class MfaChallengeFailed extends RuntimeException
{
    public function __construct(
        private readonly MfaChallengeFailure $reason,
        private readonly int|null            $retryAfter = null,
        string                               $message = 'MFA verification failed.',
        int                                  $code = 401
    )
    {
        parent::__construct(message: $message, code: $code);
    }

    public static function invalidCode() : self
    {
        return new self(
            reason : MfaChallengeFailure::INVALID,
            message: 'MFA code is invalid.'
        );
    }

    public static function expired() : self
    {
        return new self(
            reason : MfaChallengeFailure::EXPIRED,
            message: 'MFA challenge has expired.'
        );
    }

    public static function locked(int $retryAfter) : self
    {
        return new self(
            reason    : MfaChallengeFailure::LOCKED,
            retryAfter: $retryAfter,
            message   : 'MFA verification is temporarily locked.',
            code      : 429
        );
    }

    public static function notFound() : self
    {
        return new self(
            reason : MfaChallengeFailure::NOT_FOUND,
            message: 'MFA challenge is missing.'
        );
    }

    public static function notEnabled() : self
    {
        return new self(
            reason : MfaChallengeFailure::NOT_ENABLED,
            message: 'MFA is not enabled for this user.',
            code   : 409
        );
    }

    public function reason() : MfaChallengeFailure
    {
        return $this->reason;
    }

    public function retryAfter() : int|null
    {
        return $this->retryAfter;
    }
}
