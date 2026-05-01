<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Security;

use Avax\Components\Identity\Security\System\Capabilities\Configuration\SecurityConfigurationStore;
use Avax\Components\Identity\Security\System\Flows\ManageSecurityChange\ApplySecurityChange;
use Avax\Components\Identity\Security\System\Flows\ManageSecurityChange\ApproveSecurityChange;
use Avax\Components\Identity\Security\System\Flows\ManageSecurityChange\BeginSecurityChange;
use Avax\Components\Identity\Security\System\PublicSurface\Security;
use Avax\Tests\TestCase;

final class SecurityChangeWorkflowTest extends TestCase
{
    public function test_security_change_is_approved_before_it_is_applied() : void
    {
        $securityConfigurationStore = new SecurityConfigurationStore();
        $security                   = new Security(
            securityConfigurationStore: $securityConfigurationStore,
            beginSecurityChange       : new BeginSecurityChange(securityConfigurationStore: $securityConfigurationStore),
            approveSecurityChange     : new ApproveSecurityChange(securityConfigurationStore: $securityConfigurationStore),
            applySecurityChange       : new ApplySecurityChange(securityConfigurationStore: $securityConfigurationStore),
        );

        $change = $security->beginChange(tenantId: 'tenant-a', data: [
            'mfa_required'    => false,
            'password_policy' => 'standard',
        ]);

        self::assertSame(expected: 'pending', actual: $change->status);

        $security->approveChange(requestId: $change->request_id);
        $security->applyChange(requestId: $change->request_id);

        $configuration = $security->readConfiguration(tenantId: 'tenant-a');

        self::assertFalse(condition: $configuration->mfa_required);
        self::assertSame(expected: 'standard', actual: $configuration->password_policy);
    }
}
