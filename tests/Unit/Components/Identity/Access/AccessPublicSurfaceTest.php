<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Access;

use Avax\Components\Identity\Access\System\Capabilities\Authorization\AuthorizationEngine;
use Avax\Components\Identity\Access\System\Flows\AdminElevation\BeginAdminElevation;
use Avax\Components\Identity\Access\System\Flows\AdminElevation\EndAdminElevation;
use Avax\Components\Identity\Access\System\PublicSurface\Access;
use Avax\Tests\TestCase;
use Override;

final class AccessPublicSurfaceTest extends TestCase
{
    public function test_admin_elevation_temporarily_allows_permissions() : void
    {
        $access = new Access(
            authorizationEngine: new AuthorizationEngine(permissions: ['content.read']),
            beginAdminElevation: new BeginAdminElevation(),
            endAdminElevation  : new EndAdminElevation(),
        );

        self::assertTrue($access->allows(permission: 'content.read'));
        self::assertFalse($access->allows(permission: 'content.delete'));

        $access->beginElevation();

        self::assertTrue($access->isElevated());
        self::assertTrue($access->allows(permission: 'content.delete'));

        $access->endElevation();

        self::assertFalse($access->isElevated());
        self::assertFalse($access->allows(permission: 'content.delete'));
    }

    #[Override]
    protected function tearDown() : void
    {
        BeginAdminElevation::reset();

        parent::tearDown();
    }
}
