<?php

declare(strict_types=1);
namespace Avax\Tests\Container\Integration;

use Avax\Container\Features\Core\Attribute\Inject;
use Avax\Container\Features\Core\ContainerBuilder;
use PHPUnit\Framework\TestCase;

final class ContainerIntegrationTest extends TestCase
{
    private string $tempDir;

    /**
     * @throws \Throwable
     */
    public function testBuildInjectInvoke() : void
    {
        $container = ContainerBuilder::create()
            ->cacheDir(dir: $this->tempDir)
            ->build();

        $target = new IntegrationTarget();
        $container->injectInto(target: $target);

        $this->assertInstanceOf(expected: IntegrationFoo::class, actual: $target->foo);
        $this->assertInstanceOf(expected: IntegrationBar::class, actual: $target->bar);

        $result = $container->call(callable: [$target, 'combine'], parameters: ['suffix' => '-ok']);

        $this->assertSame(expected: 'foo-bar-foo-ok', actual: $result);
    }

    protected function setUp() : void
    {
        $this->tempDir = sys_get_temp_dir() . '/avax_integration_' . uniqid();
        @mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown() : void
    {
        $this->removeDirectory(dir: $this->tempDir);
    }

    private function removeDirectory(string $dir) : void
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

final class IntegrationFoo
{
    public function name() : string
    {
        return 'foo';
    }
}

final class IntegrationBar
{
    public function name() : string
    {
        return 'bar';
    }
}

final class IntegrationTarget
{
    #[Inject]
    public IntegrationFoo $foo;

    public IntegrationBar $bar;

    #[Inject]
    public function setBar(IntegrationBar $bar) : void
    {
        $this->bar = $bar;
    }

    public function combine(IntegrationFoo $foo, string $suffix) : string
    {
        return $this->foo->name() . '-' . $this->bar->name() . '-' . $foo->name() . $suffix;
    }
}