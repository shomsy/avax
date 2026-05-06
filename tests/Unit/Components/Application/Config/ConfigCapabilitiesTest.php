<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Config;

use Avax\Components\Application\Config\System\Capabilities\Repository\ConfigurationRepository;
use PHPUnit\Framework\TestCase;

final class ConfigCapabilitiesTest extends TestCase
{
    public function test_configuration_repository(): void
    {
        $repo = new ConfigurationRepository();
        $this->assertInstanceOf(ConfigurationRepository::class, $repo);
    }

    public function test_configuration_repository_set(): void
    {
        $repo = new ConfigurationRepository();
        $repo->set('key', 'value');

        $this->assertTrue($repo->has('key'));
    }

    public function test_configuration_repository_get(): void
    {
        $repo = new ConfigurationRepository();
        $repo->set('key', 'value');

        $this->assertSame('value', $repo->get('key'));
    }

    public function test_configuration_repository_has(): void
    {
        $repo = new ConfigurationRepository();
        $repo->set('key', 'value');

        $this->assertTrue($repo->has('key'));
        $this->assertFalse($repo->has('nonexistent'));
    }

    public function test_configuration_repository_get_nested(): void
    {
        $repo = new ConfigurationRepository();
        $repo->set('database.host', 'localhost');
        $repo->set('database.port', 3306);

        $this->assertSame('localhost', $repo->get('database.host'));
    }

    public function test_configuration_repository_many(): void
    {
        $repo = new ConfigurationRepository();
        $repo->set('a', 1);
        $repo->set('b', 2);
        $repo->set('c', 3);

        $this->assertTrue($repo->has('a'));
        $this->assertTrue($repo->has('b'));
        $this->assertTrue($repo->has('c'));
    }

    public function test_configuration_repository_default_value(): void
    {
        $repo = new ConfigurationRepository();
        $repo->set('key', 'value');

        $result = $repo->get('missing', 'default');
        $this->assertSame('default', $result);
    }

    public function test_configuration_repository_all(): void
    {
        $repo = new ConfigurationRepository();
        $repo->set('a', 1);
        $repo->set('b', 2);

        $all = $repo->all();

        $this->assertArrayHasKey('a', $all);
        $this->assertArrayHasKey('b', $all);
    }

    public function test_configuration_repository_merge(): void
    {
        $repo = new ConfigurationRepository();
        $repo->set('a', 1);
        $repo->merge(['b' => 2, 'c' => 3]);

        $this->assertTrue($repo->has('b'));
        $this->assertTrue($repo->has('c'));
    }
}
