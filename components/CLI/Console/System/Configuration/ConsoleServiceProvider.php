<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\CLI\Console\System\Capabilities\Output\ConsoleOutput;
use Avax\Components\CLI\Console\System\PublicSurface\Console;

/**
 * ConsoleServiceProvider — registers CLI/Console component dependencies.
 *
 * Registers the console output handler and the Console application facade.
 */
final class ConsoleServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // ConsoleOutput — terminal output handler with no external dependencies
        $container->singleton(ConsoleOutput::class, static fn () : ConsoleOutput => new ConsoleOutput());

        // Console — needs ConsoleOutput
        $container->singleton(Console::class, static fn (ContainerInterface $c) : Console => new Console(
            consoleOutput: $c->get(ConsoleOutput::class),
        ));
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot-time logic needed
    }
}
