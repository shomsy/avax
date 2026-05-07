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

        $this->assertSame('AvaX', $this->config->get(key: 'app.name'));
    }

    public function test_get_returns_default_when_missing() : void
    {
        $this->assertSame('default', $this->config->get(key: 'missing', default: 'default'));
    }

    public function test_get_throws_exception_when_missing_and_no_default() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Configuration key [missing] does not exist.');

        $this->config->get(key: 'missing');
    }

    public function test_has_returns_correct_boolean() : void
    {
        $this->repository->set(key: 'app.name', value: 'AvaX');

        $this->assertTrue($this->config->has(key: 'app.name'));
        $this->assertFalse($this->config->has(key: 'missing'));
    }

    public function test_all_returns_entire_repository() : void
    {
        $this->repository->set(key: 'a', value: 1);
        $this->repository->set(key: 'b', value: 2);

        $this->assertSame(['a' => 1, 'b' => 2], $this->config->all());
    }

    public function test_set_updates_repository() : void
    {
        $this->config->set(key: 'runtime.key', value: 'dynamic');

        $this->assertSame('dynamic', $this->repository->get(key: 'runtime.key'));
    }

    protected function setUp() : void
    {
        $this->repository = new ConfigurationRepository();
        $this->config     = new Config(configurationRepository: $this->repository);
    }
}
