<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Capabilities\Commands;

use Avax\Components\CLI\Console\System\Capabilities\Generators\ControllerGenerator;
use Avax\Components\CLI\Console\System\PublicSurface\Command;
use RuntimeException;

/**
 * Command to generate a new controller class.
 */
class MakeControllerCommand extends Command
{
    protected string $name        = 'make:controller';
    protected string $description = 'Create a new controller class';
    protected string $signature   = 'make:controller {name} [--methods=]';
    protected array  $arguments   = ['name'];
    protected array  $options     = ['methods'];

    public function __construct(
        private readonly ControllerGenerator $generator
    ) {}

    protected function handle() : int
    {
        $name = $this->argument(0);

        if (empty($name)) {
            $name = $this->ask('Enter controller name');

            if (empty($name)) {
                $this->error('Controller name is required.');

                return self::INVALID;
            }
        }

        $methodsInput = $this->option('methods');
        $methods      = [];

        if (is_string($methodsInput) && $methodsInput !== '') {
            $methods = array_map('trim', explode(',', $methodsInput));
        }

        try {
            $path = $this->generator->generate($name, ['methods' => $methods]);

            $this->info("Controller created successfully: {$path}");

            return self::SUCCESS;
        } catch (RuntimeException $e) {
            $this->error('Failed to create controller: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
