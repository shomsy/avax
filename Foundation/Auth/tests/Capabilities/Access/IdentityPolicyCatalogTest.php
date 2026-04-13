<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Access;

use Avax\Auth\System\Capability\Access\Policy\AuthenticationFactor;
use Avax\Auth\System\Capability\Access\Policy\IdentityActor;
use Avax\Auth\System\Capability\Access\Policy\IdentityPolicyCatalog;
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
