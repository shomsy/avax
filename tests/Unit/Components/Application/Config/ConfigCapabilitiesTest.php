<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Config;

use Avax\Components\Application\Config\System\Capabilities\Repository\ConfigurationRepository;
use PHPUnit\Framework\TestCase;

final class ConfigCapabilitiesTest extends TestCase
{
    public function testConfigurationRepository() : void
    {
        $repo = new ConfigurationRepository();
        $this->assertInstanceOf(ConfigurationRepository::class, $repo);
    }

    public function testConfigurationRepositorySet() : void
    {
        $repo = new ConfigurationRepository();
        $repo->set('key', 'value');

        $this->assertTrue($repo->has('key'));
    }

    public function testConfigurationRepositoryGet() : void
    {
        $repo = new ConfigurationRepository();
        $repo->set('key', 'value');

        $this->assertSame('value', $repo->get('key'));
    }

    public function testConfigurationRepositoryHas() : void
    {
        $repo = new ConfigurationRepository();
        $repo->set('key', 'value');

        $this->assertTrue($repo->has('key'));
        $this->assertFalse($repo->has('nonexistent'));
    }

    public function testConfigurationRepositoryGetNested() : void
    {
        $repo = new ConfigurationRepository();
        $repo->set('database.host', 'localhost');
        $repo->set('database.port', 3306);

        $this->assertSame('localhost', $repo->get('database.host'));
    }

    public function testConfigurationRepositoryMany() : void
    {
        $repo = new ConfigurationRepository();
        $repo->set('a', 1);
        $repo->set('b', 2);
        $repo->set('c', 3);

        $this->assertTrue($repo->has('a'));
        $this->assertTrue($repo->has('b'));
        $this->assertTrue($repo->has('c'));
    }

    public function testConfigurationRepositoryDefaultValue() : void
    {
        $repo = new ConfigurationRepository();
        $repo->set('key', 'value');

        $result = $repo->get('missing', 'default');
        $this->assertSame('default', $result);
    }

    public function testConfigurationRepositoryAll() : void
    {
        $repo = new ConfigurationRepository();
        $repo->set('a', 1);
        $repo->set('b', 2);

        $all = $repo->all();

        $this->assertArrayHasKey('a', $all);
        $this->assertArrayHasKey('b', $all);
    }

    public function testConfigurationRepositoryMerge() : void
    {
        $repo = new ConfigurationRepository();
        $repo->set('a', 1);
        $repo->merge(['b' => 2, 'c' => 3]);

        $this->assertTrue($repo->has('b'));
        $this->assertTrue($repo->has('c'));
    }
}