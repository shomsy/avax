<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Access\RequirePhishingResistantAuthentication;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Access\RequirePhishingResistantAuthentication\PhishingResistantAuthenticationRequired;
use Avax\Auth\System\Capability\Access\RequirePhishingResistantAuthentication\RequirePhishingResistantAuthentication;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use PHPUnit\Framework\TestCase;

final class RequirePhishingResistantAuthenticationTest extends TestCase
{
    /**
     * @throws Unauthenticated
     */
    public function testRequirePhishingResistantAuthenticationSuccess() : void
    {
        $current = new CurrentAuthentication();
        $current->store(context: AuthenticationContext::authenticated(
            user               : new AuthenticatedUser(
                id       : 1,
                email    : 'user@example.com',
                username : 'user'
            ),
            mode               : AuthenticationMode::SESSION,
            phishingResistant  : true
        ));

        $requirement = new RequirePhishingResistantAuthentication(currentAuthentication: $current);
        $requirement->execute();

        $this->assertTrue(condition: true);
    }

    /**
     * @throws Unauthenticated
     */
    public function testRequirePhishingResistantAuthenticationFailure() : void
    {
        $current = new CurrentAuthentication();
        $current->store(context: AuthenticationContext::authenticated(
            user  : new AuthenticatedUser(
                id       : 1,
                email    : 'user@example.com',
                username : 'user'
            ),
            mode  : AuthenticationMode::SESSION
        ));

        $requirement = new RequirePhishingResistantAuthentication(currentAuthentication: $current);

        $this->expectException(PhishingResistantAuthenticationRequired::class);
        $requirement->execute();
    }
}
