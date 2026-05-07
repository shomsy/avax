<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Access;

use Avax\Components\Identity\Access\System\System\Capabilities\Role;
use PHPUnit\Framework\TestCase;

final class AccessCapabilitiesTest extends TestCase
{
    public function test_role_identification() : void
    {
        $role = new Role('admin');
        $this->assertSame('admin', $role->name);
    }
}
