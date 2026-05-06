<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Capabilities\Commands;

use Avax\Components\CLI\Console\System\PublicSurface\Command;
use Avax\Components\DeveloperTools\CodeGeneration\System\Capabilities\Generators\CapabilityGenerator;
use Override;
use RuntimeException;

/**
 * Command to generate a new action/capability class.
 */
class MakeActionCommand extends Command
{
    protected string $name = 'make:action';

    protected string $description = 'Create a new action/capability class';

    protected string $signature = 'make:action {name} [--methods=]';

    protected array $arguments = ['name'];

    protected array $options = ['methods'];

    public function __construct(
        private readonly CapabilityGenerator $capabilityGenerator,
    ) {
    }

    #[Override]
    protected function handle(): int
    {
        $name = $this->argument(0);

        if (empty($name)) {
            $name = $this->ask('Enter service name');

            if ($name === '' || $name === '0') {
                $this->error('Action name is required.');

                return self::INVALID;
            }
        }

        $methodsInput = $this->option('methods');
        $methods = [];

        if (is_string($methodsInput) && $methodsInput !== '') {
            $methods = array_map(trim(...), explode(',', $methodsInput));
        }

        try {
            $path = $this->capabilityGenerator->generate($name, ['methods' => $methods]);

            $this->info('Action created successfully: '.$path);

            return self::SUCCESS;
        } catch (RuntimeException $runtimeException) {
            $this->error('Failed to create action: '.$runtimeException->getMessage());

            return self::FAILURE;
        }
    }
}
