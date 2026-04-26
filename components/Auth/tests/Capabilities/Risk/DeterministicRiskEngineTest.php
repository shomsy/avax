<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capabilities\Risk;

use Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support\DeterministicRiskEngine;
use Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support\InMemoryKnownAuthenticationEnvironmentStore;
use Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support\InMemoryRiskSignalStore;
use Avax\Auth\System\Capabilities\Access\RiskBasedAccess\Support\RiskAction;
use Avax\Auth\System\Capabilities\Identity\User\User;
use Avax\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Auth\System\Capabilities\Identity\User\UserRole;
use Avax\Auth\System\Foundation\Clock;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

final class DeterministicRiskEngineTest extends TestCase
{
    public function testAdminNewEnvironmentOpensReview() : void
    {
        $engine = new DeterministicRiskEngine(
            knownEnvironments: new InMemoryKnownAuthenticationEnvironmentStore(),
            signals          : new InMemoryRiskSignalStore(),
            clock            : new Clock()
        );
        $user   = User::create(
            id          : new UserId(value: 1),
            email       : new UserEmail(value: 'admin@example.com'),
            username    : 'admin',
            passwordHash: 'hash',
            roles       : [UserRole::ADMIN]
        );

        $decision = $engine->assessSuccessfulAuthentication(user: $user, ipAddress: '127.0.0.1', userAgent: 'PHPUnit');

        $this->assertSame(expected: RiskAction::OPEN_REVIEW, actual: $decision->action);
        $this->assertSame(expected: ['admin_new_environment'], actual: $decision->reasons);
        $this->assertCount(expectedCount: 1, haystack: $engine->readSignalsForUser(userId: 1));
    }

    public function testRefreshReuseRevokesSessions() : void
    {
        $engine = new DeterministicRiskEngine(
            knownEnvironments: new InMemoryKnownAuthenticationEnvironmentStore(),
            signals          : new InMemoryRiskSignalStore(),
            clock            : new Clock()
        );

        $decision = $engine->recordRefreshReuse(userId: 42, clientId: 'client-1');

        $this->assertSame(expected: RiskAction::REVOKE_SESSIONS, actual: $decision->action);
        $this->assertCount(expectedCount: 1, haystack: $engine->readSignalsForUser(userId: 42));
    }
}
