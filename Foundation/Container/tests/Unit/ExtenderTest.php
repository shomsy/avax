<?php

declare(strict_types=1);

namespace Avax\Tests\Container\Unit;

use Avax\Container\Features\Core\ContainerBuilder;
use PHPUnit\Framework\TestCase;

class ExtenderTest extends TestCase
{
    private string $tempDir;

    public function testExtendDecoratesService(): void
    {
        $builder = ContainerBuilder::create()->cacheDir(dir: $this->tempDir);
        $builder->bind(abstract: OriginalService::class)->to(concrete: OriginalService::class);

        $builder->extend(abstract: OriginalService::class, closure: static function ($service, $c): DecoratedService {
            return new DecoratedService(inner: $service);
        });

        $container = $builder->build();
        $instance = $container->get(id: OriginalService::class);

        $this->assertInstanceOf(expected: DecoratedService::class, actual: $instance);
        $this->assertInstanceOf(expected: OriginalService::class, actual: $instance->inner);
    }

    public function testExtendDecoratesSingletonOnlyOnce(): void
    {
        $builder = ContainerBuilder::create()->cacheDir(dir: $this->tempDir);
        $builder->singleton(abstract: OriginalService::class)->to(concrete: OriginalService::class);

        $builder->extend(abstract: OriginalService::class, closure: static function ($service, $c): DecoratedService {
            return new DecoratedService(inner: $service);
        });

        $container = $builder->build();
        $instance1 = $container->get(id: OriginalService::class);
        $instance2 = $container->get(id: OriginalService::class);

        $this->assertSame(expected: $instance1, actual: $instance2);
        $this->assertInstanceOf(expected: DecoratedService::class, actual: $instance1);
        $this->assertInstanceOf(expected: OriginalService::class, actual: $instance1->inner);
    }

    public function testMultipleExtendersStack(): void
    {
        $builder = ContainerBuilder::create()->cacheDir(dir: $this->tempDir);
        $builder->bind(abstract: OriginalService::class)->to(concrete: OriginalService::class);

        $builder->extend(abstract: OriginalService::class, closure: static function ($service, $c): DecoratedService {
            return new DecoratedService(inner: $service);
        });

        $builder->extend(abstract: OriginalService::class, closure: static function ($service, $c): AnotherDecorator {
            return new AnotherDecorator(payload: $service);
        });

        $container = $builder->build();
        /** @var AnotherDecorator $instance */
        $instance = $container->get(id: OriginalService::class);

        $this->assertInstanceOf(expected: AnotherDecorator::class, actual: $instance);
        $this->assertInstanceOf(expected: DecoratedService::class, actual: $instance->payload);
        $this->assertInstanceOf(expected: OriginalService::class, actual: $instance->payload->inner);
    }

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/avax_ext_test_' . uniqid();
        @mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory(dir: $this->tempDir);
    }

    private function removeDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->removeDirectory(dir: $path) : unlink($path);
        }

        rmdir($dir);
    }
}

class OriginalService {}

class DecoratedService
{
    public function __construct(public OriginalService $inner) {}
}

class AnotherDecorator
{
    public function __construct(public mixed $payload) {}
}
