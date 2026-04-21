<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integrations\Http;

use Avax\Auth\Integrations\Http\MapAuthFailure;
use Avax\Auth\System\Capabilities\Access\RequirePermission\PermissionDenied;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Challenge\MfaAttemptLimitReached;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\FreshMfaRequired;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\MfaAttemptLimitReached;
use Avax\Auth\System\Capabilities\Identity\User\UserPermission;
use Avax\Auth\System\Flows\Login\AuthenticationFailed;
use Avax\Auth\System\Flows\Login\RateLimit\RateLimitException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class MapAuthFailureTest extends TestCase
{
    public function testMapAuthFailureReturnsSafeHttpOutcomeForAccessDenial() : void
    {
        $failure = (new MapAuthFailure())->execute(failure: new PermissionDenied(requirement: new UserPermission(value: 'write')));

        $this->assertSame(expected: 403, actual: $failure->statusCode);
        $this->assertSame(expected: 'access_denied', actual: $failure->errorCode);
        $this->assertSame(expected: 'Access denied.', actual: $failure->message);
    }

    public function testMapAuthFailureRedactsAuthenticationFailureDetails() : void
    {
        $failure = (new MapAuthFailure())->execute(failure: AuthenticationFailed::invalidCredentials());

        $this->assertSame(expected: 401, actual: $failure->statusCode);
        $this->assertSame(expected: 'authentication_failed', actual: $failure->errorCode);
        $this->assertSame(expected: 'Authentication failed.', actual: $failure->message);
    }

    public function testMapAuthFailureCarriesRetryAfterForRateLimits() : void
    {
        $loginFailure = (new MapAuthFailure())->execute(failure: new RateLimitException(message: 'Slow down.', retryAfter: 30));
        $mfaFailure   = (new MapAuthFailure())->execute(failure: new MfaAttemptLimitReached(retryAfter: 45));

        $this->assertSame(expected: 429, actual: $loginFailure->statusCode);
        $this->assertSame(expected: 30, actual: $loginFailure->retryAfterSeconds);
        $this->assertSame(expected: 429, actual: $mfaFailure->statusCode);
        $this->assertSame(expected: 45, actual: $mfaFailure->retryAfterSeconds);
    }

    public function testMapAuthFailureMarksFreshMfaBoundary() : void
    {
        $failure = (new MapAuthFailure())->execute(failure: new FreshMfaRequired(maxAgeSeconds: 300));

        $this->assertSame(expected: 403, actual: $failure->statusCode);
        $this->assertSame(expected: 'fresh_mfa_required', actual: $failure->errorCode);
        $this->assertSame(expected: 'Fresh MFA verification required.', actual: $failure->message);
    }

    public function testMapAuthFailureFallsBackToInternalErrorForUnknownFailures() : void
    {
        $failure = (new MapAuthFailure())->execute(failure: new RuntimeException(message: 'boom'));

        $this->assertSame(expected: 500, actual: $failure->statusCode);
        $this->assertSame(expected: 'auth_error', actual: $failure->errorCode);
        $this->assertSame(expected: 'Authentication flow failed.', actual: $failure->message);
    }
}
