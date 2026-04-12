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

        $this->assertSame(IdentityActor::ADMIN, $policy->actor);
        $this->assertTrue($policy->phishingResistantRequired);
        $this->assertTrue($policy->requiresFactor(AuthenticationFactor::PASSKEY));
        $this->assertTrue($policy->privilegedApprovalRequired);
    }

    public function testMachineIdentityPolicyRequiresSenderConstrainedTokens() : void
    {
        $policy = IdentityPolicyCatalog::machineIdentity();

        $this->assertSame(IdentityActor::MACHINE_IDENTITY, $policy->actor);
        $this->assertTrue($policy->senderConstrainedTokensRequired);
        $this->assertTrue($policy->requiresFactor(AuthenticationFactor::MTLS));
        $this->assertFalse($policy->allowsFactor(AuthenticationFactor::PASSWORD));
    }
}
