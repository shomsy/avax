<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Access\RequirePhishingResistantAuthentication;

use Avax\Auth\System\Capability\Access\RequirePhishingResistantAuthentication\PhishingResistantAuthenticationRequired;
use Avax\Auth\System\Capability\Access\RequirePhishingResistantAuthentication\RequirePhishingResistantAuthentication;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use PHPUnit\Framework\TestCase;

final class RequirePhishingResistantAuthenticationTest extends TestCase
{
    public function testRequirePhishingResistantAuthenticationSuccess() : void
    {
        $current = new CurrentAuthentication();
        $current->store(AuthenticationContext::authenticated(
            user               : new AuthenticatedUser(
                id       : 1,
                email    : 'user@example.com',
                username : 'user'
            ),
            mode               : AuthenticationMode::SESSION,
            phishingResistant  : true
        ));

        $requirement = new RequirePhishingResistantAuthentication($current);
        $requirement->execute();

        $this->assertTrue(true);
    }

    public function testRequirePhishingResistantAuthenticationFailure() : void
    {
        $current = new CurrentAuthentication();
        $current->store(AuthenticationContext::authenticated(
            user  : new AuthenticatedUser(
                id       : 1,
                email    : 'user@example.com',
                username : 'user'
            ),
            mode  : AuthenticationMode::SESSION
        ));

        $requirement = new RequirePhishingResistantAuthentication($current);

        $this->expectException(PhishingResistantAuthenticationRequired::class);
        $requirement->execute();
    }
}
