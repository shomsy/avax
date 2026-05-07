<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Config\PublicSurface;

use Avax\Components\Application\Config\System\Capabilities\Repository\ConfigurationRepository;
use Avax\Components\Application\Config\System\PublicSurface\Config;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ConfigTest extends TestCase
{
    private ConfigurationRepository $repository;

    private Config $config;

    public function test_get_returns_value_when_exists() : void
    {
        $this->repository->set(key: 'app.name', value: 'AvaX');

        $this->assertSame(expected: 'AvaX', actual: $this->config->get(key: 'app.name'));
    }

    public function test_get_returns_default_when_missing() : void
    {
        $this->assertSame(expected: 'default', actual: $this->config->get(key: 'missing', default: 'default'));
    }

    public function test_get_throws_exception_when_missing_and_no_default() : void
    {
        $this->expectException(exception: RuntimeException::class);
        $this->expectExceptionMessage(message: 'Configuration key [missing] does not exist.');

        $this->config->get(key: 'missing');
    }

    public function test_has_returns_correct_boolean() : void
    {
        $this->repository->set(key: 'app.name', value: 'AvaX');

        $this->assertTrue(condition: $this->config->has(key: 'app.name'));
        $this->assertFalse(condition: $this->config->has(key: 'missing'));
    }

    public function test_all_returns_entire_repository() : void
    {
        $this->repository->set(key: 'a', value: 1);
        $this->repository->set(key: 'b', value: 2);

        $this->assertSame(expected: ['a' => 1, 'b' => 2], actual: $this->config->all());
    }

    public function test_set_updates_repository() : void
    {
        $this->config->set(key: 'runtime.key', value: 'dynamic');

        $this->assertSame(expected: 'dynamic', actual: $this->repository->get(key: 'runtime.key'));
    }

    protected function setUp() : void
    {
        $this->repository = new ConfigurationRepository();
        $this->config     = new Config(configurationRepository: $this->repository);
    }
}
