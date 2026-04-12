<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Risk;

use Avax\Auth\System\Capability\Risk\DeterministicRiskEngine;
use Avax\Auth\System\Capability\Risk\InMemoryKnownAuthenticationEnvironmentStore;
use Avax\Auth\System\Capability\Risk\InMemoryRiskSignalStore;
use Avax\Auth\System\Capability\Risk\RiskAction;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\User\UserRole;
use Avax\Auth\System\Foundation\Clock;
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
            id          : new UserId(1),
            email       : new UserEmail('admin@example.com'),
            username    : 'admin',
            passwordHash: 'hash',
            roles       : [UserRole::ADMIN]
        );

        $decision = $engine->assessSuccessfulAuthentication($user, '127.0.0.1', 'PHPUnit');

        $this->assertSame(RiskAction::OPEN_REVIEW, $decision->action);
        $this->assertSame(['admin_new_environment'], $decision->reasons);
        $this->assertCount(1, $engine->readSignalsForUser(1));
    }

    public function testRefreshReuseRevokesSessions() : void
    {
        $engine = new DeterministicRiskEngine(
            knownEnvironments: new InMemoryKnownAuthenticationEnvironmentStore(),
            signals          : new InMemoryRiskSignalStore(),
            clock            : new Clock()
        );

        $decision = $engine->recordRefreshReuse(42, 'client-1');

        $this->assertSame(RiskAction::REVOKE_SESSIONS, $decision->action);
        $this->assertCount(1, $engine->readSignalsForUser(42));
    }
}
