<?php

declare(strict_types=1);

namespace Avax\Container\Tests\Configuration;

use Avax\Container\DependencyInjection\Configuration\ContainerConfig;
use PHPUnit\Framework\TestCase;

final class ContainerConfigTest extends TestCase
{
    public function test_empty_cache_dir_falls_back_to_system_temp_dir() : void
    {
        $config = new ContainerConfig;

        $this->assertSame(expected: sys_get_temp_dir(), actual: $config->cacheDirectory());
    }

    public function test_with_settings_returns_new_config() : void
    {
        $config = (new ContainerConfig(cacheDir: '/tmp/cache', debug: true))->withSettings(settings: [
                                                                                                         'app.name' => 'container',
                                                                                                     ]);

        $this->assertSame(expected: '/tmp/cache', actual: $config->cacheDir);
        $this->assertTrue(condition: $config->debug);
        $this->assertSame(expected: ['app.name' => 'container'], actual: $config->settings);
    }
}
