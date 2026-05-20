<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\CLI\Console\Configuration;

use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use Avax\Components\CLI\Console\System\Capabilities\Output\ConsoleOutput;
use Avax\Components\CLI\Console\System\Configuration\ConsoleServiceProvider;
use Avax\Components\CLI\Console\System\PublicSurface\Console;
use PHPUnit\Framework\TestCase;

final class ConsoleServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private ConsoleServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider = new ConsoleServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_console_output_resolves(): void
    {
        $output = $this->container->get(ConsoleOutput::class);

        $this->assertInstanceOf(ConsoleOutput::class, $output);
    }

    public function test_console_resolves(): void
    {
        $console = $this->container->get(Console::class);

        $this->assertInstanceOf(Console::class, $console);
    }
}
