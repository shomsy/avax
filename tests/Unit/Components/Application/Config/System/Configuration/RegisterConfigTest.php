<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Config\System\Configuration;

use Avax\Components\Application\Config\System\Configuration\Builders\RegisterConfig;
use Avax\Components\Application\Config\System\PublicSurface\Config;
use PHPUnit\Framework\TestCase;

final class RegisterConfigTest extends TestCase
{
    public function test_build_returns_config_instance() : void
    {
        $register = new RegisterConfig();
        $config   = $register->build();

        $this->assertInstanceOf(Config::class, $config);
    }
}
