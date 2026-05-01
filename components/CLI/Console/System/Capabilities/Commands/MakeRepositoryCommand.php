<?php

declare(strict_types=1);

namespace Avax\Components\CLI\Console\System\Capabilities\Commands;

use Avax\Components\CLI\Console\System\PublicSurface\Command;
use Avax\Components\DeveloperTools\CodeGeneration\System\Capabilities\Generators\RepositoryGenerator;
use Override;
use RuntimeException;

/**
 * Command to generate a new repository class.
 */
class MakeRepositoryCommand extends Command
{
    protected string $name = 'make:repository';

    protected string $description = 'Create a new repository class';

    protected string $signature = 'make:repository {name} [--entity=]';

    protected array $arguments = ['name'];

    protected array $options = ['entity'];

    public function __construct(
        private readonly RepositoryGenerator $repositoryGenerator,
    ) {}

    #[Override]
    protected function handle(): int
    {
        $name = $this->argument(0);

        if (empty($name)) {
            $name = $this->ask('Enter repository name');

            if ($name === '' || $name === '0') {
                $this->error('Repository name is required.');

                return self::INVALID;
            }
        }

        $entity = $this->option('entity');

        if ($entity === null) {
            $entity = $this->ask('Enter entity name (optional)');
        }

        try {
            $data = [];

            if ($entity !== '') {
                $data['entity'] = $entity;
            }

            $path = $this->repositoryGenerator->generate($name, $data);

            $this->info('Repository created successfully: '.$path);

            return self::SUCCESS;
        } catch (RuntimeException $runtimeException) {
            $this->error('Failed to create repository: '.$runtimeException->getMessage());

            return self::FAILURE;
        }
    }
}
