<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capabilities\IdentitySync;

use Avax\Auth\System\Capabilities\IdentitySync\IdentitySyncCapabilityUnavailable;
use Avax\Auth\System\Capabilities\IdentitySync\Provisioning\Provisioning;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\SCIM;
use Avax\Tests\TestCase;
use PHPUnit\Framework\TestCase;

final class OptionalCapabilityModelingTest extends TestCase
{
    public function testScimReportsUnavailableSurfaceExplicitly() : void
    {
        $scim = new SCIM(
            registerScimDirectory     : null,
            readScimDirectories       : null,
            rotateScimToken           : null,
            markScimDirectoryOutage   : null,
            recoverScimDirectoryOutage: null,
            provisionScimUser         : null,
            deleteScimUser            : null,
            readScimUsers             : null,
            readScimGroups            : null,
            syncScimGroups            : null,
            runScimBulk               : null
        );

        $this->assertFalse(condition: $scim->isConfigured());

        $this->expectException(IdentitySyncCapabilityUnavailable::class);
        $this->expectExceptionMessage('SCIM operation [read_directories] is not configured.');

        $scim->readDirectories();
    }

    public function testProvisioningReportsUnavailableSurfaceExplicitly() : void
    {
        $provisioning = new Provisioning(
            suspendUser    : null,
            reactivateUser : null,
            deprovisionUser: null
        );

        $this->assertFalse(condition: $provisioning->isConfigured());

        $this->expectException(IdentitySyncCapabilityUnavailable::class);
        $this->expectExceptionMessage('Provisioning operation [suspend_user] is not configured.');

        $provisioning->suspendUser(userId: 42);
    }
}
