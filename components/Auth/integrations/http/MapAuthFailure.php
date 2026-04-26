<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Http;

use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Access\RequirePermission\PermissionDenied;
use Avax\Auth\System\Capabilities\Access\RequireRole\RoleDenied;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Limit\MfaAttemptLimitReached;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\FreshMfaRequired;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\MfaChallengeFailed;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\MfaEnrollmentFailed;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\MfaRecoveryFailed;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Flow\RefreshAuthenticationFailed;
use Avax\Auth\System\Flows\ChangePassword\PasswordChangeFailed;
use Avax\Auth\System\Flows\Login\AuthenticationFailed;
use Avax\Auth\System\Flows\Login\RateLimit\RateLimitException;
use Avax\Auth\System\Flows\Register\RegistrationFailed;
use Throwable;

/**
 * Maps kernel-safe failures to HTTP-safe transport outcomes.
 */
final class MapAuthFailure
{
    public function execute(Throwable $failure) : HttpAuthFailure
    {
        return match (true) {
            $failure instanceof RateLimitException                               => new HttpAuthFailure(
                statusCode       : 429,
                errorCode        : 'rate_limited',
                message          : 'Too many attempts.',
                retryAfterSeconds: $failure->getRetryAfter()
            ),
            $failure instanceof MfaAttemptLimitReached                           => new HttpAuthFailure(
                statusCode       : 429,
                errorCode        : 'mfa_rate_limited',
                message          : 'Too many attempts.',
                retryAfterSeconds: $failure->retryAfter()
            ),
            $failure instanceof Unauthenticated                                  => new HttpAuthFailure(
                statusCode: 401,
                errorCode : 'authentication_required',
                message   : 'Authentication required.'
            ),
            $failure instanceof AuthenticationFailed                             => new HttpAuthFailure(
                statusCode: 401,
                errorCode : 'authentication_failed',
                message   : 'Authentication failed.'
            ),
            $failure instanceof RefreshAuthenticationFailed                      => new HttpAuthFailure(
                statusCode: 401,
                errorCode : 'refresh_rejected',
                message   : 'Authentication failed.'
            ),
            $failure instanceof FreshMfaRequired                                 => new HttpAuthFailure(
                statusCode: 403,
                errorCode : 'fresh_mfa_required',
                message   : 'Fresh MFA verification required.'
            ),
            $failure instanceof RoleDenied, $failure instanceof PermissionDenied => new HttpAuthFailure(
                statusCode: 403,
                errorCode : 'access_denied',
                message   : 'Access denied.'
            ),
            $failure instanceof PasswordChangeFailed                             => new HttpAuthFailure(
                statusCode: 403,
                errorCode : 'password_change_failed',
                message   : 'Password change failed.'
            ),
            $failure instanceof RegistrationFailed                               => new HttpAuthFailure(
                statusCode: 409,
                errorCode : 'registration_failed',
                message   : 'Registration failed.'
            ),
            $failure instanceof MfaChallengeFailed                               => new HttpAuthFailure(
                statusCode       : $failure->getCode() !== 0 ? $failure->getCode() : 401,
                errorCode        : 'mfa_challenge_failed',
                message          : 'MFA verification failed.',
                retryAfterSeconds: $failure->retryAfter()
            ),
            $failure instanceof MfaEnrollmentFailed                              => new HttpAuthFailure(
                statusCode: $failure->getCode() !== 0 ? $failure->getCode() : 422,
                errorCode : 'mfa_enrollment_failed',
                message   : 'MFA enrollment failed.'
            ),
            $failure instanceof MfaRecoveryFailed                                => new HttpAuthFailure(
                statusCode: $failure->getCode() !== 0 ? $failure->getCode() : 401,
                errorCode : 'mfa_recovery_failed',
                message   : 'MFA recovery failed.'
            ),
            default                                                              => new HttpAuthFailure(
                statusCode: 500,
                errorCode : 'auth_error',
                message   : 'Authentication flow failed.'
            ),
        };
    }
}
