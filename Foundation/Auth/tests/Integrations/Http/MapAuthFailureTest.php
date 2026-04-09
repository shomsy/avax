<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integrations\Http;

use Avax\Auth\Integrations\Http\MapAuthFailure;
use Avax\Auth\System\Capability\Access\RequirePermission\PermissionDenied;
use Avax\Auth\System\Capability\User\UserPermission;
use Avax\Auth\System\Flow\Login\AuthenticationFailed;
use Avax\Auth\System\Flow\Login\RateLimit\RateLimitException;
use Avax\Auth\System\Flow\Mfa\Challenge\MfaAttemptLimitReached;
use Avax\Auth\System\Flow\Mfa\FreshMfaRequired;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class MapAuthFailureTest extends TestCase
{
    public function testMapAuthFailureReturnsSafeHttpOutcomeForAccessDenial() : void
    {
        $failure = (new MapAuthFailure())->execute(new PermissionDenied(new UserPermission('write')));

        $this->assertSame(403, $failure->statusCode);
        $this->assertSame('access_denied', $failure->errorCode);
        $this->assertSame('Access denied.', $failure->message);
    }

    public function testMapAuthFailureRedactsAuthenticationFailureDetails() : void
    {
        $failure = (new MapAuthFailure())->execute(AuthenticationFailed::invalidCredentials());

        $this->assertSame(401, $failure->statusCode);
        $this->assertSame('authentication_failed', $failure->errorCode);
        $this->assertSame('Authentication failed.', $failure->message);
    }

    public function testMapAuthFailureCarriesRetryAfterForRateLimits() : void
    {
        $loginFailure = (new MapAuthFailure())->execute(new RateLimitException('Slow down.', 30));
        $mfaFailure   = (new MapAuthFailure())->execute(new MfaAttemptLimitReached(45));

        $this->assertSame(429, $loginFailure->statusCode);
        $this->assertSame(30, $loginFailure->retryAfterSeconds);
        $this->assertSame(429, $mfaFailure->statusCode);
        $this->assertSame(45, $mfaFailure->retryAfterSeconds);
    }

    public function testMapAuthFailureMarksFreshMfaBoundary() : void
    {
        $failure = (new MapAuthFailure())->execute(new FreshMfaRequired(300));

        $this->assertSame(403, $failure->statusCode);
        $this->assertSame('fresh_mfa_required', $failure->errorCode);
        $this->assertSame('Fresh MFA verification required.', $failure->message);
    }

    public function testMapAuthFailureFallsBackToInternalErrorForUnknownFailures() : void
    {
        $failure = (new MapAuthFailure())->execute(new RuntimeException('boom'));

        $this->assertSame(500, $failure->statusCode);
        $this->assertSame('auth_error', $failure->errorCode);
        $this->assertSame('Authentication flow failed.', $failure->message);
    }
}
