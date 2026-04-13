<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Mfa\StepUp;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Mfa\FreshMfaRequired;
use Avax\Auth\System\Flow\Mfa\StepUp\RequireFreshMfa;
use Avax\Auth\Tests\Support\FrozenClock;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for fresh-MFA guard.
 */
final class RequireFreshMfaTest extends TestCase
{
    /**
     * @throws Unauthenticated
     */
    public function testFreshMfaPassesForRecentVerification() : void
    {
        $clock                 = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-09T12:05:00+00:00'));
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: AuthenticationContext::authenticated(
            user         : new AuthenticatedUser(
                               id        : 1,
                               email     : 'user@example.com',
                               username  : 'user',
                               mfaEnabled: true
                           ),
            mode         : AuthenticationMode::TOKEN,
            mfaVerifiedAt: new DateTimeImmutable(datetime: '2026-04-09T12:02:00+00:00')
        ));

        $guard = new RequireFreshMfa(
            currentAuthentication: $currentAuthentication,
            clock                : $clock,
            maxAgeSeconds        : 300
        );

        $guard->execute();
        $this->assertTrue(condition: true);
    }

    /**
     * @throws Unauthenticated
     */
    public function testFreshMfaFailsForStaleVerification() : void
    {
        $clock                 = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-09T12:10:01+00:00'));
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: AuthenticationContext::authenticated(
            user         : new AuthenticatedUser(
                               id        : 1,
                               email     : 'user@example.com',
                               username  : 'user',
                               mfaEnabled: true
                           ),
            mode         : AuthenticationMode::TOKEN,
            mfaVerifiedAt: new DateTimeImmutable(datetime: '2026-04-09T12:05:00+00:00')
        ));

        $guard = new RequireFreshMfa(
            currentAuthentication: $currentAuthentication,
            clock                : $clock,
            maxAgeSeconds        : 300
        );

        $this->expectException(FreshMfaRequired::class);
        $guard->execute();
    }

    /**
     * @throws Unauthenticated
     */
    public function testFreshMfaIsNotRequiredWhenUserDoesNotUseMfa() : void
    {
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: AuthenticationContext::authenticated(
            user: new AuthenticatedUser(
                      id        : 1,
                      email     : 'user@example.com',
                      username  : 'user',
                      mfaEnabled: false
                  ),
            mode: AuthenticationMode::TOKEN
        ));

        $guard = new RequireFreshMfa(
            currentAuthentication: $currentAuthentication,
            clock                : new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-09T12:10:01+00:00')),
            maxAgeSeconds        : 300
        );

        $guard->execute();
        $this->assertTrue(condition: true);
    }
}
