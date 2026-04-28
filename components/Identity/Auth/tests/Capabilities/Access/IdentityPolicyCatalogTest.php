<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Capabilities\Access;

use Avax\Components\Identity\Auth\System\Capabilities\Access\Policy\AuthenticationFactor;
use Avax\Components\Identity\Auth\System\Capabilities\Access\Policy\IdentityActor;
use Avax\Components\Identity\Auth\System\Capabilities\Access\Policy\IdentityPolicyCatalog;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

final class IdentityPolicyCatalogTest extends TestCase
{
    public function testAdminPolicyRequiresPhishingResistantFactor() : void
    {
        $policy = IdentityPolicyCatalog::admin();

        $this->assertSame(expected: IdentityActor::ADMIN, actual: $policy->actor);
        $this->assertTrue(condition: $policy->phishingResistantRequired);
        $this->assertTrue(condition: $policy->requiresFactor(factor: AuthenticationFactor::PASSKEY));
        $this->assertTrue(condition: $policy->privilegedApprovalRequired);
    }

    public function testMachineIdentityPolicyRequiresSenderConstrainedTokens() : void
    {
        $policy = IdentityPolicyCatalog::machineIdentity();

        $this->assertSame(expected: IdentityActor::MACHINE_IDENTITY, actual: $policy->actor);
        $this->assertTrue(condition: $policy->senderConstrainedTokensRequired);
        $this->assertTrue(condition: $policy->requiresFactor(factor: AuthenticationFactor::MTLS));
        $this->assertFalse(condition: $policy->allowsFactor(factor: AuthenticationFactor::PASSWORD));
    }
}
