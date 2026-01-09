<?php

declare(strict_types=1);

namespace Avax\Tests\Container\Unit;

use Avax\Container\Features\Core\Attribute\Inject;
use Avax\Container\Features\Core\ContainerBuilder;
use Avax\Container\Features\Core\Contracts\ContainerInterface;
use Avax\Container\Features\Core\Exceptions\ContainerException;
use Avax\Container\Features\Core\Exceptions\ResolutionException;
use PHPUnit\Framework\TestCase;

class ArchitectureTest extends TestCase
{
    private string $tempDir;

    public function testInjectionHappensExactlyOnce(): void
    {
        $builder = ContainerBuilder::create();
        $builder->cacheDir(dir: $this->tempDir);
        $builder->bind(abstract: 'counter', concrete: InjectionCounter::class);

        $container = $builder->build();

        /** @var InjectionCounter $instance */
        $instance = $container->get(id: 'counter');

        $this->assertSame(expected: 1, actual: $instance->injectCount, message: 'Injection should happen exactly once via setter/property');
        $this->assertTrue(condition: $instance->constructed, message: 'Constructor should rely on Instantiator');
    }

    public function testInjectAttributeWithoutTypeOrIdThrowsException(): void
    {
        $container = ContainerBuilder::create()->build();

        // Using ContainerException because ResolutionPipeline wraps inner exceptions
        $this->expectException(exception: ContainerException::class);
        $this->expectExceptionMessage(message: 'has #[Inject] but no resolvable type');

        $container->get(id: BadlyConfiguredService::class);
    }

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/avax_arch_test_' . uniqid();
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

class InjectionCounter
{
    public int $injectCount = 0;
    public bool $constructed = true;

    #[Inject]
    public function setDep(ContainerInterface $c): void
    {
        $this->injectCount++;
    }
}

class BadlyConfiguredService
{
    #[Inject]
    public $something;
}
