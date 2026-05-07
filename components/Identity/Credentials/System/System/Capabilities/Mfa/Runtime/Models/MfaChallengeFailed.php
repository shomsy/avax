<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\System\Capabilities\Mfa\Runtime\Models;

use RuntimeException;

/**
 * Safe public failure contract for MFA challenge verification.
 */
final class MfaChallengeFailed extends RuntimeException
{
    public function __construct(
        private readonly MfaChallengeFailure $mfaChallengeFailure,
        private readonly ?int                $retryAfter = null,
        string                               $message = 'MFA verification failed.',
        int                                  $code = 401,
    )
    {
        parent::__construct(message: $message, code: $code);
    }

    public static function invalidCode() : self
    {
        return new self(
            message: 'MFA code is invalid.',
            reason : MfaChallengeFailure::INVALID,
        );
    }

    public static function expired() : self
    {
        return new self(
            message: 'MFA challenge has expired.',
            reason : MfaChallengeFailure::EXPIRED,
        );
    }

    public static function locked(int $retryAfter) : self
    {
        return new self(
            retryAfter: $retryAfter,
            message   : 'MFA verification is temporarily locked.',
            code      : 429,
            reason    : MfaChallengeFailure::LOCKED,
        );
    }

    public static function notFound() : self
    {
        return new self(
            message: 'MFA challenge is missing.',
            reason : MfaChallengeFailure::NOT_FOUND,
        );
    }

    public static function notEnabled() : self
    {
        return new self(
            message: 'MFA is not enabled for this user.',
            code   : 409,
            reason : MfaChallengeFailure::NOT_ENABLED,
        );
    }

    public function reason() : MfaChallengeFailure
    {
        return $this->mfaChallengeFailure;
    }

    public function retryAfter() : ?int
    {
        return $this->retryAfter;
    }
}
