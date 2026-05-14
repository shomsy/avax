<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Capabilities\ConsoleCommands;

use Avax\Components\CLI\Console\System\PublicSurface\Command;
use Avax\Components\DeveloperTools\CodeGeneration\System\Capabilities\Generators\ControllerGenerator;
use Override;
use RuntimeException;

/**
 * Command to generate a new controller class.
 */
class MakeControllerCommand extends Command
{
    protected string $name = 'make:controller';

    protected string $description = 'Create a new controller class';

    protected string $signature = 'make:controller {name} [--methods=]';

    protected array $arguments = ['name'];

    protected array $options = ['methods'];

    public function __construct(
        private readonly ControllerGenerator $controllerGenerator,
    ) {
    }

    #[Override]
    protected function handle(): int
    {
        $name = $this->argument(0);

        if (empty($name)) {
            $name = $this->ask('Enter controller name');

            if ($name === '' || $name === '0') {
                $this->error('Controller name is required.');

                return self::INVALID;
            }
        }

        $methodsInput = $this->option('methods');
        $methods = [];

        if (is_string($methodsInput) && $methodsInput !== '') {
            $methods = array_map(trim(...), explode(',', $methodsInput));
        }

        try {
            $path = $this->controllerGenerator->generate($name, ['methods' => $methods]);

            $this->info('Controller created successfully: '.$path);

            return self::SUCCESS;
        } catch (RuntimeException $runtimeException) {
            $this->error('Failed to create controller: '.$runtimeException->getMessage());

            return self::FAILURE;
        }
    }
}
