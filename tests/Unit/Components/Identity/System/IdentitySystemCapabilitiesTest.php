<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\System;

use Avax\Components\Identity\System\PublicSurface\Identity;
use PHPUnit\Framework\TestCase;

final class IdentitySystemCapabilitiesTest extends TestCase
{
    public function test_identity_surface_provides_access_to_subsystems() : void
    {
        $identity = new Identity();
        $this->assertInstanceOf(Identity::class, $identity);
    }
}
