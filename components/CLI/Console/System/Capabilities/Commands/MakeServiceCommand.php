<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Capabilities\Commands;

use Avax\Components\CLI\Console\System\Capabilities\Generators\ServiceGenerator;
use Avax\Components\CLI\Console\System\PublicSurface\Command;
use RuntimeException;

/**
 * Command to generate a new service class.
 */
class MakeServiceCommand extends Command
{
    protected string $name        = 'make:service';
    protected string $description = 'Create a new service class';
    protected string $signature   = 'make:service {name} [--methods=]';
    protected array  $arguments   = ['name'];
    protected array  $options     = ['methods'];

    public function __construct(
        private readonly ServiceGenerator $generator
    ) {}

    protected function handle() : int
    {
        $name = $this->argument(0);

        if (empty($name)) {
            $name = $this->ask('Enter service name');

            if (empty($name)) {
                $this->error('Service name is required.');

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

            $this->info("Service created successfully: {$path}");

            return self::SUCCESS;
        } catch (RuntimeException $e) {
            $this->error('Failed to create service: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
